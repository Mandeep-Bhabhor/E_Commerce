<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Storage;
use Kreait\Firebase\Contract\Auth;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class FirebaseService
{
    public Storage $storage;

    public Auth $auth;

    private string $projectId;

    private string $credentialsPath;

    private string $bucket;

    private ?string $accessToken = null;

    private int $tokenExpiry = 0;

    public function __construct()
    {
        $this->credentialsPath =
            app_path('firebase/firebase_credentials.json');

        $credentials =
            json_decode(
                file_get_contents($this->credentialsPath),
                true
            );

        if (!$credentials) {

            throw new Exception(
                'Invalid Firebase credentials JSON'
            );
        }

        $this->projectId =
            $credentials['project_id'];

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Backend Storage SDK uses:
        | project.appspot.com
        |
        | NOT:
        | project.firebasestorage.app
        |
        */

        $this->bucket =
            $this->projectId . '.appspot.com';

        $factory = (new Factory)

            ->withServiceAccount(
                $this->credentialsPath
            )


            ->withDefaultStorageBucket(
                'laravel-store-notifications.firebasestorage.app'
            );

        $this->storage =
            $factory->createStorage();

        $this->auth =
            $factory->createAuth();
    }

    /*
    |--------------------------------------------------------------------------
    | GET STORAGE BUCKET
    |--------------------------------------------------------------------------
    */

    public function bucket()
    {
        return $this->storage
            ->getBucket();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESS TOKEN
    |--------------------------------------------------------------------------
    */

    private function getAccessToken(): string
    {
        if (

            $this->accessToken &&

            time() < ($this->tokenExpiry - 60)

        ) {

            return $this->accessToken;
        }

        $credentials =
            new ServiceAccountCredentials(

                [
                    'https://www.googleapis.com/auth/datastore'
                ],

                $this->credentialsPath

            );

        $token =
            $credentials->fetchAuthToken(

                HttpHandlerFactory::build(
                    new Client()
                )

            );

        if (!isset($token['access_token'])) {

            throw new Exception(
                'Unable to fetch Firebase access token'
            );
        }

        $this->accessToken =
            $token['access_token'];

        $this->tokenExpiry =
            time() + ($token['expires_in'] ?? 3600);

        return $this->accessToken;
    }

    /*
    |--------------------------------------------------------------------------
    | FIRESTORE BASE URL
    |--------------------------------------------------------------------------
    */

    private function firestoreBase(): string
    {
        return

            "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP CLIENT
    |--------------------------------------------------------------------------
    */

    private function http(): Client
    {
        return new Client([

            'timeout' => 15,

            'headers' => [

                'Authorization' =>

                'Bearer ' .
                    $this->getAccessToken(),

                'Content-Type' =>
                'application/json',

            ],

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function getDocument(
        string $path
    ): ?array {

        try {

            $response =
                $this->http()->get(

                    $this->firestoreBase() .
                        '/' .
                        $path

                );

            $doc =
                json_decode(
                    $response->getBody(),
                    true
                );

            return $this->decodeDocument($doc);
        } catch (Exception $e) {

            throw new Exception(

                'Firestore getDocument failed: ' .

                    $e->getMessage()

            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LIST DOCUMENTS
    |--------------------------------------------------------------------------
    */

    public function listDocuments(
        string $collection,
        int $pageSize = 50
    ): array {

        try {

            $response =
                $this->http()->get(

                    $this->firestoreBase() .
                        '/' .
                        $collection,

                    [

                        'query' => [

                            'pageSize' => $pageSize

                        ]

                    ]

                );

            $body =
                json_decode(
                    $response->getBody(),
                    true
                );

            return collect(
                $body['documents'] ?? []
            )

                ->map(
                    fn($doc) =>
                    $this->decodeDocument($doc)
                )

                ->values()

                ->all();
        } catch (Exception $e) {

            throw new Exception(

                'Firestore listDocuments failed: ' .

                    $e->getMessage()

            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADD DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function addDocument(
        string $collection,
        array $data
    ): array {

        try {

            $response =
                $this->http()->post(

                    $this->firestoreBase() .
                        '/' .
                        $collection,

                    [

                        'json' => [

                            'fields' =>
                            $this->encodeFields($data)

                        ]

                    ]

                );

            return $this->decodeDocument(

                json_decode(
                    $response->getBody(),
                    true
                )

            );
        } catch (Exception $e) {

            throw new Exception(

                'Firestore addDocument failed: ' .

                    $e->getMessage()

            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SET DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function setDocument(
        string $path,
        array $data
    ): array {

        try {

            $response =
                $this->http()->patch(

                    $this->firestoreBase() .
                        '/' .
                        $path,

                    [

                        'json' => [

                            'fields' =>
                            $this->encodeFields($data)

                        ]

                    ]

                );

            return $this->decodeDocument(

                json_decode(
                    $response->getBody(),
                    true
                )

            );
        } catch (Exception $e) {

            throw new Exception(

                'Firestore setDocument failed: ' .

                    $e->getMessage()

            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function deleteDocument(
        string $path
    ): bool {

        try {

            $this->http()->delete(

                $this->firestoreBase() .
                    '/' .
                    $path

            );

            return true;
        } catch (Exception $e) {

            throw new Exception(

                'Firestore deleteDocument failed: ' .

                    $e->getMessage()

            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE DOCUMENT (partial — only specified fields)
    |--------------------------------------------------------------------------
    |
    | Uses PATCH with updateMask to update only the given fields
    | without overwriting the entire document.
    |
    | Example: updateDocument('support_chats/user_5/messages/abc123', ['read' => true])
    |
    */

    public function updateDocument(
        string $path,
        array $data
    ): array {

        try {

            $queryParams = [];

            foreach (array_keys($data) as $field) {
                $queryParams[] = 'updateMask.fieldPaths=' . $field;
            }

            $url =
                $this->firestoreBase() .
                '/' .
                $path .
                '?' .
                implode('&', $queryParams);

            $response =
                $this->http()->patch(

                    $url,

                    [
                        'json' => [
                            'fields' => $this->encodeFields($data)
                        ]
                    ]

                );

            return $this->decodeDocument(
                json_decode(
                    $response->getBody(),
                    true
                )
            );
        } catch (Exception $e) {

            throw new Exception(
                'Firestore updateDocument failed: ' .
                    $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RUN QUERY (structured query via REST API)
    |--------------------------------------------------------------------------
    |
    | Runs a structured query against a collection or subcollection.
    | Returns an array of documents matching the filters.
    |
    | $parent:  parent path, e.g. "support_chats/user_5"
    | $collectionId: subcollection name, e.g. "messages"
    | $filters: array of ['field' => ..., 'op' => ..., 'value' => ...]
    |           op: EQUAL, NOT_EQUAL, LESS_THAN, etc.
    |
    | Example:
    |   runQuery('support_chats/user_5', 'messages', [
    |       ['field' => 'sender', 'op' => 'EQUAL', 'value' => 'customer'],
    |       ['field' => 'read',   'op' => 'EQUAL', 'value' => false],
    |   ])
    |
    */

    public function runQuery(
        string $parent,
        string $collectionId,
        array $filters = []
    ): array {

        try {

            $where = [];

            if (count($filters) === 1) {

                $f = $filters[0];
                $where = [
                    'fieldFilter' => [
                        'field' => ['fieldPath' => $f['field']],
                        'op'    => $f['op'],
                        'value' => $this->encodeValue($f['value']),
                    ]
                ];

            } elseif (count($filters) > 1) {

                $compositeFilters = [];

                foreach ($filters as $f) {
                    $compositeFilters[] = [
                        'fieldFilter' => [
                            'field' => ['fieldPath' => $f['field']],
                            'op'    => $f['op'],
                            'value' => $this->encodeValue($f['value']),
                        ]
                    ];
                }

                $where = [
                    'compositeFilter' => [
                        'op'      => 'AND',
                        'filters' => $compositeFilters,
                    ]
                ];
            }

            $structuredQuery = [
                'from' => [
                    ['collectionId' => $collectionId]
                ],
            ];

            if (!empty($where)) {
                $structuredQuery['where'] = $where;
            }

            $url =
                $this->firestoreBase() .
                '/' .
                $parent .
                ':runQuery';

            $response =
                $this->http()->post(

                    $url,

                    [
                        'json' => [
                            'structuredQuery' => $structuredQuery
                        ]
                    ]

                );

            $body = json_decode(
                $response->getBody(),
                true
            );

            $results = [];

            foreach ($body as $item) {

                if (!isset($item['document'])) {
                    continue;
                }

                $doc = $item['document'];

                // Extract document path for reference
                $fullName = $doc['name'] ?? '';

                // Get relative path after /documents/
                $basePath = "projects/{$this->projectId}/databases/(default)/documents/";
                $relativePath = str_replace($basePath, '', $fullName);

                $decoded = $this->decodeDocument($doc);
                $decoded['__path'] = $relativePath;

                $results[] = $decoded;
            }

            return $results;

        } catch (Exception $e) {

            throw new Exception(
                'Firestore runQuery failed: ' .
                    $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ENCODE FIELDS
    |--------------------------------------------------------------------------
    */

    public function encodeFields(
        array $data
    ): array {

        return array_map(

            fn($v) =>
            $this->encodeValue($v),

            $data

        );
    }

    /*
    |--------------------------------------------------------------------------
    | ENCODE VALUE
    |--------------------------------------------------------------------------
    */

    private function encodeValue(mixed $value): array
    {
        return match (true) {

            $value instanceof \DateTimeInterface => [
                'timestampValue' => \DateTime::createFromInterface($value)
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->format('Y-m-d\TH:i:s.v\Z')   // e.g. 2026-05-18T13:03:14.276Z
            ],

            is_null($value) => [
                'nullValue' => null
            ],

            is_bool($value) => [
                'booleanValue' => $value
            ],

            is_int($value) => [
                'integerValue' => (string) $value
            ],

            is_float($value) => [
                'doubleValue' => $value
            ],

            is_array($value) => [
                'mapValue' => [
                    'fields' => $this->encodeFields($value)
                ]
            ],

            default => [
                'stringValue' => (string) $value
            ],
        };
    }

    /*
    |--------------------------------------------------------------------------
    | DECODE DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function decodeDocument(
        array $doc
    ): array {

        $result = [];

        foreach (
            $doc['fields'] ?? []
            as $key => $wrapper
        ) {

            $result[$key] =
                $this->decodeValue($wrapper);
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | DECODE VALUE
    |--------------------------------------------------------------------------
    */

    private function decodeValue(
        array $wrapper
    ): mixed {

        $type =
            array_key_first($wrapper);

        $value =
            $wrapper[$type];

        return match ($type) {

            'stringValue' =>
            $value,

            'integerValue' =>
            (int) $value,

            'doubleValue' =>
            (float) $value,

            'booleanValue' =>
            $value,

            'nullValue' =>
            null,

            default =>
            $value,
        };
    }
}

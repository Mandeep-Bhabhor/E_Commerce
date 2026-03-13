<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($user_id)
    {
        $addresses = Address::where('user_id', $user_id)->get();

        return response()->json([
            'success' => true,
            'user_id' => $user_id,
            'data' => $addresses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // Store address for a specific customer
    public function store(Request $request, $user_id)
    {
        // 1. Manual Validation
        $validated = $request->validate([
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string',
            'state' => 'required|string',
            'pincode' => 'required|numeric|max_digits:6|min_digits:6',
            'type' => 'required|string|in:home,office',
        ]);

        // 2. Prepare Data (Merging address lines)
        $data = $validated;
        $data['address'] = trim(
            $data['address_line1'].' '.($data['address_line2'] ?? '')
        );
        $data['user_id'] = $user_id;

        // Remove the temporary line fields that aren't in the DB
        unset($data['address_line1'], $data['address_line2']);

        // 3. Logic: Only one address per type per user
        $exists = Address::where('user_id', $user_id)
            ->where('type', $data['type'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => [
                    'type' => "You already have a {$data['type']} address.",
                ],
            ], 422); // 422 is the standard Unprocessable Content error
        }

        // 4. Create the Model
        $address = Address::create($data);

        // 5. API Response (No sessions or redirects here!)
        return response()->json([
            'message' => 'Address saved successfully',
            'data' => $address,
            'checkout_address_id' => $address->id, // Sending this so the frontend can store it
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $user_id)
    {
        // 1. Determine which type to update (Default to 'home')
        $type = $request->input('type', 'home');

        // 2. Find the address for this user of this specific type
        $address = Address::where('user_id', $user_id)
            ->where('type', $type)
            ->first();

        // 3. If that type doesn't exist yet, return an error
        if (! $address) {
            return response()->json([
                'message' => "No {$type} address found for this user. Please create it first.",
            ], 404);
        }

        // 4. Validate only the address data (Exclude 'type' from being updatable)
        $validated = $request->validate([
            'address_line1' => 'sometimes|required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'sometimes|required|string',
            'state' => 'sometimes|required|string',
            'pincode' => 'sometimes|required|numeric',
        ]);

        $data = $validated;

        // 5. Handle Address Merging
        if ($request->has('address_line1') || $request->has('address_line2')) {
            // Use current DB values as fallbacks if one line is missing in request
            $currentParts = explode(' ', $address->address, 2);
            $line1 = $request->input('address_line1', $currentParts[0] ?? '');
            $line2 = $request->input('address_line2', $currentParts[1] ?? '');

            $data['address'] = trim($line1.' '.$line2);
        }

        // Remove temporary fields
        unset($data['address_line1'], $data['address_line2']);

        // 6. Update the record
        $address->update($data);

        return response()->json([
            'message' => ucfirst($type).' address updated successfully',
            'data' => $address,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $user_id)
    {
        // 1. Get the type from the request
        $type = $request->input('type');

        if (! $type) {
            return response()->json(['message' => 'Please select type'], 422);
        }

        // 2. Find the specific address first
        // Use an array in where() or chain them correctly
        $address = Address::where('user_id', $user_id)
            ->where('type', $type)
            ->first();

        // 3. Check if it actually exists before deleting
        if (! $address) {
            return response()->json([
                'message' => "No {$type} address found for user {$user_id}",
            ], 404);
        }

        // 4. Delete the record
        $address->delete();

        // DO NOT call $address->save() here. It is already gone from the DB.

        return response()->json([
            'status' => 'success',
            'message' => ucfirst($type).' address deleted successfully',
            'deleted_item' => $address, // Optional: returns the data of the item you just deleted
        ], 200);
    }

   
}

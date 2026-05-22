<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    //
    public function store_api(Request $request)
    {

        $user = Auth::user();

        $data['user_id'] = $user->id;
        $data['product_id'] = $request->product_id;
        $data['inquiry'] = $request->inquiry_subject;
        $data['message'] = $request->message;

        Inquiry::create($data);

        return response()->json(
            [
                'message' => 'stored successfully'
            ]
        );
    }


    public function list_api_user()
    {
        $user = Auth::user();

        $inq = Inquiry::with(['product'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();
        if ($inq->isEmpty()) {

            return response()->json(
                [
                    'message' => 'No inquiry found',
                ]
            );
        } else {
            return response()->json([
                'inquiries' => $inq,
            ]);
        }
    }


    public function create($productId)
    {
        $product = Product::findOrFail($productId);

        $previousInquiries = Inquiry::where('product_id', $productId)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('Inquiry.create', compact(
            'product',
            'previousInquiries'
        ));
    }



    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'inquiry' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Inquiry::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'inquiry' => $request->inquiry,
            'message' => $request->message,
        ]);

        return redirect()->route('products.list')->with('success', 'Inquiry sent successfully');
    }

    public function index()
    {
        $inquiries = Inquiry::with(['user', 'product'])
            ->latest()
            ->paginate(10);

        return view('Inquiry.index', compact('inquiries'));
    }

    public function show($id)
    {
        $inquiry = Inquiry::with(['user', 'product'])
            ->findOrFail($id);

        return view('Inquiry.show', compact('inquiry'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string'
        ]);

        $inquiry = Inquiry::findOrFail($id);

        $inquiry->reply = $request->reply;

        $inquiry->save();

        return redirect()
            ->back()
            ->with('success', 'Reply saved successfully');
    }
}

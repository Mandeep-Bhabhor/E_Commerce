<?php

namespace App\Http\Controllers;

use App\Mail\ContactReplyMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function contactForm()
    {
        return view('customer.contactform');
    }

    public function index()
    {
        // Fetch messages, newest first, 15 per page
        $messages = Contact::latest()->paginate(15);

        return view('admin.contacts.index', compact('messages'));
    }

    // Handle form submission
    public function submit(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // 2. Attach the user_id if they are logged in
        $validated['user_id'] = auth()->check() ? auth()->id() : null;

        // 3. Save it to the database
        Contact::create($validated);

        // 4. Redirect back with a success message
        return back()->with('success', 'Thank you! Your message has been sent successfully. We will get back to you shortly.');
    }

    // Open a specific message
    public function show(Contact $contact)
    {
        // Automatically mark as 'read' when the admin opens it (if it was pending)
        if ($contact->status === 'pending') {
            $contact->update(['status' => 'read']);
        }

        return view('admin.contacts.show', compact('contact'));
    }
   public function reply(Request $request, Contact $contact)
    {
        // 1. Validate the admin's reply
        $request->validate([
            'reply_message' => 'required|string|min:5'
        ]);

        // 2. Send the email directly to the customer's email address
        Mail::to($contact->email)->send(new ContactReplyMail($contact, $request->reply_message));

        // 3. Mark the ticket as resolved so it doesn't clutter the inbox
        $contact->update(['status' => 'resolved']);

        // 4. Send the admin back to the inbox with a success message
        return redirect()->route('admin.contacts.index')
                         ->with('success', 'Reply sent successfully to ' . $contact->email);
    }

}

<?php

namespace App\Http\Controllers;

use App\Events\AdminApprove;
use App\Models\User;
use Illuminate\Http\Request;

class CustomController extends Controller
{
    //
    public function approve_customers_page()
    {
        $users = User::latest()->get();

        return view('admin.approve_customers', compact('users'));
    }

    public function approveCustomer($id)
    {

        $user = User::findOrFail($id);

        $user->status = 'active';
        $user->save();

        event(new AdminApprove($user));

        return redirect()->back();
    }
}

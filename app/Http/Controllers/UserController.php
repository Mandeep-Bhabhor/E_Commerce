<?php

namespace App\Http\Controllers;

use App\Models\HeaderFooterSetting;

class UserController extends Controller
{
    //
    public function dashboard()
    {
        $settings = HeaderFooterSetting::first(); // gets existing row or null

        return view('admin.dashboard', compact('settings'));
    }
}

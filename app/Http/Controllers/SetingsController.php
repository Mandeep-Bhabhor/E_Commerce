<?php

namespace App\Http\Controllers;

use App\Models\HeaderFooterSetting;
use Illuminate\Http\Request;

class SetingsController extends Controller
{
    public function saveSettings(Request $request)
    {
        $setting = HeaderFooterSetting::first() ?? new HeaderFooterSetting;

        // STORE ARRAYS
        $setting->email = json_encode($request->emails ?? []);
        $setting->phone = json_encode($request->phones ?? []);

        // SINGLE HEADER LOGO
        if ($request->hasFile('header_logo')) {
            $image = $request->file('header_logo');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();

            // Save to storage/app/public/logos
            $path = $image->storeAs('logos', $imageName, 'public');

            // Save as a direct string path instead of json_encode
            $setting->header_logo = $path;
        }

        // SINGLE FOOTER LOGO
        if ($request->hasFile('footer_logo')) {
            $image = $request->file('footer_logo');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();

            $path = $image->storeAs('logos', $imageName, 'public');

            // Save as a direct string path instead of json_encode
            $setting->footer_logo = $path;
        }

        // Save Map Iframe
        $setting->map_iframe = $request->map_iframe;

        $setting->save();

        return back()->with('success', 'Settings Saved Successfully');
    }
}

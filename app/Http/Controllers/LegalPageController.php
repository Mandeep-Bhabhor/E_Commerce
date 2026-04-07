<?php
namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegalPageController extends Controller
{
    // List all legal pages
    public function index()
    {
        $pages = LegalPage::latest()->get();
        return view('admin.legal_pages.index', compact('pages'));
    }

    // Show the form to create a new page
    public function create()
    {
        return view('admin.legal_pages.form');
    }

    // Save the new page
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:legal_pages,slug',
            'description' => 'nullable|string',
        ]);

        LegalPage::create([
            'title' => $request->title,
            // Automatically generate a slug from the title if left blank
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->title),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.legal-pages.index')->with('success', 'Page created successfully.');
    }

    // Show the form to edit a page
    public function edit(LegalPage $legalPage)
    {
        return view('admin.legal_pages.form', compact('legalPage'));
    }

    // Update the page
    public function update(Request $request, LegalPage $legalPage)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:legal_pages,slug,' . $legalPage->id,
            'description' => 'nullable|string',
        ]);

        $legalPage->update([
            'title' => $request->title,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->title),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.legal-pages.index')->with('success', 'Page updated successfully.');
    }

    // Delete the page
    public function destroy(LegalPage $legalPage)
    {
        $legalPage->delete();
        return back()->with('success', 'Page deleted successfully.');
    }
}
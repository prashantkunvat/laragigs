<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    // Get and Show all Listings
    public function index()
    {
        // dd(Listing::latest()->filter(request(['tag', 'search']))->paginate(2));
        // dd(request('tag'));
        return view('listings.index', [
            'listings' => Listing::latest()->filter(request(['tag', 'search']))->paginate(6)
        ]);
    }
    
    // Show Single Listings
    public function show(Listing $listing)
    {
        return view('listings.show', [
            'listing' => $listing
        ]);
    }

    // Show create form
    public function create()
    {
        return view('listings.create');
    }

    // Store Listing Data
    public function store(Request $request)
    {

        // dd(($request->file('logo')->store()));
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if ($request->hasFile('logo')) {
            // dd($request->file('logo'));

            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
            // dd($formFields); // Check if 'logo' key is present with the file path
        }

        $formFields['user_id'] = auth()->id();
        // dd($formFields); // Check if 'user_id' is present

        Listing::create($formFields);

        return redirect('/')->with('message', 'Listing created successfuly!');
    }


    // Show edit form
    public function edit(Listing $listing)
    {
        // dd($listing->title);
        return view('listings.edit', ['listing' => $listing]);
    }

    // Updating Listing Data
    public function update(Request $request, Listing $listing)
    {

        // Make sure logged in user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, "Unauthorized Action");
        }

        // dd(($request->file('logo')->store()));
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if ($request->hasFile('logo')) {
            // dd($request->file('logo'));

            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
            // dd($formFields); // Check if 'logo' key is present with the file path
        }

        $listing->update($formFields);

        return back()->with('message', 'Listing updated successfuly!');
    }

    // Delete Listing

    public function destroy(Listing $listing)
    {
        // Make sure logged in user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, "Unauthorized Action");
        }

        $listing->delete();
        return redirect('/')->with('message', 'Listing deleted successfully!');
    }

    // Manage function
    public function manage()
    {
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
    }
}

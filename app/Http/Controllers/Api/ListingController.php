<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Listing;
use App\Models\Notification;

class ListingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'district' => 'required|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $listing = Listing::create([
            'category_id' => $request->category_id,
            'user_id' => request()->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'district' => $request->district,
            'status' => 'pending',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('listings', 'public');

                $listing->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::send(
                $admin->id,
                'listing_created',
                'A new listing "' . $listing->title . '" has been created and is awaiting approval.'
            );
        }

        return response()->json([
            'message' => 'Listing created successfully. Waiting for admin approval.',
            'listing' => $listing,
        ], 201);
    }

    public function index()
    {
        $listings = Listing::with('category', 'user', 'images')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return response()->json($listings);
    }

    public function myListings(Request $request)
    {
        $user = $request->user();
        $listings = Listing::with('category', 'images')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json($listings);
    }

    public function approve($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->status = 'approved';
        $listing->save();

        Notification::send(
            $listing->user_id,
            'listing_approved',
            'Your listing "' . $listing->title . '" has been approved.'
        );

        return response()->json([
            'message' => 'Listing approved successfully.',
            'listing' => $listing,
        ]);
    }

    public function reject($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->status = 'rejected';
        $listing->save();

        Notification::send(
            $listing->user_id,
            'listing_rejected',
            'Your listing "' . $listing->title . '" has been rejected.'
        );

        return response()->json([
            'message' => 'Listing rejected successfully.',
            'listing' => $listing,
        ]);
    }

    public function pending()
    {
        $listings = Listing::with(['category', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json($listings);
    }

    public function show($id)
    {
        $listing = Listing::with(['category', 'user', 'images'])
            ->where('status', 'approved')
            ->findOrFail($id);

        return response()->json($listing);
    }

    public function showOwn(Request $request, $id)
    {
        $listing = Listing::with(['category', 'images'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($listing);
    }

    public function update(Request $request, $id)
    {
        $listing = Listing::where('user_id', $request->user()->id)->findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'district' => 'required|string',
        ]);

        $listing->update([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'district' => $request->district,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Listing updated. Waiting for admin re-approval.',
            'listing' => $listing,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $listing = Listing::where('user_id', $request->user()->id)->findOrFail($id);
        $listing->delete();

        return response()->json(['message' => 'Listing deleted successfully']);
    }
}

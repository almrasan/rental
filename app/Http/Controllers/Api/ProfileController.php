<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Booking;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'total_listings' => Listing::where('user_id', $user->id)->count(),
            'approved_listings' => Listing::where('user_id', $user->id)->where('status', 'approved')->count(),
            'pending_listings' => Listing::where('user_id', $user->id)->where('status', 'pending')->count(),
            'total_bookings_made' => Booking::where('user_id', $user->id)->count(),
            'bookings_received' => Booking::whereHas('listing', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
        ]);
    }
}

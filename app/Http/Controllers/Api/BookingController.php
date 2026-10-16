<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Listing;
use Illuminate\Http\Request;
use App\Models\Notification;


class BookingController extends Controller
{
    //
    public function store(Request $request, $listingId)
    {
        $listing = Listing::where('status', 'approved')->findOrFail($listingId);

        $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_nic' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string|max:255',
            'contact_city' => 'nullable|string|max:100',
            'contact_notes' => 'nullable|string|max:500',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'listing_id' => $listing->id,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'contact_nic' => $request->contact_nic,
            'contact_address' => $request->contact_address,
            'contact_city' => $request->contact_city,
            'contact_notes' => $request->contact_notes,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'pending', // Default status
        ]);

        Notification::send(
            $listing->user_id,
            'booking_request',
            'You have a new booking request for your listing: ' . $listing->title . '"'
        );

        return response()->json([
            'message' => 'Booking created successfully.',
            'booking' => $booking,
        ], 201);
    }

    public function confirm($id){

        $booking = Booking::with('listing')->findOrFail($id);
        if($booking->listing->user_id !== request()->user()->id){
            return response()->json([
                'message' => 'Unauthorized. You can only confirm bookings for your own listings.'
            ], 403);
        }

        $booking->status = 'confirmed';
        $booking->save();

        $listing = $booking->listing;
        $listing->is_available = false;
        $listing->save();

         Notification::send(
            $booking->user_id,
            'booking_confirmed',
            'Your booking for the listing: "' . $booking->listing->title . '" has been confirmed.'
        );

        return response()->json([
            'message' => 'Booking confirmed successfully.',
            'booking' => $booking,
        ]);
    }

    public function rejectBooking($id)
    {
        $booking = Booking::with('listing')->findOrFail($id);

        if ($booking->listing->user_id !== request()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only reject bookings for your own listings.'
            ], 403);
        }

        $booking->status = 'rejected';
        $booking->save();

        Notification::send(
            $booking->user_id,
            'booking_rejected',
            'Your booking for the listing: "' . $booking->listing->title . '" has been rejected.'
        );

        return response()->json([
            'message' => 'Booking rejected successfully.',
            'booking' => $booking,
        ]);
    }

    public function myListingBookings(Request $request)
    {
        $bookings = Booking::with(['listing', 'user'])
            ->whereHas('listing', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest()
            ->get();

        return response()->json($bookings);
    }

   public function myBookings(Request $request)
    {
        $bookings = Booking::with(['listing.category', 'listing.user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($bookings);
    }

    public function cancel(Request $request, $id)
    {
        $booking = Booking::with('listing')->where('user_id', $request->user()->id)->findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Only pending bookings can be cancelled.'], 400);
        }

        $booking->status = 'cancelled';
        $booking->save();

        Notification::send(
            $booking->listing->user_id,
            'booking_cancelled',
            'Booking for "' . $booking->listing->title . '" was cancelled by the user.'
        );

        return response()->json(['message' => 'Booking cancelled successfully']);
    }
}

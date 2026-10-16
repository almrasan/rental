<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    //
    protected $fillable = [
        'user_id',
        'listing_id',
        'start_date',
        'end_date',
        'status',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_nic',
        'contact_address',
        'contact_city',
        'contact_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }


}

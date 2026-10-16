<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    //
    protected $fillable = [
        'category_id',
        'user_id',
        'title',
        'description',
        'price',
        'district',
        'status',
        'is_available',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}

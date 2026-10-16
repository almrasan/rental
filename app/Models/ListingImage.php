<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingImage extends Model
{
    //
    protected $fillable = [
        'listing_id',
        'image_path',
    ];

    protected $appends = ['full_url'];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }

   public function getFullUrlAttribute()
    {
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return asset('storage/' . $this->image_path);
    }
}

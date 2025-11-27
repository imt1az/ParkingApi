<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSpace extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id', 'title', 'description', 'address', 'place_label',
        'lat', 'lng', 'capacity', 'height_limit', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lat' => 'float',
        'lng' => 'float',
    ];

    // Relations
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function availability()
    {
        return $this->hasMany(SpaceAvailability::class, 'space_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'space_id');
    }
}

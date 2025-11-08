<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'space_id', 'start_ts', 'end_ts', 'hours',
        'price_total', 'status', 'hold_expires_at',
        'checked_in_at', 'checked_out_at'
    ];

    protected $casts = [
        'start_ts' => 'datetime',
        'end_ts' => 'datetime',
        'hold_expires_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'price_total' => 'float',
        'hours' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function space()
    {
        return $this->belongsTo(ParkingSpace::class, 'space_id');
    }
}

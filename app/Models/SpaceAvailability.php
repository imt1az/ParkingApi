<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceAvailability extends Model
{
    use HasFactory;

    protected $table = 'space_availability';

    protected $fillable = [
        'space_id', 'start_ts', 'end_ts',
        'base_price_per_hour', 'is_active'
    ];

    protected $casts = [
        'start_ts' => 'datetime',
        'end_ts' => 'datetime',
        'is_active' => 'boolean',
        'base_price_per_hour' => 'float',
    ];

    public function space()
    {
        return $this->belongsTo(ParkingSpace::class, 'space_id');
    }
}

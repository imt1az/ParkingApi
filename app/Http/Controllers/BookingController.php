<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingSpace;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    // Driver: create booking
    public function store(Request $r)
    {
        $u = $this->mustDriver();

        $r->validate([
            'space_id' => 'required|exists:parking_spaces,id',
            'start_ts' => 'required|date',
            'end_ts'   => 'required|date|after:start_ts',
        ]);

        $space = ParkingSpace::findOrFail($r->space_id);
        abort_unless($space->is_active, 409, 'Space not active');

        // Availability must cover the full requested window
        $availability = $space->availability()
            ->where('is_active', true)
            ->where('start_ts', '<=', $r->start_ts)
            ->where('end_ts',   '>=', $r->end_ts)
            ->orderBy('start_ts')
            ->first();

        if (! $availability) {
            return response()->json([
                'error' => ['code' => 'NO_AVAILABILITY', 'message' => 'No active availability for requested window']
            ], 409);
        }

        // Prevent overlap with existing active bookings
        $overlap = Booking::where('space_id', $space->id)
            ->whereIn('status', ['reserved','confirmed','checked_in'])
            ->where(function ($q) use ($r) {
                $q->where('start_ts', '<', $r->end_ts)
                  ->where('end_ts',   '>', $r->start_ts);
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'error' => ['code' => 'ALREADY_BOOKED', 'message' => 'Time window overlaps another booking']
            ], 409);
        }

        $start = Carbon::parse($r->start_ts);
        $end   = Carbon::parse($r->end_ts);
        $minutes = $end->diffInMinutes($start);
        $hours   = round($minutes / 60, 2);
        $price   = round($hours * $availability->base_price_per_hour, 2);

        $booking = Booking::create([
            'user_id' => $u->id,
            'space_id' => $space->id,
            'start_ts' => $start,
            'end_ts'   => $end,
            'hours'    => $hours,
            'price_total' => $price,
            'status'   => 'reserved',
        ]);

        return response()->json($booking->load('space:id,title,address,provider_id'), 201);
    }

    // Driver: list own bookings
    public function myBookings(Request $r)
    {
        $u = $this->mustDriver();

        $q = Booking::with(['space:id,title,address,provider_id'])
            ->where('user_id', $u->id);

        if ($r->filled('status')) {
            $q->where('status', $r->status);
        }

        return response()->json($q->orderByDesc('id')->paginate(20));
    }

    private function mustProviderOrAdmin()
    {
        $u = auth('api')->user();
        abort_unless($u && in_array($u->role, ['provider','admin']), 403, 'FORBIDDEN');
        return $u;
    }

    private function mustDriver()
    {
        $u = auth('api')->user();
        abort_unless($u && $u->role === 'driver', 403, 'FORBIDDEN');
        return $u;
    }

    private function loadBooking($id)
    {
        return Booking::with('space')->findOrFail($id);
    }

    private function ensureOwner($booking)
    {
        $u = auth('api')->user();
        if ($u->role === 'admin') return;
        abort_unless($u->role === 'provider' && $booking->space->provider_id === $u->id, 403, 'FORBIDDEN');
    }

    // Provider/Admin: list bookings for owned spaces
    public function forMySpaces(Request $r)
    {
        $u = $this->mustProviderOrAdmin();

        $q = Booking::with(['space:id,title,address,provider_id'])
            ->whereHas('space', function ($q2) use ($u) {
                if ($u->role === 'provider') {
                    $q2->where('provider_id', $u->id);
                }
            });

        if ($r->filled('status')) {
            $q->where('status', $r->status);
        }

        return response()->json($q->orderByDesc('id')->paginate(20));
    }

    // Provider/Admin: confirm (reserved -> confirmed)
    public function confirm($id)
    {
        $u = $this->mustProviderOrAdmin();
        $booking = $this->loadBooking($id);
        $this->ensureOwner($booking);

        abort_unless($booking->status === 'reserved', 409, 'Only reserved can be confirmed');

        $booking->status = 'confirmed';
        $booking->save();

        return response()->json(['ok'=>true, 'status'=>$booking->status]);
    }

    // Driver/Provider/Admin: cancel
    public function cancel($id)
    {
        $user = auth('api')->user();
        $booking = $this->loadBooking($id);

        // Access rules
        if ($user->role === 'driver') {
            abort_unless($booking->user_id === $user->id, 403, 'FORBIDDEN');
        } elseif ($user->role === 'provider') {
            abort_unless($booking->space->provider_id === $user->id, 403, 'FORBIDDEN');
        }

        // Allowed from states
        abort_unless(in_array($booking->status, ['reserved','confirmed']), 409, 'Cannot cancel in this state');

        $booking->status = 'cancelled';
        $booking->save();

        return response()->json(['ok'=>true, 'status'=>$booking->status]);
    }

    // Provider/Admin: check-in (confirmed -> checked_in)
    public function checkIn($id)
    {
        $u = $this->mustProviderOrAdmin();
        $booking = $this->loadBooking($id);
        $this->ensureOwner($booking);

        abort_unless($booking->status === 'confirmed', 409, 'Only confirmed can be checked-in');

        $booking->status = 'checked_in';
        $booking->checked_in_at = Carbon::now();
        $booking->save();

        return response()->json(['ok'=>true, 'status'=>$booking->status]);
    }

    // Provider/Admin: check-out (checked_in -> completed)
    public function checkOut($id)
    {
        $u = $this->mustProviderOrAdmin();
        $booking = $this->loadBooking($id);
        $this->ensureOwner($booking);

        abort_unless($booking->status === 'checked_in', 409, 'Only checked-in can be checked-out');

        $booking->status = 'completed';
        $booking->checked_out_at = Carbon::now();
        $booking->save();

        return response()->json(['ok'=>true, 'status'=>$booking->status]);
    }

    // Provider/Admin: monthly income for owned spaces (last 12 months)
    public function monthlyReport(Request $r)
    {
        $u = $this->mustProviderOrAdmin();

        $rows = Booking::query()
            ->selectRaw("DATE_FORMAT(start_ts, '%Y-%m') as month, SUM(price_total) as total, COUNT(*) as count")
            ->where('status', 'completed')
            ->whereHas('space', function ($q) use ($u) {
                if ($u->role === 'provider') {
                    $q->where('provider_id', $u->id);
                }
            })
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $sum = $rows->sum('total');

        return response()->json([
            'total_income' => $sum,
            'months' => $rows,
        ]);
    }
}

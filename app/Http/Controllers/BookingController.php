<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingSpace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    // ... (তোমার আগের store(), myBookings() থাকবে)

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

    // Provider/Admin: নিজের স্পেসগুলোর বুকিং লিস্ট
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
}

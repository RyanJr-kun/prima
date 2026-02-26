<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Schedule;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingStatusNotification;

class RoomBookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'room_id'      => 'required|exists:rooms,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'purpose'      => 'required|string|max:255',
            'start_time'   => 'nullable|required_without:is_full_day',
            'end_time'     => 'nullable|required_without:is_full_day|after:start_time',
        ]);

        $isFullDay = $request->has('is_full_day');
        $startTime = $isFullDay ? '07:00' : $request->start_time;
        $endTime   = $isFullDay ? '21:00' : $request->end_time;
        $dayName   = Carbon::parse($request->booking_date)->format('l');

        // Cek Conflict Schedule (Kuliah Rutin)
        $scheduleConflicts = Schedule::where('room_id', $request->room_id)
            ->where('day', $dayName)
            ->get()
            ->filter(function ($schedule) use ($startTime, $endTime) {
                $time = $schedule->real_time;
                if (!$time) return false;
                return ($startTime < $time['end_formatted']) && ($endTime > $time['start_formatted']);
            });

        if ($scheduleConflicts->isNotEmpty()) {
            return back()->with('error', 'Gagal! Ruangan digunakan untuk kuliah rutin pada jam tersebut.');
        }

        // Cek Conflict Booking Lain (Approved)
        $bookingConflicts = RoomBooking::where('room_id', $request->room_id)
            ->where('booking_date', $request->booking_date)
            ->where('status', 'approved')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->exists();

        if ($bookingConflicts) {
            return back()->with('error', 'Gagal! Ruangan sudah dibooking orang lain pada jam tersebut.');
        }

        $booking = RoomBooking::create([
            'user_id'      => Auth::id(),
            'room_id'      => $request->room_id,
            'booking_date' => $request->booking_date,
            'start_time'   => $startTime,
            'end_time'     => $endTime,
            'is_full_day'  => $isFullDay,
            'purpose'      => $request->purpose,
            'status'       => 'pending',
        ]);

        // kirim notif ke admin/baak
        $admins = User::role(['admin', 'baak'])->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new BookingStatusNotification($booking, 'submitted'));
        }

        return back()->with('success', 'Permintaan booking berhasil diajukan! Menunggu persetujuan Admin/BAAK.');
    }

    /**
     * Approve Booking (Aksi Admin)
     */
    public function approve($id)
    {
        $booking = RoomBooking::with('user', 'room')->findOrFail($id);
        $booking->update(['status' => 'approved']);

        // Notif ke Dosen
        $booking->user->notify(new BookingStatusNotification($booking, 'approved'));

        return back()->with('success', 'Booking berhasil disetujui.');
    }

    /**
     * Reject Booking (Aksi Admin)
     */
    public function reject(Request $request, $id)
    {
        $booking = RoomBooking::with('user', 'room')->findOrFail($id);

        $booking->update([
            'status' => 'rejected',
            'rejection_note' => $request->reason ?? 'Tidak sesuai prosedur.'
        ]);

        // Notif ke Dosen
        $booking->user->notify(new BookingStatusNotification($booking, 'rejected'));

        return back()->with('success', 'Booking telah ditolak.');
    }
}

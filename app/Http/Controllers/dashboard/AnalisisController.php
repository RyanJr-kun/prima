<?php

namespace App\Http\Controllers\dashboard;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Schedule;
use App\Models\RoomBooking;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AnalisisController extends Controller
{
  public function index(Request $request)
  {
    $user = Auth::user();
    $now = Carbon::now();

    // 1. FILTER INPUT
    $filterDate = $request->get('date', $now->format('Y-m-d'));
    $filterCampus = $request->get('campus'); // 'kampus_1' atau 'kampus_2'

    $dayName = Carbon::parse($filterDate)->format('l'); // Monday, Tuesday...
    $greeting = $this->getGreeting($now->format('H'));

    $activePeriod = AcademicPeriod::where('is_active', true)->first();

    // ==========================================
    // DASHBOARD DOSEN
    // ==========================================
    if ($user->hasRole('dosen')) {

      // 1. QUERY JADWAL HARI INI (Diperbaiki agar sama dengan MyScheduleController)
      $todaySchedules = collect([]);

      if ($activePeriod) {
        $todaySchedules = Schedule::with(['course', 'studyClass.prodi', 'room'])
          ->where('day', $dayName)
          // Gunakan logika relasi yang sama persis dengan MyScheduleController
          ->whereHas('courseDistribution', function ($q) use ($activePeriod, $user) {
            $q->where('academic_period_id', $activePeriod->id);
            $q->whereHas('teachingLecturers', function ($teacher) use ($user) {
              $teacher->where('users.id', $user->id);
            });
          })
          ->orderBy('time_slot_ids')
          ->take(3)
          ->get();
      }

      // 2. LOGIC KETERSEDIAAN RUANGAN (Menampilkan Jam Terpakai)
      $allRooms = Room::where('is_active', true)
        ->when($filterCampus, function ($q) use ($filterCampus) {
          return $q->where('location', $filterCampus);
        })
        ->orderBy('building')
        ->orderBy('name')
        ->get();

      $availableRooms = $allRooms->map(function ($room) use ($dayName, $filterDate) {

        // A. Ambil Jadwal Rutin (Schedule)
        $schedules = Schedule::where('room_id', $room->id)
          ->where('day', $dayName)
          ->get();

        // B. Ambil Booking Insidental (Approved)
        $bookings = RoomBooking::where('room_id', $room->id)
          ->where('booking_date', $filterDate)
          ->where('status', 'approved')
          ->get();

        // C. List Jam Terpakai (Untuk ditampilkan ke user)
        $busySlots = [];

        // Cek dari Jadwal Rutin
        foreach ($schedules as $sch) {
          $realTime = $sch->real_time; // accessor getRealTimeAttribute
          if ($realTime) {
            $busySlots[] = $realTime['start_formatted'] . '-' . $realTime['end_formatted'];
          }
        }

        foreach ($bookings as $book) {
          $start = substr($book->start_time, 0, 5);
          $end = substr($book->end_time, 0, 5);
          $busySlots[] = $start . '-' . $end . ' (Booked)';
        }
        sort($busySlots);

        if (empty($busySlots)) {
          $room->availability_status = 'Kosong Sepanjang Hari';
          $room->availability_color = 'success';
          $room->busy_notes = 'Bisa digunakan kapan saja';
        } else {
          $room->availability_status = 'Terpakai Sebagian';
          $room->availability_color = 'warning';
          $room->busy_notes = implode(', ', $busySlots);
        }
        return $room;
      });
      $displayRooms = $availableRooms->take(20);

      // Untuk tab "Terpakai", kita ambil yang memang ada jadwalnya
      $busyRooms = $availableRooms->filter(function ($r) {
        return $r->availability_color == 'warning';
      });

      return view('content.dashboard.dashboard', compact(
        'user',
        'greeting',
        'todaySchedules',
        'displayRooms',
        'busyRooms',
        'filterDate',
        'filterCampus'
      ));
    }

    // ==========================================
    // DASHBOARD ADMIN (Tetap Sama)
    // ==========================================
    else {
      $pendingBookings = RoomBooking::with(['user', 'room'])
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->get();

      $occupiedRoomIds = $this->getOccupiedRoomIds($filterDate);

      $allRooms = Room::where('is_active', true)
        ->when($filterCampus, function ($q) use ($filterCampus) {
          return $q->where('location', $filterCampus);
        })
        ->orderBy('building')
        ->orderBy('name')
        ->get()
        ->map(function ($room) use ($occupiedRoomIds) {
          $room->status_hari_ini = in_array($room->id, $occupiedRoomIds) ? 'Terpakai' : 'Kosong';
          return $room;
        });

      $recentBookings = RoomBooking::with('user', 'room')->latest()->take(5)->get()
        ->map(function ($item) {
          $item->activity_type = 'booking';
          $item->activity_desc = "Mengajukan booking ruang {$item->room->name}";
          $item->time = $item->created_at;
          return $item;
        });

      $recentLogins = User::role('dosen')->orderBy('updated_at', 'desc')->take(5)->get()
        ->map(function ($item) {
          $item->activity_type = 'login';
          $item->activity_desc = "Login ke dalam sistem";
          $item->time = $item->updated_at;
          return $item;
        });

      $activities = $recentBookings->concat($recentLogins)->sortByDesc('time')->take(5);

      return view('content.dashboard.dashboard_admin', compact(
        'user',
        'greeting',
        'pendingBookings',
        'allRooms',
        'activities',
        'filterDate',
        'filterCampus'
      ));
    }
  }

  // Helper Lama
  private function getOccupiedRoomIds($date)
  {
    $dayName = Carbon::parse($date)->format('l');
    $idsFromSchedule = Schedule::where('day', $dayName)->pluck('room_id')->toArray();
    $idsFromBooking = RoomBooking::where('booking_date', $date)->where('status', 'approved')->pluck('room_id')->toArray();
    return array_unique(array_merge($idsFromSchedule, $idsFromBooking));
  }

  private function getGreeting($hour)
  {
    if ($hour < 12) return 'Selamat Pagi';
    if ($hour < 15) return 'Selamat Siang';
    if ($hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
  }
}

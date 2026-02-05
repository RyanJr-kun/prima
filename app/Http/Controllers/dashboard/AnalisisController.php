<?php

namespace App\Http\Controllers\dashboard;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Schedule;
use App\Models\RoomBooking;
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
    $filterDate = $request->get('date', $now->format('Y-m-d'));
    $greeting = $this->getGreeting($now->format('H'));

    if ($user->hasRole('dosen')) {

      $dayName = Carbon::parse($filterDate)->format('l');
      $todaySchedules = Schedule::with(['room', 'course', 'studyClass'])
        ->where('user_id', $user->id)
        ->where('day', $dayName)
        ->whereHas('studyClass', function ($q) {
          $q->whereExists(function ($subQuery) {
            $subQuery->select(DB::raw(1))
              ->from('aproval_documents')
              ->whereColumn('aproval_documents.prodi_id', 'study_classes.prodi_id')
              ->whereColumn('aproval_documents.academic_period_id', 'study_classes.academic_period_id')
              ->where('aproval_documents.type', 'jadwal_perkuliahan')
              ->where('aproval_documents.status', 'approved_direktur');
          });
        })
        ->orderBy('time_slot_ids')
        ->take(3)
        ->get();

      $occupiedRoomIds = $this->getOccupiedRoomIds($filterDate);

      $availableRooms = Room::whereNotIn('id', $occupiedRoomIds)
        ->where('is_active', true)
        ->get();

      $bookedRooms = Room::whereIn('id', $occupiedRoomIds)->get();

      return view('content.dashboard.dashboard', compact(
        'user',
        'greeting',
        'todaySchedules',
        'availableRooms',
        'bookedRooms',
        'filterDate'
      ));
    } else {

      $pendingBookings = RoomBooking::with(['user', 'room'])
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->get();

      // 2. Monitoring Ruangan
      $occupiedRoomIds = $this->getOccupiedRoomIds($filterDate);

      $allRooms = Room::where('is_active', true)
        ->orderBy('building')
        ->orderBy('name')
        ->get()
        ->map(function ($room) use ($occupiedRoomIds) {
          $room->status_hari_ini = in_array($room->id, $occupiedRoomIds) ? 'Terpakai' : 'Kosong';
          return $room;
        });

      // 3. Aktivitas Terkini

      // A. Booking Terakhir
      $recentBookings = RoomBooking::with('user', 'room')
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($item) {
          $item->activity_type = 'booking';
          $item->activity_desc = "Mengajukan booking ruang {$item->room->name}";
          $item->time = $item->created_at;
          return $item;
        });

      // B. Login Terakhir (Dosen)
      // PERUBAHAN 2: Gunakan scope role() milik Spatie
      // Pastikan nama role di database roles adalah 'dosen' (huruf kecil/besar berpengaruh)
      $recentLogins = User::role('dosen')
        ->orderBy('updated_at', 'desc')
        ->take(5)
        ->get()
        ->map(function ($item) {
          $item->activity_type = 'login';
          $item->activity_desc = "Login ke dalam sistem";
          $item->time = $item->updated_at;
          return $item;
        });

      $activities = $recentBookings->concat($recentLogins)
        ->sortByDesc('time')
        ->take(5);

      return view('content.dashboard.dashboard_admin', compact(
        'user',
        'greeting',
        'pendingBookings',
        'allRooms',
        'activities',
        'filterDate'
      ));
    }
  }

  private function getOccupiedRoomIds($date)
  {
    $dayName = Carbon::parse($date)->format('l');

    $idsFromSchedule = Schedule::where('day', $dayName)
      ->whereHas('studyClass', function ($q) {
        $q->whereExists(function ($subQuery) {
          $subQuery->select(DB::raw(1))
            ->from('aproval_documents')
            ->whereColumn('aproval_documents.prodi_id', 'study_classes.prodi_id')
            ->whereColumn('aproval_documents.academic_period_id', 'study_classes.academic_period_id')
            ->where('aproval_documents.type', 'jadwal_perkuliahan')
            ->where('aproval_documents.status', 'approved_direktur');
        });
      })
      ->pluck('room_id')
      ->toArray();

    $idsFromBooking = RoomBooking::where('booking_date', $date)
      ->where('status', 'approved')
      ->pluck('room_id')
      ->toArray();

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

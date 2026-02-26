<?php

namespace App\Http\Controllers\dashboard;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Workload;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Models\AprovalDocument;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AnalisisController extends Controller
{
  public function index(Request $request)
  {
    $user = Auth::user();
    $now = Carbon::now();

    // filter tanggal & lokasi kampus
    $filterDate = $request->get('date', $now->format('Y-m-d'));
    $filterCampus = $request->get('campus');

    // nama hari + waktu
    $dayName = Carbon::parse($filterDate)->format('l');
    $greeting = $this->getGreeting($now->format('H'));

    //ambil periode aktif
    $activePeriod = AcademicPeriod::where('is_active', true)->first();

    // DASHBOARD DOSEN

    /** @var User $user */
    if ($user->hasRole('dosen')) { // kalau user yang login memiliki role dosen.

      $todaySchedules = collect([]);

      if ($activePeriod) {
        $todaySchedules = Schedule::with(['course', 'studyClass.prodi', 'room'])
          ->where('day', $dayName)
          ->where('user_id', $user->id)
          ->whereHas('courseDistribution', function ($q) use ($activePeriod) {
            $q->where('academic_period_id', $activePeriod->id);
          })
          ->orderBy('time_slot_ids')
          ->take(5)
          ->get();
      }

      $myBookings = RoomBooking::with('room')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

      $allRooms = Room::where('is_active', true)
        ->when($filterCampus, function ($q) use ($filterCampus) {
          return $q->where('location', $filterCampus);
        })
        ->orderBy('building')
        ->orderBy('name')
        ->get();


      $availableRooms = $allRooms->map(function ($room) use ($dayName, $filterDate) {

        $schedules = Schedule::with(['courseDistribution.teachingLecturers', 'course'])
          ->where('room_id', $room->id)
          ->where('day', $dayName)
          ->get();

        $bookings = RoomBooking::with('user')
          ->where('room_id', $room->id)
          ->where('booking_date', $filterDate)
          ->where('status', 'approved')
          ->get();

        $usageDetails = [];

        foreach ($schedules as $sch) {
          $realTime = $sch->real_time;
          if ($realTime) {
            $dosenName = $sch->courseDistribution->teachingLecturers->first()->name ?? 'Dosen';
            $courseName = $sch->course->name ?? 'Kuliah';
            $usageDetails[] = [
              'start' => $realTime['start_formatted'],
              'end'   => $realTime['end_formatted'],
              'user'  => $dosenName,
              'desc'  => $courseName,
              'type'  => 'Kuliah'
            ];
          }
        }

        foreach ($bookings as $book) {
          $start = substr($book->start_time, 0, 5);
          $end = substr($book->end_time, 0, 5);

          $usageDetails[] = [
            'start' => $start,
            'end'   => $end,
            'user'  => $book->user->name,
            'desc'  => $book->purpose,
            'type'  => 'Booking'
          ];
        }

        // Urutkan berdasarkan jam mulai
        usort($usageDetails, function ($a, $b) {
          return $a['start'] <=> $b['start'];
        });

        // Status & HTML Popover
        if (empty($usageDetails)) {
          $room->availability_status = 'Kosong Sepanjang Hari';
          $room->availability_color = 'success';
          $room->busy_notes = 'Bisa digunakan kapan saja';
          $room->popover_content = null;
        } else {
          $room->availability_status = 'Terpakai Sebagian';
          $room->availability_color = 'warning';

          // Generate list jam string (untuk keperluan booking modal/simple text)
          $busyString = array_map(function ($item) {
            return $item['start'] . '-' . $item['end'];
          }, $usageDetails);
          $room->busy_notes = implode(', ', $busyString);

          // Generate HTML List untuk Popover
          $htmlList = '<ul class="list-group list-group-flush p-0 m-0 text-start">';
          foreach ($usageDetails as $detail) {
            $badgeColor = $detail['type'] == 'Kuliah' ? 'bg-label-primary' : 'bg-label-warning';
            $htmlList .= '
                    <li class="list-group-item d-flex flex-column px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge ' . $badgeColor . ' p-1" style="font-size:0.65rem">' . $detail['type'] . '</span>
                            <span class="fw-bold text-dark small">' . $detail['start'] . ' - ' . $detail['end'] . '</span>
                        </div>
                        <span class="fw-semibold text-truncate small" style="max-width:180px">' . htmlspecialchars($detail['user']) . '</span>
                        <small class="text-muted text-truncate" style="max-width:180px; font-size:0.65rem">' . htmlspecialchars($detail['desc']) . '</small>
                    </li>';
          }
          $htmlList .= '</ul>';
          $room->popover_content = $htmlList;
        }
        return $room;
      });
      $displayRooms = $availableRooms->take(20);

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
        'filterCampus',
        'myBookings'
      ));
    } else {
      // DASHBOARD ADMIN 

      $pendingBookings = RoomBooking::with(['user', 'room'])
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->get();

      // Monitoring Ruangan
      $allRooms = Room::where('is_active', true)
        ->when($filterCampus, function ($q) use ($filterCampus) {
          return $q->where('location', $filterCampus);
        })
        ->orderBy('building')
        ->orderBy('name')
        ->get()
        ->map(function ($room) use ($dayName, $filterDate) {
          $schedules = Schedule::with(['courseDistribution.teachingLecturers']) // Eager load dosen
            ->where('room_id', $room->id)
            ->where('day', $dayName)
            ->get();

          // Cek Booking Approved 
          $bookings = RoomBooking::with('user') // Eager load user
            ->where('room_id', $room->id)
            ->where('booking_date', $filterDate)
            ->where('status', 'approved')
            ->get();

          $usageDetails = [];

          foreach ($schedules as $sch) {
            $realTime = $sch->real_time;
            if ($realTime) {

              $dosenName = $sch->courseDistribution->teachingLecturers->first()->name ?? 'Dosen';
              $courseName = $sch->course->name ?? 'Kuliah';

              $usageDetails[] = [
                'start' => $realTime['start_formatted'],
                'end'   => $realTime['end_formatted'],
                'user'  => $dosenName,
                'desc'  => $courseName,
                'type'  => 'Kuliah'
              ];
            }
          }

          foreach ($bookings as $book) {
            $start = substr($book->start_time, 0, 5);
            $end = substr($book->end_time, 0, 5);

            $usageDetails[] = [
              'start' => $start,
              'end'   => $end,
              'user'  => $book->user->name,
              'desc'  => $book->purpose,
              'type'  => 'Booking'
            ];
          }

          usort($usageDetails, function ($a, $b) {
            return $a['start'] <=> $b['start'];
          });

          if (empty($usageDetails)) {
            $room->status_hari_ini = 'Kosong';
            $room->popover_content = null;
          } else {
            $room->status_hari_ini = 'Terpakai';

            $htmlList = '<ul class="list-group list-group-flush p-0 m-0">';
            foreach ($usageDetails as $detail) {
              $badgeColor = $detail['type'] == 'Kuliah' ? 'bg-label-primary' : 'bg-label-warning';
              $htmlList .= '
                    <li class="list-group-item d-flex flex-column px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge ' . $badgeColor . ' p-1" style="font-size:0.65rem">' . $detail['type'] . '</span>
                            <span class="fw-bold text-dark small">' . $detail['start'] . ' - ' . $detail['end'] . '</span>
                        </div>
                        <span class="fw-semibold text-truncate small" style="max-width:180px">' . htmlspecialchars($detail['user']) . '</span>
                        <small class="text-muted text-truncate" style="max-width:180px; font-size:0.65rem">' . htmlspecialchars($detail['desc']) . '</small>
                    </li>';
            }
            $htmlList .= '</ul>';

            $room->popover_content = $htmlList;
          }

          return $room;
        });

      // Aktivitas Terkini
      // booking ruangan
      $recentBookings = RoomBooking::with('user', 'room')->latest()->take(5)->get()
        ->map(function ($item) {
          return (object) [
            'name'  => $item->user->name,
            'desc'  => "Mengajukan booking ruang {$item->room->name}",
            'time'  => $item->created_at,
            'type'  => 'booking',
            'icon'  => 'bx-calendar-plus',
            'color' => 'primary'
          ];
        });

      // terakhir login 
      $recentLogins = User::role('dosen')->orderBy('updated_at', 'desc')->take(5)->get()
        ->map(function ($item) {
          return (object) [
            'name'  => $item->name,
            'desc'  => "Login ke dalam sistem",
            'time'  => $item->updated_at,
            'type'  => 'login',
            'icon'  => 'bx-log-in-circle',
            'color' => 'success'
          ];
        });

      // dokumen 
      $recentApprovals = AprovalDocument::with(['lastActionUser', 'prodi'])
        ->whereNotNull('action_by_user_id') // Hanya yang sudah di-acc/reject
        ->orderBy('updated_at', 'desc')
        ->take(5)
        ->get()
        ->map(function ($item) {
          $isRejected = $item->status == 'rejected';
          $actionWord = $isRejected ? 'meminta revisi' : 'menyetujui';

          // Ambil info dokumen dari Accessor model
          $docType = $item->type_label;

          // Konteks Prodi (Jika ada)
          $context = $item->prodi ? "({$item->prodi->name})" : "(Global)";

          return (object) [
            'name'  => $item->lastActionUser->name ?? 'Sistem', // Siapa yang ACC
            'desc'  => "Telah $actionWord dokumen <strong>$docType</strong> $context",
            'time'  => $item->updated_at,
            'type'  => 'approval',
            'icon'  => $isRejected ? 'bx-edit' : 'bx-check-double',
            'color' => $isRejected ? 'warning' : 'info'
          ];
        });

      // managemen bkd
      $recentBkds = Workload::with(['user', 'academicPeriod'])
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($item) {
          $period = $item->academicPeriod->name ?? '-';

          return (object) [
            'name'  => $item->user->name ?? 'Dosen',
            'desc'  => "Melaporkan BKD Periode $period",
            'time'  => $item->created_at,
            'type'  => 'bkd',
            'icon'  => 'bx-file',
            'color' => 'danger'
          ];
        });

      // GABUNGKAN SEMUA & URUTKAN
      // Urutan prioritas: Booking -> Approval -> BKD -> Login
      $activities = $recentBookings
        ->concat($recentApprovals)
        ->concat($recentBkds)
        ->concat($recentLogins)
        ->sortByDesc('time') // Urutkan berdasarkan waktu kejadian terbaru
        ->take(8);

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
    if ($hour < 9) return 'Selamat Pagi';
    if ($hour < 15) return 'Selamat Siang';
    if ($hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
  }
}

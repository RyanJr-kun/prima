<?php

namespace App\Http\Controllers\dokumen;

use Carbon\Carbon;

use App\Models\Room;
use App\Models\Prodi;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\TimeSlots;
use App\Models\StudyClass;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Models\AprovalDocument;
use App\Models\CourseDistribution;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\AutoScheduleService;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Setup Filter Dasar
        $campus  = $request->input('campus', 'kampus_1');
        $shift   = $request->input('shift', 'pagi');
        $prodiId = $request->input('prodi_id');

        $prodis  = Prodi::all();
        $activePeriodId = AcademicPeriod::where('is_active', true)->value('id');

        if (!$activePeriodId) {
            return redirect()->back()->with('error', 'Tidak ada periode akademik yang aktif.');
        }

        // 2. Ambil Dokumen Approval (Logic Baru: Kampus & Shift)
        $document = AprovalDocument::where('academic_period_id', $activePeriodId)
            ->where('type', 'jadwal_perkuliahan')
            ->where('campus', $campus)
            ->where('shift', $shift)
            ->first();

        $isReadOnly = false;
        // Jika dokumen ada DAN statusnya bukan draft/rejected, maka kunci (Read Only)
        if ($document && !in_array($document->status, ['draft', 'rejected'])) {
            $isReadOnly = true;
        }

        // 3. Ambil Prodi yang Distribusinya Sudah ACC Direktur
        $approvedProdiIds = AprovalDocument::where('academic_period_id', $activePeriodId)
            ->where('type', 'distribusi_matkul')
            ->where('status', 'approved_direktur')
            ->pluck('prodi_id')
            ->toArray();

        // 4. Query Matkul yang Belum Terjadwal (FIXED VARIABLE SCOPE)
        $unscheduledDistributions = CourseDistribution::query()
            ->where('academic_period_id', $activePeriodId)
            ->has('teachingLecturers')
            ->whereHas('studyClass', function ($query) use ($approvedProdiIds, $shift, $campus, $prodiId) {
                // PERBAIKAN DI SINI: Tambahkan $shift, $campus, $prodiId ke dalam 'use'

                $query->whereIn('prodi_id', $approvedProdiIds);
                $query->where('shift', $shift);

                // Filter Kampus via Relasi Prodi
                $query->whereHas('prodi', function ($q) use ($campus) {
                    $q->where('primary_campus', $campus);
                });

                // Filter Optional User (Jika user memilih filter Prodi)
                if ($prodiId) {
                    $query->where('prodi_id', $prodiId);
                }
            })
            ->doesntHave('schedule')
            ->with(['course', 'studyClass', 'teachingLecturers'])
            ->get()
            ->map(function ($dist) {
                // Ambil Raw Tags dari Course
                $rawTags = $dist->course->required_tags;
                // Handle format data (array/json string)
                $tags = is_array($rawTags) ? $rawTags : json_decode($rawTags ?? '[]', true);
                if (!is_array($tags)) $tags = [];

                // TAMBAHAN: Generate HTML Badge siap pakai untuk View
                $badges = [];
                foreach ($tags as $t) {
                    $label = \App\Models\Room::getTagName($t);
                    $color = \App\Models\Room::getTagColor($t);
                    // Kita simpan struktur data untuk diloop di view
                    $badges[] = [
                        'label' => $label,
                        'color' => $color
                    ];
                }

                $dist->needs_lab = !empty(array_diff($tags, ['general'])); // Logic: butuh lab jika tags bukan cuma general
                $dist->formatted_tags = $badges; // Kirim ini ke View

                return $dist;
            });

        // 5. Ambil Data Ruangan (Resources)
        $rooms = Room::where('location', $campus)
            ->orderBy('building')
            ->orderBy('name')
            ->get();

        $resources = [];
        $groupedRooms = $rooms->groupBy('building');

        foreach ($groupedRooms as $building => $roomList) {
            $children = [];
            foreach ($roomList as $room) {
                $eventColor = $room->type === 'laboratorium' ? '#ff9f43' : '#7367f0';
                $children[] = [
                    'id' => $room->id,
                    'title' => $room->name . " ({$room->capacity})",
                    'capacity' => $room->capacity,
                    'eventColor' => $eventColor
                ];
            }
            $resources[] = [
                'id' => 'gedung_' . Str::slug($building),
                'title' => $building ?? 'Gedung Lain',
                'children' => $children
            ];
        }

        // 6. Return View (FIXED COMPACT TYPO)
        return view('content.jadwal.index', compact(
            'resources',
            'campus',
            'shift',
            'prodis',
            'unscheduledDistributions',
            'document',
            'isReadOnly',
            'prodiId' // Perbaikan: sebelumnya 'ProdiId' (Typo kapital)
        ));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'campus' => 'required|in:kampus_1,kampus_2',
            'shift'  => 'required|in:pagi,malam',
        ]);


        try {
            $activePeriodId = AcademicPeriod::where('is_active', true)->value('id');

            // Cek apakah ada jadwal di Kampus & Shift ini?
            $hasSchedule = Schedule::whereHas('studyClass', function ($q) use ($request) {
                $q->where('shift', $request->shift);
            })->whereHas('room', function ($q) use ($request) {
                $q->where('location', $request->campus);
            })->exists();

            if (!$hasSchedule) {
                return back()->with('error', 'Belum ada jadwal yang dibuat untuk ' . $request->campus . ' shift ' . $request->shift);
            }
            // Buat Dokumen Global (Prodi ID NULL)
            AprovalDocument::updateOrCreate(
                [
                    'academic_period_id' => $activePeriodId,
                    'type'   => 'jadwal_perkuliahan',
                    'campus' => $request->campus,
                    'shift'  => $request->shift,
                ],
                [
                    'prodi_id' => null, // Global document
                    'status' => 'submitted',
                    'action_by_user_id' => Auth::id(),
                    'feedback_message' => null
                ]
            );
            return back()->with('success', 'Jadwal ' . $request->campus . ' (' . ucfirst($request->shift) . ') berhasil diajukan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengajukan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_distribution_id' => 'required|exists:course_distributions,id',
            'course_id'              => 'required|exists:courses,id',
            'study_class_id'         => 'required|exists:study_classes,id',
            'user_id'                => 'required|exists:users,id',
            'room_id'                => 'required|exists:rooms,id',
            'start_time'             => 'required|date',
        ]);

        try {
            $distribution = CourseDistribution::findOrFail($request->course_distribution_id);
            $studyClass = StudyClass::findOrFail($request->study_class_id);
            $newStudentsCount = $studyClass->total_students;

            $isLecturerValid = $distribution->teachingLecturers()
                ->where('users.id', $request->user_id)
                ->exists();

            if (!$isLecturerValid) {
                return response()->json([
                    'success' => false,
                    'message' => "Error: Dosen yang dipilih tidak terdaftar sebagai pengajar mata kuliah ini."
                ], 422);
            }
            // 1. Parsing Waktu
            $startTimeISO = Carbon::parse($request->start_time);
            $dayName      = $startTimeISO->format('l');
            $startTime    = $startTimeISO->format('H:i');

            $course = Course::findOrFail($request->course_id);
            $room   = Room::findOrFail($request->room_id);

            $requiredTags = $course->required_tags ?? [];
            $roomTags     = $room->facility_tags ?? [];

            if (is_string($requiredTags)) $requiredTags = json_decode($requiredTags, true) ?? [];
            if (is_string($roomTags))     $roomTags     = json_decode($roomTags, true) ?? [];

            $missingTags = array_diff($requiredTags, $roomTags);

            if (!empty($missingTags)) {
                return response()->json([
                    'success' => false,
                    'message' => "Ruangan tidak memadai! Matkul butuh: " . implode(', ', $missingTags)
                ], 422);
            }


            $course = Course::findOrFail($request->course_id);
            $sks = $course->sks_total;

            if ($sks > 1) {
                $effectiveSks = $sks - 1;
            } else {
                $effectiveSks = 1; // Minimal 1 sesi tatap muka
            }
            $requiredMinutes = $effectiveSks * 50;

            $studyClass = StudyClass::findOrFail($request->study_class_id);
            $isKaryawan = $studyClass->shift === 'malam';

            $daySlots = TimeSlots::forDay($dayName, $isKaryawan)
                ->orderBy('start_time')
                ->get();

            if ($daySlots->isEmpty()) {
                $jenisKelas = $isKaryawan ? 'Kelas Malam/Karyawan' : 'Kelas Pagi/Reguler';
                return response()->json([
                    'success' => false,
                    'message' => "Gagal: {$jenisKelas} tidak memiliki jadwal kuliah di hari {$dayName}."
                ], 422);
            }

            $startIndex = $daySlots->search(function ($slot) use ($startTime) {
                return substr($slot->start_time, 0, 5) === $startTime;
            });

            if ($startIndex === false) {
                return response()->json([
                    'success' => false,
                    'message' => "Gagal: Jam {$startTime} tidak valid di grid waktu."
                ], 422);
            }

            $selectedSlotIds = [];
            $accumulatedMinutes = 0;

            foreach ($daySlots->slice($startIndex) as $slot) {
                $start = Carbon::parse($slot->start_time);
                $end   = Carbon::parse($slot->end_time);
                // Pakai abs() biar aman dari minus
                $slotDuration = abs($end->getTimestamp() - $start->getTimestamp()) / 60;

                $selectedSlotIds[] = $slot->id;
                $accumulatedMinutes += $slotDuration;

                if ($accumulatedMinutes >= $requiredMinutes) break;
            }

            if ($accumulatedMinutes < $requiredMinutes) {
                return response()->json([
                    'success' => false,
                    'message' => "Waktu tidak cukup! Butuh {$requiredMinutes} menit."
                ], 422);
            }

            // 6. Cek Bentrok
            $clashError = $this->checkClash(
                $dayName,
                $selectedSlotIds,
                $request->room_id,
                $request->user_id,
                $request->study_class_id,
                $request->course_id,
                $newStudentsCount
            );

            if ($clashError) {
                return response()->json(['success' => false, 'message' => $clashError], 422);
            }

            // 8. SIMPAN (Versi Bersih)
            $schedule = Schedule::create([
                'course_distribution_id' => $request->course_distribution_id,
                'study_class_id' => $request->study_class_id,
                'course_id'      => $request->course_id,
                'room_id'        => $request->room_id,
                'user_id'        => $request->user_id,
                'day'            => $dayName,
                'time_slot_ids'  => $selectedSlotIds,
                // Kolom status & description SUDAH DIHAPUS
            ]);

            return response()->json([
                'success' => true,
                'schedule_id' => $schedule->id,
                'message' => 'Jadwal berhasil disimpan!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function resize(Request $request, $id)
    {
        try {
            $schedule = Schedule::findOrFail($id);
            $newStart = Carbon::parse($request->start_time);
            $newEnd   = Carbon::parse($request->end_time);
            $dayName  = $newStart->format('l');
            $studyClass = StudyClass::findOrFail($request->study_class_id);
            $newStudentsCount = $studyClass->total_students;
            $isKaryawan = $schedule->studyClass->shift === 'malam';


            $slots = TimeSlots::forDay($dayName, $isKaryawan)
                ->whereTime('start_time', '>=', $newStart->format('H:i:s'))
                ->whereTime('start_time', '<', $newEnd->format('H:i:s'))
                ->orderBy('start_time')
                ->pluck('id')
                ->toArray();

            if (empty($slots)) {
                return response()->json([
                    'success' => false,
                    'message' => "Gagal resize: Tidak ada slot valid antara jam " . $newStart->format('H:i') . " s/d " . $newEnd->format('H:i')
                ], 422);
            }

            $clashError = $this->checkClash(
                $dayName,
                $selectedSlotIds,
                $request->room_id,
                $schedule->user_id,
                $schedule->study_class_id,
                $schedule->course_id,     // <--- Parameter Baru
                $newStudentsCount,        // <--- Parameter Baru
                $schedule->id             // Exclude ID sendiri
            );

            if ($clashError) {
                return response()->json(['success' => false, 'message' => $clashError], 422);
            }

            // 5. Update Database
            $schedule->update([
                'time_slot_ids' => $slots
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Durasi jadwal diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'System Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'room_id'    => 'required|exists:rooms,id',
            'start_time' => 'required|date',
        ]);

        try {
            $schedule = Schedule::with(['course', 'studyClass', 'room'])->findOrFail($id);
            $newStudentsCount = $schedule->studyClass->total_students;

            // 1. Ambil Data Durasi / SKS dari Course Terkait
            // Kita butuh tahu berapa menit yang dibutuhkan
            $course = $schedule->course;
            $sks = $course->sks_total;

            if ($sks > 1) {
                $effectiveSks = $sks - 1;
            } else {
                $effectiveSks = 1; // Minimal 1 sesi tatap muka
            }
            $requiredMinutes = $effectiveSks * 50;

            // 2. Parsing Waktu Baru
            $startTimeISO = Carbon::parse($request->start_time);
            $dayName      = $startTimeISO->format('l');
            $startTime    = $startTimeISO->format('H:i');

            // 3. Ambil Slot yang Tersedia untuk Hari Itu
            // Kita perlu tahu jenis kelas (Pagi/Malam) dari StudyClass terkait
            $isKaryawan = $schedule->studyClass->shift === 'malam';

            $daySlots = TimeSlots::forDay($dayName, $isKaryawan)
                ->orderBy('start_time')
                ->get();

            // 4. Cari Posisi Slot Awal
            $startIndex = $daySlots->search(function ($slot) use ($startTime) {
                return substr($slot->start_time, 0, 5) === $startTime;
            });

            if ($startIndex === false) {
                return response()->json(['success' => false, 'message' => "Jam {$startTime} tidak valid."], 422);
            }

            // 5. Akumulasi Slot Sesuai Durasi (PERBAIKAN UTAMA DI SINI)
            $selectedSlotIds = [];
            $accumulatedMinutes = 0;

            foreach ($daySlots->slice($startIndex) as $slot) {
                $start = Carbon::parse($slot->start_time);
                $end   = Carbon::parse($slot->end_time);
                $slotDuration = abs($end->getTimestamp() - $start->getTimestamp()) / 60;

                $selectedSlotIds[] = $slot->id;
                $accumulatedMinutes += $slotDuration;

                // Stop jika durasi sudah cukup
                if ($accumulatedMinutes >= $requiredMinutes) break;
            }

            // Validasi jika waktu tidak cukup (misal dipindah ke jam terakhir)
            if ($accumulatedMinutes < $requiredMinutes) {
                return response()->json(['success' => false, 'message' => "Waktu sisa tidak cukup untuk {$sks} SKS."], 422);
            }

            $clashError = $this->checkClash(
                $dayName,
                $selectedSlotIds,
                $request->room_id,
                $schedule->user_id,
                $schedule->study_class_id,
                $schedule->course_id,     // <--- Parameter Baru
                $newStudentsCount,        // <--- Parameter Baru
                $schedule->id             // Exclude ID sendiri
            );

            if ($clashError) {
                return response()->json(['success' => false, 'message' => $clashError], 422);
            }

            // 7. Update Database
            $schedule->update([
                'room_id'       => $request->room_id,
                'day'           => $dayName,
                'time_slot_ids' => $selectedSlotIds,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dipindahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memindahkan jadwal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $schedule = Schedule::findOrFail($id);

            // Hapus data
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dihapus dan dikembalikan ke antrean.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEvents(Request $request)
    {
        // Parameter dari FullCalendar (Range tanggal yang sedang dilihat user)
        $startStr = $request->input('start'); // Ex: 2026-01-26
        $endStr   = $request->input('end');   // Ex: 2026-02-01

        // Load Semua TimeSlot untuk referensi (biar tidak query berulang-ulang)
        // Kita jadikan Key By ID supaya mudah ambilnya: $slots[1]->start_time
        $allSlots = TimeSlots::all()->keyBy('id');

        // Ambil Jadwal dari Database
        // Eager Load relasi agar ringan
        $schedules = Schedule::with(['course', 'studyClass.prodi', 'lecturer', 'room'])
            ->get();

        $events = [];

        // LOOPING UTAMA: Konversi "Senin" menjadi "2026-01-27"
        foreach ($schedules as $schedule) {

            // 1. Tentukan Tanggal Konkret
            // Kita cari tanggal di dalam range (startStr s/d endStr) yang harinya sesuai dengan $schedule->day
            $targetDate = $this->findDateForDay($startStr, $endStr, $schedule->day);

            if (!$targetDate) continue; // Skip jika hari tidak ada di range view

            // 2. Hitung Waktu Mulai & Selesai dari Array TimeSlot IDs
            // $schedule->time_slot_ids contohnya [1, 2, 3]
            $slotIds = $schedule->time_slot_ids ?? [];

            if (empty($slotIds)) continue;

            // Ambil Slot Pertama (Start) dan Terakhir (End)
            $firstSlotId = $slotIds[0];
            $lastSlotId  = end($slotIds);

            // Cek apakah slot ada di master data
            if (!isset($allSlots[$firstSlotId]) || !isset($allSlots[$lastSlotId])) continue;

            $startTime = $allSlots[$firstSlotId]->start_time; // 08:00:00
            $endTime   = $allSlots[$lastSlotId]->end_time;    // 10:30:00

            // Gabungkan Tanggal + Jam -> ISO8601 (2026-01-27T08:00:00)
            $startDateTime = $targetDate->format('Y-m-d') . 'T' . $startTime;
            $endDateTime   = $targetDate->format('Y-m-d') . 'T' . $endTime;

            $startC = Carbon::parse($startDateTime);
            $endC   = Carbon::parse($endDateTime);
            $durationMinutes = $startC->diffInMinutes($endC);

            $events[] = [
                'id' => $schedule->id,
                'resourceId' => $schedule->room_id, // Masuk ke baris ruangan mana?
                'title' => $schedule->course->name . ' - ' . $schedule->studyClass->name,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'description' => $schedule->lecturer->name ?? 'Belum ada Dosen',
                'extendedProps' => [
                    // Panggil Accessor full_name dari Model StudyClass
                    'fullClassName' => $schedule->studyClass->full_name,
                    'semester'      => $schedule->studyClass->semester,     // <--- Tambah ini
                    'prodiCode'     => $schedule->studyClass->prodi->code,
                    'courseName' => $schedule->course->name,
                    'courseCode' => $schedule->course->code,
                    'dosenName' => $schedule->lecturer->name ?? 'Belum ada Dosen',
                    'sks' => $schedule->course->sks_total,
                    'location' => $schedule->room->name . ' (' . $schedule->room->building . ')',
                    'jam_mulai' => substr($startTime, 0, 5), // 08:00
                    'jam_selesai' => substr($endTime, 0, 5), // 10:30
                    'durasi' => $durationMinutes . ' Menit'
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * Helper: Mencari tanggal spesifik dari nama hari di dalam range
     * Misal: Cari tanggal "Monday" di antara "2026-01-26" s/d "2026-02-01"
     */
    private function findDateForDay($startStr, $endStr, $dayName)
    {
        $start = Carbon::parse($startStr);
        $end   = Carbon::parse($endStr);

        // Map nama hari Inggris (Database) ke Carbon integer
        // Carbon: 0=Sunday, 1=Monday, ..., 6=Saturday
        $dayMap = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6
        ];

        if (!isset($dayMap[$dayName])) return null;

        $targetDayInt = $dayMap[$dayName];

        // Loop dari start sampai end untuk cari hari yang pas
        $current = $start->copy();
        while ($current->lte($end)) {
            if ($current->dayOfWeek === $targetDayInt) {
                return $current;
            }
            $current->addDay();
        }

        return null;
    }

    /**
     * Cek Bentrok dengan Logika Smart Merging
     */
    private function checkClash($day, $slotIds, $roomId, $userId, $classId, $courseId, $newStudentsCount, $excludeScheduleId = null)
    {
        $query = Schedule::with(['studyClass', 'course', 'room'])
            ->where('day', $day);

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        $existingSchedules = $query->get();

        foreach ($existingSchedules as $existing) {
            $intersect = array_intersect($existing->time_slot_ids ?? [], $slotIds);
            if (empty($intersect)) continue;

            if ($existing->room_id == $roomId) {
                $isSameLecturer = ($existing->user_id == $userId);
                $isSameCourse   = ($existing->course_id == $courseId);

                if ($isSameLecturer && $isSameCourse) {
                    $currentOccupants = $existingSchedules->filter(function ($item) use ($roomId, $intersect) {
                        return $item->room_id == $roomId && !empty(array_intersect($item->time_slot_ids, $intersect));
                    })->sum(function ($item) {
                        return $item->studyClass->total_students ?? 0;
                    });

                    $totalStudents = $currentOccupants + $newStudentsCount;
                    $roomCapacity  = $existing->room->capacity;

                    if ($totalStudents > $roomCapacity) {
                        return "Gagal Gabung: Ruangan penuh! (Total Mhs: $totalStudents, Kapasitas: $roomCapacity).";
                    }


                    continue;
                }

                if (!$isSameLecturer) {
                    return "BENTROK RUANGAN! Ruang dipakai dosen {$existing->lecturer->name}.";
                }
                if (!$isSameCourse) {
                    return "BENTROK RUANGAN! Sedang dipakai kuliah {$existing->course->name}.";
                }
            }

            if ($existing->user_id == $userId) {
                return "BENTROK DOSEN! Dosen sedang mengajar di {$existing->room->name}.";
            }

            if ($existing->study_class_id == $classId) {
                return "BENTROK KELAS! {$existing->studyClass->name} sudah ada jadwal {$existing->course->name}.";
            }
        }

        return null; // Aman
    }

    public function show(Request $request)
    {
        $campus = $request->input('campus', 'kampus_1');
        $shift  = $request->input('shift', 'pagi');
        $prodiId = $request->input('prodi_id');
        $semester = $request->input('semester');

        $activePeriodId = AcademicPeriod::where('is_active', true)->value('id');

        // 1. Definisikan Slot Waktu (Master Data Grid)
        // Format: [Label Jam, Start, End]
        $slotsPagi = [
            ['08:00', '08:50'],
            ['08:50', '09:40'],
            ['09:40', '10:30'],
            ['10:30', '11:20'],
            ['11:20', '12:10'],
            ['13:00', '13:50'],
            ['13:50', '14:40'],
            ['14:40', '15:30'],
            ['15:30', '16:20']
        ];

        // Shift Malam (Senin-Jumat beda, Sabtu ikut pagi)
        // Kita buat master slot malam standar dulu
        $slotsMalam = [
            ['13:00', '13:50'],
            ['13:50', '14:40'],
            ['14:40', '15:30'],
            ['15:30', '16:20'],
            ['16:20', '17:00'],
            ['17:00', '17:30'],
            ['17:30', '18:00'],
            ['18:00', '18:30'],
            ['18:30', '19:00'],
            ['19:00', '19:30'],
            ['19:30', '20:00']
        ];

        // Pilih Slot Berdasarkan Filter
        $masterSlots = ($shift === 'malam') ? $slotsMalam : $slotsPagi;

        // 2. Query Data Jadwal
        $query = Schedule::query()
            ->with(['course', 'studyClass', 'lecturer', 'room'])
            ->whereHas('room', fn($q) => $q->where('location', $campus))
            ->whereHas('courseDistribution', fn($q) => $q->where('academic_period_id', $activePeriodId))
            ->whereHas('studyClass', function ($q) use ($shift, $prodiId, $semester) {
                // Filter Shift (Penting!)
                $q->where('shift', $shift);
                if ($prodiId) $q->where('prodi_id', $prodiId);
                if ($semester) $q->where('semester', $semester);
            });

        $schedules = $query->get();

        // 3. Ambil Ruangan yang TERPAKAI saja (Agar tidak terlalu lebar)
        $usedRoomIds = $schedules->pluck('room_id')->unique();

        $rooms = Room::whereIn('id', $usedRoomIds)
            ->orderBy('building')
            ->orderBy('name')
            ->get();

        // 4. CHUNKING (PENTING UNTUK PDF)
        // Karena kertas terbatas, kita bagi ruangan misal per 6 ruangan satu tabel
        $roomChunks = $rooms->chunk(6);

        // 5. PETA MATRIKS (Mapping Data ke Grid)
        // Struktur: $scheduleMatrix[Hari][IndexSlot][RoomId] = DataJadwal
        $scheduleMatrix = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        foreach ($schedules as $sched) {
            // Ambil Slot ID pertama dari jadwal ini
            $firstSlotId = $sched->time_slot_ids[0] ?? null;
            if (!$firstSlotId) continue;

            // Cari jadwal ini masuk ke "Master Slot" nomor berapa?
            // Kita bandingkan jam mulainya.
            $startTime = TimeSlots::find($firstSlotId)->start_time; // ex: 08:00:00
            $startHi = substr($startTime, 0, 5); // 08:00

            // Cari index di masterSlots
            foreach ($masterSlots as $index => $slotVal) {

                if ($this->isSlotInSchedule($sched, $slotVal[0])) {
                    $scheduleMatrix[$sched->day][$index][$sched->room_id] = $sched;
                }
            }
        }

        $prodis = Prodi::all();

        return view('content.jadwal.show', compact(
            'roomChunks',
            'masterSlots',
            'scheduleMatrix',
            'days',
            'campus',
            'shift',
            'prodiId',
            'semester',
            'prodis'
        ));
    }

    private function isSlotInSchedule($schedule, $slotStartTime)
    {
        $slotIds = $schedule->time_slot_ids;
        $dbSlots = TimeSlots::whereIn('id', $slotIds)->get();

        foreach ($dbSlots as $dbSlot) {
            // Format H:i (08:00)
            if (substr($dbSlot->start_time, 0, 5) === $slotStartTime) return true;
        }
        return false;
    }

    public function autoGenerate(Request $request, AutoScheduleService $service)
    {
        $request->validate([
            'campus' => 'required',
            'shift'  => 'required',
        ]);

        $queryDelete = Schedule::query()
            ->whereHas('studyClass', function ($q) use ($request) {
                $q->where('shift', $request->shift);

                if ($request->prodi_id) {
                    $q->where('prodi_id', $request->prodi_id);
                }

                $q->whereHas('prodi', fn($p) => $p->where('primary_campus', $request->campus));
            });

        $queryDelete->delete();

        // Panggil Service
        $result = $service->generate(
            $request->campus,
            $request->shift,
            $request->prodi_id
        );

        if ($result['status']) {
            $msg = $result['message'];
            if (!empty($result['logs'])) {
                // Tampilkan log error jika ada matkul yang gagal
                $msg .= " | Detail Gagal: " . implode(', ', array_slice($result['logs'], 0, 3)) . "...";
            }
            return back()->with('success', $msg);
        } else {
            return back()->with('error', $result['message']);
        }
    }

    public function printPDF(Request $request) {}
}

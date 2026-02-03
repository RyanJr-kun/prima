<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Schedule;
use App\Models\TimeSlots;
use App\Models\CourseDistribution;
use App\Models\AcademicPeriod;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoScheduleService
{
    protected $logs = [];
    protected $successCount = 0;
    protected $failCount = 0;
    protected $mergedCount = 0;

    // Tracker Beban (Load Balancing)
    protected $lecturerDailyLoad = [];
    protected $classDailyLoad = [];
    protected $dayGlobalLoad = [];

    const MAX_LECTURER_SESSIONS_PER_DAY = 4;
    const MAX_STUDENT_SESSIONS_PER_DAY = 6;

    public function generate($campus, $shift, $prodiId = null)
    {
        // Reset Variables
        $this->logs = [];
        $this->successCount = 0;
        $this->failCount = 0;
        $this->mergedCount = 0;
        $this->lecturerDailyLoad = [];
        $this->classDailyLoad = [];

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($days as $d) $this->dayGlobalLoad[$d] = 0;

        // Load Existing Load
        $existingSchedules = Schedule::with('studyClass')->get();
        foreach ($existingSchedules as $sch) {
            $this->incrementTracker($sch->user_id, $sch->study_class_id, $sch->day);
        }

        DB::beginTransaction();

        try {
            $activePeriodId = AcademicPeriod::where('is_active', true)->value('id');

            $approvedProdiIds = \App\Models\AprovalDocument::where('academic_period_id', $activePeriodId)
                ->where('type', 'distribusi_matkul')
                ->where('status', 'approved_direktur')
                ->pluck('prodi_id')
                ->toArray();

            if (empty($approvedProdiIds)) return ['status' => false, 'message' => 'Belum ada Dokumen disetujui.'];

            // Query Distributions
            $query = CourseDistribution::query()
                ->where('academic_period_id', $activePeriodId)
                ->has('teachingLecturers')
                ->doesntHave('schedule')
                ->with(['course', 'studyClass', 'teachingLecturers'])
                ->whereHas('studyClass', function ($q) use ($shift, $prodiId, $campus, $approvedProdiIds) {
                    $q->where('shift', $shift);
                    $q->whereIn('prodi_id', $approvedProdiIds);
                    if ($prodiId) $q->where('prodi_id', $prodiId);
                    $q->whereHas('prodi', fn($p) => $p->where('primary_campus', $campus));
                });

            // Filter Ignore & SKS
            $query->whereHas('course', function ($q) {
                $ignoredKeywords = ['Praktik Industri', 'Magang', 'Skripsi', 'Tugas Akhir', 'KKN', 'PKL'];
                $q->where(function ($sub) use ($ignoredKeywords) {
                    foreach ($ignoredKeywords as $keyword) $sub->where('name', 'NOT LIKE', "%{$keyword}%");
                });
                $q->whereRaw('(COALESCE(sks_teori, 0) + COALESCE(sks_praktik, 0) + COALESCE(sks_lapangan, 0)) <= 6');
            });

            $distributions = $query->get();
            if ($distributions->isEmpty()) return ['status' => false, 'message' => 'Tidak ada matkul valid.'];

            // Sorting
            $sortedDistributions = $distributions->sortByDesc(function ($dist) {
                $needsLab = str_contains(json_encode($dist->course->required_tags), 'lab') ? 1000 : 0;
                $sks = $dist->course->sks_total * 100;
                $students = $dist->studyClass->total_students;
                return $needsLab + $sks + $students;
            });

            // Resources
            $rooms = Room::where('location', $campus)->orderBy('capacity', 'asc')->get();
            if ($rooms->isEmpty()) return ['status' => false, 'message' => "Gagal: Tidak ada ruangan di $campus"];

            // Time Slots
            $isKaryawan = ($shift === 'malam');
            $timeSlotsGrouped = [];
            foreach ($days as $day) {
                $checkShift = ($day == 'Saturday' && $shift == 'malam') ? false : $isKaryawan;
                $slots = TimeSlots::forDay($day, $checkShift)->orderBy('start_time')->get();
                if ($slots->isNotEmpty()) $timeSlotsGrouped[$day] = $slots;
            }
            if (empty($timeSlotsGrouped)) return ['status' => false, 'message' => "Tidak ada Time Slot tersedia."];

            // ---------------------------------------------------------
            // ALGORITMA UTAMA
            // ---------------------------------------------------------
            foreach ($sortedDistributions as $dist) {
                $assigned = false;
                $sks = $dist->course->sks_total;
                $effectiveSks = ($sks > 1) ? $sks - 1 : 1;
                $requiredMinutes = $effectiveSks * 50;

                $studentsCount = $dist->studyClass->total_students;
                $lecturerId = $dist->teachingLecturers->first()->id ?? null;
                $classId = $dist->studyClass->id;
                $semester = $dist->studyClass->semester;
                $prodiIdClass = $dist->studyClass->prodi_id;

                // Tags Processing
                $rawTags = $dist->course->required_tags;
                $requiredTags = is_array($rawTags) ? $rawTags : json_decode($rawTags ?? '[]', true);
                if (!is_array($requiredTags)) $requiredTags = [];

                // Cek apakah ini Matkul Umum (General Only atau Kosong)
                $isGeneralCourse = (empty($requiredTags) || (count($requiredTags) === 1 && $requiredTags[0] === 'general'));

                // --- LANGKAH A: MERGING STRATEGY (STRICT SHIFT) ---
                if ($lecturerId) {
                    $mergeCandidate = Schedule::with('room')
                        ->where('course_id', $dist->course_id)
                        ->where('user_id', $lecturerId)
                        ->whereHas('studyClass', function ($q) use ($prodiIdClass, $semester, $shift) {
                            $q->where('prodi_id', $prodiIdClass)
                                ->where('semester', $semester)
                                ->where('shift', $shift); // <--- HARAM DIGABUNG KALAU BEDA SHIFT
                        })
                        ->get()
                        ->filter(function ($existingSchedule) use ($studentsCount) {
                            $currentOccupants = Schedule::where('room_id', $existingSchedule->room_id)
                                ->where('day', $existingSchedule->day)
                                ->whereJsonContains('time_slot_ids', $existingSchedule->time_slot_ids[0])
                                ->with('studyClass')->get()->sum('studyClass.total_students');
                            return $existingSchedule->room->capacity >= ($currentOccupants + $studentsCount);
                        })
                        ->first();

                    if ($mergeCandidate) {
                        Schedule::create([
                            'course_distribution_id' => $dist->id,
                            'study_class_id' => $classId,
                            'course_id' => $dist->course_id,
                            'room_id' => $mergeCandidate->room_id,
                            'user_id' => $lecturerId,
                            'day' => $mergeCandidate->day,
                            'time_slot_ids' => $mergeCandidate->time_slot_ids
                        ]);
                        $this->incrementTracker($lecturerId, $classId, $mergeCandidate->day);
                        $this->successCount++;
                        $this->mergedCount++;
                        $assigned = true;
                        continue;
                    }
                }

                // --- LANGKAH B: SEARCH NEW ROOM ---
                $debugReasons = [];
                $sortedDays = collect($timeSlotsGrouped)->sortBy(fn($slots, $day) => $this->dayGlobalLoad[$day] ?? 0);

                foreach ($sortedDays as $day => $slots) {
                    if ($assigned) break;

                    // Limit Check
                    if ($lecturerId && ($this->lecturerDailyLoad[$lecturerId][$day] ?? 0) >= self::MAX_LECTURER_SESSIONS_PER_DAY) {
                        $debugReasons['lecturer_limit'] = "Dosen Full";
                        continue;
                    }
                    if (($this->classDailyLoad[$classId][$day] ?? 0) >= self::MAX_STUDENT_SESSIONS_PER_DAY) {
                        $debugReasons['student_limit'] = "Mhs Full";
                        continue;
                    }

                    // LOOP RUANGAN
                    foreach ($rooms as $room) {
                        if ($assigned) break;

                        // [LOGIKA BARU] STRICT GENERAL ROOM CHECK
                        $rawRoomTags = $room->facility_tags;
                        $roomTags = is_array($rawRoomTags) ? $rawRoomTags : json_decode($rawRoomTags ?? '[]', true);
                        if (!is_array($roomTags)) $roomTags = [];

                        if ($isGeneralCourse) {
                            // Jika Matkul Umum, Ruangan HARUS murni general (tidak boleh punya tag spesial)
                            // Contoh: Punya tag 'computer' -> Haram buat matkul umum
                            $specializedTags = array_diff($roomTags, ['general']);
                            if (!empty($specializedTags)) {
                                $debugReasons['tags_strict'] = "Matkul Umum dilarang pakai Lab Spesifik";
                                continue;
                            }
                        } else {
                            // Jika Matkul Spesifik, Cek kelengkapan tags seperti biasa
                            $specificRequirements = array_diff($requiredTags, ['general']);
                            if (!empty($specificRequirements) && !empty(array_diff($specificRequirements, $roomTags))) {
                                $debugReasons['tags'] = "Fasilitas kurang";
                                continue;
                            }
                        }

                        // Cek Kapasitas
                        if ($room->capacity < $studentsCount) {
                            $debugReasons['capacity'] = "Kapasitas kurang";
                            continue;
                        }

                        // LOOP SLOT
                        foreach ($slots as $startIndex => $slot) {
                            $potentialSlots = [];
                            $accumulatedMinutes = 0;
                            $checkSlots = $slots->slice($startIndex);
                            $prevEndTime = null;
                            $isConsecutive = true;

                            foreach ($checkSlots as $checkSlot) {
                                if ($prevEndTime && $checkSlot->start_time != $prevEndTime) {
                                    $isConsecutive = false;
                                    break;
                                }
                                $startC = Carbon::parse(date('Y-m-d') . ' ' . $checkSlot->start_time);
                                $endC   = Carbon::parse(date('Y-m-d') . ' ' . $checkSlot->end_time);
                                $accumulatedMinutes += abs($endC->diffInMinutes($startC));
                                $potentialSlots[] = $checkSlot->id;
                                $prevEndTime = $checkSlot->end_time;
                                if ($accumulatedMinutes >= $requiredMinutes) break;
                            }

                            if (!$isConsecutive || $accumulatedMinutes < $requiredMinutes) continue;

                            // CEK BENTROK
                            if ($this->isSlotSafe($day, $potentialSlots, $room->id, $lecturerId, $classId, $prodiIdClass, $semester, $dist->course_id)) {

                                Schedule::create([
                                    'course_distribution_id' => $dist->id,
                                    'study_class_id' => $classId,
                                    'course_id' => $dist->course_id,
                                    'room_id' => $room->id,
                                    'user_id' => $lecturerId,
                                    'day' => $day,
                                    'time_slot_ids' => $potentialSlots
                                ]);

                                $this->incrementTracker($lecturerId, $classId, $day);
                                $this->successCount++;
                                $assigned = true;
                                break;
                            } else {
                                $debugReasons['clash'] = "Bentrok";
                            }
                        }
                    }
                }

                if (!$assigned) {
                    $this->failCount++;
                    $mainReason = implode(' | ', array_unique($debugReasons));
                    $this->logs[] = "Gagal: {$dist->course->name} - {$mainReason}";
                }
            }

            DB::commit();
            return [
                'status' => true,
                'message' => "Selesai. Sukses: {$this->successCount}, Gagal: {$this->failCount}",
                'logs' => $this->logs
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function incrementTracker($lecturerId, $classId, $day)
    {
        if (!isset($this->dayGlobalLoad[$day])) $this->dayGlobalLoad[$day] = 0;
        $this->dayGlobalLoad[$day]++;

        if ($lecturerId) {
            if (!isset($this->lecturerDailyLoad[$lecturerId][$day])) $this->lecturerDailyLoad[$lecturerId][$day] = 0;
            $this->lecturerDailyLoad[$lecturerId][$day]++;
        }
        if ($classId) {
            if (!isset($this->classDailyLoad[$classId][$day])) $this->classDailyLoad[$classId][$day] = 0;
            $this->classDailyLoad[$classId][$day]++;
        }
    }

    private function isSlotSafe($day, $slotIds, $roomId, $lecturerId, $classId, $prodiId, $semester, $courseId)
    {
        $existingSchedules = Schedule::with(['studyClass'])->where('day', $day)->get();

        foreach ($existingSchedules as $existing) {
            $intersect = array_intersect($existing->time_slot_ids ?? [], $slotIds);
            if (empty($intersect)) continue;

            // Bentrok Ruang & Dosen Standard
            if ($existing->room_id == $roomId) return false;
            if ($lecturerId && $existing->user_id == $lecturerId) return false;
            if ($existing->study_class_id == $classId) return false;

            // Bentrok Semester (Block System)
            if ($existing->studyClass->prodi_id == $prodiId && $existing->studyClass->semester == $semester) {
                return false;
            }
        }
        return true;
    }
}

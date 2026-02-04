@extends('layouts/contentNavbarLayout')
@section('title', 'Jadwal Perkuliahan - PRIMA')

@section('page-style')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-bg-subtle: #e7e7ff;
            --text-dark: #566a7f;
            --text-muted: #a1acb8;
            --border-color: #eceef1;
            --bg-header: #ffffff;
        }

        .jadwal-wrapper {
            max-height: 75vh;
            overflow: auto;
            position: relative;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.1);
        }

        /* Custom Scrollbar */
        .jadwal-wrapper::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .jadwal-wrapper::-webkit-scrollbar-track {
            background: #f5f5f9;
        }

        .jadwal-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-family: 'Public Sans', sans-serif;
        }

        .table-modern th,
        .table-modern td {
            border-right: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
            padding: 0;
        }

        /* --- STICKY HEADERS (RUANGAN) --- */
        .table-modern thead th {
            position: sticky;
            top: 0;
            background-color: var(--bg-header);
            z-index: 100;
            height: 65px;
            vertical-align: middle;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            border-bottom: 2px solid var(--border-color);
            min-width: 180px;
            /* Lebar kolom ruangan lebih lega */
        }

        .room-header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        /* --- STICKY COLUMNS (HARI & JAM) --- */
        .table-modern .col-day {
            position: sticky;
            left: 0;
            background-color: #fff;
            z-index: 101;
            border-right: 2px solid var(--border-color);
            font-weight: 800;
            color: var(--primary-color);
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            width: 50px;
            min-width: 50px;
            text-align: center;
            vertical-align: middle;
            letter-spacing: 2px;
            font-size: 0.85rem;
        }

        .table-modern .col-jam {
            position: sticky;
            left: 50px;
            /* Sesuaikan dengan width col-day */
            background-color: #f8f9fa;
            z-index: 101;
            border-right: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--text-dark);
            min-width: 100px;
            text-align: center;
            vertical-align: middle;
            font-size: 0.8rem;
        }

        /* Fix Z-Index Corner */
        .table-modern thead th.col-day,
        .table-modern thead th.col-jam {
            z-index: 105;
            background: #fff;
        }

        /* --- CONTENT CARDS --- */
        .cell-wrapper {
            padding: 6px;
            height: 100%;
            min-height: 90px;
            /* Tinggi minimal sel */
        }

        .schedule-card {
            background: #fff;
            border-left: 4px solid var(--primary-color);
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(67, 89, 113, 0.06);
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .schedule-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(105, 108, 255, 0.15);
            border-left-width: 6px;
        }

        /* Background Accent Gradient */
        .schedule-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(to right, var(--primary-bg-subtle), transparent 70%);
            opacity: 0.4;
            z-index: 0;
            pointer-events: none;
        }
    </style>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-primary mb-1">
                <i class='bx bx-calendar-event me-2'></i>Jadwal Perkuliahan
            </h4>
            <p class="text-muted mb-0 small">Monitoring penggunaan ruangan dan jadwal perkuliahan.</p>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('jadwal-perkuliahan.show') }}" class="row g-2 align-items-center">
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Kampus</label>
                    <select name="campus" class="form-select form-select-sm select2" onchange="this.form.submit()">
                        <option value="kampus_1" {{ $campus == 'kampus_1' ? 'selected' : '' }}>Kampus 1</option>
                        <option value="kampus_2" {{ $campus == 'kampus_2' ? 'selected' : '' }}>Kampus 2</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Shift Kelas</label>
                    <select name="shift" class="form-select form-select-sm select2" onchange="this.form.submit()">
                        <option value="pagi" {{ $shift == 'pagi' ? 'selected' : '' }}>Kelas Pagi</option>
                        <option value="malam" {{ $shift == 'malam' ? 'selected' : '' }}>Kelas Malam</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Program Studi</label>
                    <select name="prodi_id" class="form-select form-select-sm select2" onchange="this.form.submit()">
                        <option value="">-- Semua Prodi --</option>
                        @foreach ($prodis as $prodi)
                            <option value="{{ $prodi->id }}" {{ $prodiId == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->jenjang }} {{ $prodi->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Semester</label>
                    <select name="semester" class="form-select form-select-sm select2" onchange="this.form.submit()">
                        <option value="">-- Smt --</option>
                        @foreach (range(1, 8) as $sem)
                            <option value="{{ $sem }}" {{ $semester == $sem ? 'selected' : '' }}>Semester
                                {{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- MATRIX SCROLL AREA --}}
    <div class="jadwal-wrapper shadow-sm">
        @foreach ($roomChunks as $chunkIndex => $rooms)
            @if (!$loop->first)
                <div class="p-2 bg-light text-center border-top border-bottom">
                    <small class="fw-bold text-muted">HALAMAN SELANJUTNYA (RUANGAN {{ $rooms->first()->name }} -
                        {{ $rooms->last()->name }})</small>
                </div>
            @endif

            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="col-day">HARI</th>
                        <th class="col-jam">JAM</th>
                        @foreach ($rooms as $room)
                            <th>
                                <div class="room-header-content">
                                    <span class="fw-bold text-dark mb-1"
                                        style="font-size: 0.9rem;">{{ $room->name }}</span>
                                    <span class="badge bg-label-secondary rounded-pill" style="font-size: 0.7rem;">
                                        {{ $room->capacity }} Kursi
                                    </span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $daysIndo = [
                            'Monday' => 'SENIN',
                            'Tuesday' => 'SELASA',
                            'Wednesday' => 'RABU',
                            'Thursday' => 'KAMIS',
                            'Friday' => 'JUMAT',
                            'Saturday' => 'SABTU',
                        ];
                    @endphp

                    @foreach ($days as $day)
                        @foreach ($masterSlots as $slotIndex => $slotTime)
                            <tr>
                                {{-- KOLOM HARI --}}
                                @if ($slotIndex === 0)
                                    <td rowspan="{{ count($masterSlots) }}" class="col-day">
                                        {{ $daysIndo[$day] }}
                                    </td>
                                @endif

                                {{-- KOLOM JAM --}}
                                <td class="col-jam">
                                    {{ $slotTime[0] }} - {{ $slotTime[1] }}
                                </td>

                                {{-- KOLOM RUANGAN --}}
                                @foreach ($rooms as $room)
                                    @php
                                        $schedules = $scheduleMatrix[$day][$slotIndex][$room->id] ?? [];
                                    @endphp

                                    <td>
                                        <div class="cell-wrapper">
                                            @if (!empty($schedules))
                                                @php
                                                    $firstSched = $schedules[0];
                                                @endphp
                                                <div class="schedule-card"
                                                    title="{{ $firstSched->course->name }} - {{ $firstSched->lecturer->name }}">
                                                    <div style="position: relative; z-index: 1;">
                                                        <div class="fw-bold text-dark mb-1"
                                                            style="font-size: 0.8rem; line-height: 1.3;">
                                                            {{ $firstSched->course->name }}
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                                            @foreach ($schedules as $sch)
                                                                <span class="badge bg-label-primary"
                                                                    style="font-size: 0.65rem;">
                                                                    {{ $sch->studyClass->full_name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                        <div class="text-muted mt-1 d-flex align-items-center"
                                                            style="font-size: 0.7rem;">
                                                            <i class="bx bx-user me-1" style="font-size: 0.8rem;"></i>
                                                            {{ \Illuminate\Support\Str::limit($firstSched->lecturer->name, 15) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        {{-- Garis Pemisah Hari --}}
                        <tr>
                            <td colspan="{{ count($rooms) + 2 }}"
                                style="height: 8px; background: #f5f5f9; padding:0; border:none;"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    </div>

@endsection

@section('page-script')
    <script type="module">
        // Fungsi inisialisasi Select2
        const initSelect2 = () => {
            // Cek apakah jQuery dan Select2 sudah siap
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: $this.find('option[value=""]').length >
                            0, // Hanya allowClear jika ada value=""
                        width: '100%',
                        minimumResultsForSearch: 10 // Sembunyikan search box jika opsi < 10
                    });
                });
            } else {
                // Jika belum siap, coba lagi dalam 100ms
                setTimeout(initSelect2, 100);
            }
        };

        // Jalankan saat script dimuat
        initSelect2();
    </script>
@endsection

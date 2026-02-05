@extends('layouts/commonMaster')
@section('title', 'Jadwal Kuliah Publik - PRIMA')

@section('vendor-style')
    {{-- 1. Import Font & Animation CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">


    {{-- 2. Custom CSS untuk Style Premium --}}
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f6f8;
            /* Background abu-abu sangat muda agar card pop-up */
        }

        /* Gradient Text */
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Card Modernization */
        .card-modern {
            border: none;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.15);
        }

        /* Header Card dengan Gradient Halus */
        .card-header-gradient {
            background: linear-gradient(to right, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        }

        /* Badge Styling */
        .badge-soft-primary {
            background-color: rgba(102, 126, 234, 0.1);
            color: #667eea;
            font-weight: 600;
        }

        .badge-soft-info {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }

        /* List Group Item Custom */
        .list-group-item-custom {
            border: none;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
            padding: 1.25rem;
            transition: background 0.2s;
        }

        .list-group-item-custom:last-child {
            border-bottom: none;
        }

        .list-group-item-custom:hover {
            background-color: #fafbfc;
        }

        /* Tombol Utama */
        .btn-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s;
        }

        .btn-gradient-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.5);
            color: white;
        }

        /* Select2 Customization (Agar rounded dan modern) */
        .select2-container--default .select2-selection--single {
            border-radius: 12px;
            height: 48px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
        }
    </style>
@endsection

@section('layoutContent')
    <div class="container-xxl flex-grow-1 container-p-y pb-5">

        {{-- 1. HEADER SECTION --}}
        <div class="text-center mb-5 mt-3" data-aos="fade-down">
            <span class="badge badge-soft-primary rounded-pill mb-2 px-3 py-2">
                <i class='bx bx-calendar-star me-1'></i> Akademik
            </span>
            <h2 class="display-6 fw-bold mb-1">Jadwal Perkuliahan</h2>
            <p class="text-muted fs-5">
                Periode: <strong class="text-dark">{{ $activePeriod->name ?? 'Tidak Aktif' }}</strong>
            </p>
        </div>

        @if (!$activePeriod)
            <div class="row justify-content-center" data-aos="zoom-in">
                <div class="col-md-6">
                    <div class="alert alert-danger text-center border-0 shadow-sm rounded-4 p-4" role="alert">
                        <i class='bx bx-error-circle fs-1 mb-2'></i><br>
                        <span class="fw-bold fs-5">Periode Akademik Belum Aktif</span>
                        <p class="mb-0 mt-2">Mohon hubungi bagian akademik untuk informasi lebih lanjut.</p>
                    </div>
                </div>
            </div>
        @else
            {{-- 2. FILTER SECTION (Floating Card) --}}
            <div class="row justify-content-center mb-5">
                <div class="col-lg-10" data-aos="fade-up" data-aos-delay="100">
                    <div class="card card-modern p-2">
                        <div class="card-body p-4">
                            <form action="{{ route('public.jadwal') }}" method="GET">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold text-uppercase small text-muted ms-1">Cari Kelas
                                            Mahasiswa</label>

                                        <select name="class_id" class="form-select select2 border-start-0" required>
                                            <option value="">-- Ketik Nama Kelas (Contoh: TI 1A) --</option>
                                            @foreach ($classes as $cls)
                                                <option value="{{ $cls->id }}"
                                                    {{ $classId == $cls->id ? 'selected' : '' }}>
                                                    {{ $cls->prodi->jenjang ?? '' }} {{ $cls->prodi->name ?? '' }} -
                                                    {{ $cls->name }} (Angkatan {{ $cls->angkatan }})
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                    <div class="col-md-4 d-flex gap-2">
                                        <button type="submit"
                                            class="btn btn-gradient-primary w-100 py-2 rounded-3 fw-bold">
                                            Tampilkan Jadwal
                                        </button>
                                        @if ($classId)
                                            <a href="{{ route('public.jadwal') }}"
                                                class="btn btn-light border w-25 py-2 rounded-3" data-bs-toggle="tooltip"
                                                title="Reset Filter">
                                                <i class='bx bx-refresh fs-4'></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. CONTENT SECTION --}}
            @if (empty($classId))
                {{-- State Awal (Empty State) --}}
                <div class="text-center py-5" data-aos="fade-up" data-aos-delay="200">
                    <div class="d-flex justify-content-center mb-0">
                        {{-- Panggil File Lokal Menggunakan asset() --}}
                        <dotlottie-player src="{{ asset('assets/json/cat.json') }}" background="transparent" speed="1"
                            style="width: 300px; height: 300px; filter: drop-shadow(0 10px 20px rgba(102, 126, 234, 0.2));"
                            loop autoplay>
                        </dotlottie-player>
                    </div>

                    <h4 class="fw-bold text-dark mb-2">Siap Mencari Jadwal?</h4>
                    <p class="text-muted fs-5" style="max-width: 500px; margin: 0 auto; line-height: 1.6;">
                        Silakan pilih <strong>Kelas</strong> pada form di atas untuk menampilkan detail jadwal perkuliahan
                        mingguan.
                    </p>
                </div>
            @elseif($groupedSchedules->isEmpty())
                {{-- State Data Kosong --}}
                <div class="text-center py-5" data-aos="fade-in">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png"
                        alt="Empty" width="200" style="opacity: 0.8">
                    <h5 class="mt-4 fw-bold">Jadwal Belum Tersedia</h5>
                    <p class="text-muted">Belum ada data jadwal yang didistribusikan untuk kelas ini.</p>
                </div>
            @else
                {{-- RESULT GRID --}}
                <div class="row g-4">
                    @php
                        $daysIndo = [
                            'Monday' => 'Senin',
                            'Tuesday' => 'Selasa',
                            'Wednesday' => 'Rabu',
                            'Thursday' => 'Kamis',
                            'Friday' => 'Jumat',
                            'Saturday' => 'Sabtu',
                        ];
                        $delay = 100; // Untuk animasi bertingkat
                    @endphp

                    @foreach ($daysIndo as $engDay => $indoDay)
                        @if (isset($groupedSchedules[$engDay]) && $groupedSchedules[$engDay]->count() > 0)
                            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $delay }}">
                                <div class="card card-modern h-100">
                                    {{-- Card Header --}}
                                    <div
                                        class="card-header card-header-gradient d-flex justify-content-between align-items-center py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-white p-2 rounded-circle shadow-sm me-3 text-primary">
                                                <i class='bx bx-calendar-event fs-4'></i>
                                            </div>
                                            <h5 class="mb-0 fw-bold text-dark">{{ $indoDay }}</h5>
                                        </div>
                                        <span class="badge bg-primary rounded-pill px-3">
                                            {{ $groupedSchedules[$engDay]->count() }} Sesi
                                        </span>
                                    </div>

                                    {{-- Card Body (List) --}}
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @foreach ($groupedSchedules[$engDay] as $schedule)
                                                @php
                                                    // Ambil Slot Waktu (Fix Array/Json)
                                                    $rawSlots = is_array($schedule->time_slot_ids)
                                                        ? $schedule->time_slot_ids
                                                        : json_decode($schedule->time_slot_ids, true);
                                                    $rawSlots = $rawSlots ?? [];

                                                    $slots = \App\Models\TimeSlots::whereIn('id', $rawSlots)
                                                        ->orderBy('start_time')
                                                        ->get();

                                                    $startTime = $slots->first()
                                                        ? substr($slots->first()->start_time, 0, 5)
                                                        : '-';
                                                    $endTime = $slots->last()
                                                        ? substr($slots->last()->end_time, 0, 5)
                                                        : '-';
                                                @endphp

                                                <div class="list-group-item list-group-item-custom">
                                                    {{-- Baris 1: Waktu & Lokasi --}}
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="badge badge-soft-info rounded-pill">
                                                            <i class='bx bx-time-five me-1'></i> {{ $startTime }} -
                                                            {{ $endTime }}
                                                        </span>
                                                        <small class="text-muted fw-semibold">
                                                            <i class='bx bx-map text-secondary'></i>
                                                            {{ $schedule->room->name ?? 'TBA' }}
                                                        </small>
                                                    </div>

                                                    {{-- Baris 2: Nama Matkul --}}
                                                    <h6 class="mb-2 fw-bold text-dark lh-sm">
                                                        {{ $schedule->course->name ?? '-' }}
                                                    </h6>

                                                    {{-- Baris 3: Dosen & Kode --}}
                                                    <div
                                                        class="d-flex align-items-center mt-3 pt-2 border-top border-light">
                                                        <div class="avatar avatar-xs me-2">
                                                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                                                <i class='bx bx-user'></i>
                                                            </span>
                                                        </div>
                                                        <div class="w-100">
                                                            <small class="d-block fw-bold text-dark text-truncate"
                                                                style="max-width: 200px;">
                                                                {{ $schedule->lecturer->name ?? 'Dosen Belum Ditentukan' }}
                                                            </small>
                                                            <small class="d-block text-muted" style="font-size: 0.75rem;">
                                                                <i class="bx bx-location"></i> &nbsp;Lokasi :
                                                                {{ $schedule->room->location ?? '-' }}
                                                                - {{ $schedule->room->building ?? '-' }}
                                                                - Lantai {{ $schedule->room->floor ?? '..' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $delay += 100; @endphp
                        @endif
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endsection

@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    <script>
        // Init AOS Animation
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });
    </script>
    <script type="module">
        const initSelect2 = () => {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: $this.find('option[value=""]').length >
                            0,
                        width: '100%',
                        minimumResultsForSearch: 10
                    });
                });
            } else {
                setTimeout(initSelect2, 100);
            }
        };
        initSelect2();
    </script>
@endsection

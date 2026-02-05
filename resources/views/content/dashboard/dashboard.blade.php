@extends('layouts/contentNavbarLayout')

@section('title', 'Jadwal & Booking Ruangan')

@section('vendor-style')
    {{-- Flatpickr CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    {{-- Font Plus Jakarta Sans --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
            --hover-shadow: 0 1rem 3rem rgba(18, 38, 63, 0.08);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        /* Welcome Card Decoration */
        .card-welcome {
            background: var(--primary-gradient);
            border: none;
            border-radius: 1rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .card-welcome::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: -1;
        }

        .card-welcome::before {
            content: '';
            position: absolute;
            bottom: -30px;
            left: 20%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            z-index: -1;
        }

        /* Modern Cards */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
        }

        /* Schedule Item Styling */
        .schedule-item {
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            transition: all 0.3s ease;
            margin-bottom: 0.75rem;
            border-radius: 0.5rem;
        }

        .schedule-item:hover {
            background: #fff;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        /* Tabs Styling */
        .nav-pills .nav-link {
            color: #697a8d;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            transition: all 0.3s;
        }

        .nav-pills .nav-link.active {
            background: var(--primary-gradient);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* Room Card */
        .room-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 1rem;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            background: white;
            position: relative;
            overflow: hidden;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
            border-color: #667eea;
        }

        .btn-booking-card {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            font-weight: 600;
            border: none;
        }

        .btn-booking-card:hover {
            background: var(--primary-gradient);
            color: white;
        }

        /* Modal Enhancements */
        .transition-all {
            transition: all 0.3s ease-in-out;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.3) !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .form-floating>.form-control:focus~label::after {
            background-color: transparent;
        }

        /* Class pembungkus agar style custom tidak merusak elemen default Sneat */
        .modal-academic-custom .modal-content {
            border-radius: 0.5rem;
            /* Standar Sneat */
        }

        .modal-academic-custom .modal-header-decoration {
            background: var(--primary-gradient);
            padding: 2rem 1rem;
            border-radius: 0.5rem 0.5rem 0 0;
            text-align: center;
            color: white;
        }

        .modal-academic-custom .icon-circle {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .modal-academic-custom .room-indicator {
            background: #f0f2f4;
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            color: #566a7f;
            margin-top: -25px;
            /* Narik ke atas agar overlap sedikit */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 3px solid white;
        }

        /* Memperbaiki tampilan switch agar sejajar dengan standar Sneat */
        .modal-academic-custom .full-day-box {
            border: 1px solid #d9dee3;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        {{-- 1. WELCOME & RINGKASAN --}}
        <div class="col-lg-12 mb-4">
            <div class="card card-welcome text-white">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="text-white mb-1 fw-bold">{{ $greeting }}, {{ $user->name }}! 👋</h3>
                        <p class="mb-0 opacity-75 fs-6">
                            Hari ini Anda memiliki <span
                                class="badge bg-white text-primary rounded-pill fw-bold fs-6 mx-1">{{ $todaySchedules->count() }}</span>
                            sesi mengajar.
                            <br class="d-none d-md-block">Semangat mencerdaskan bangsa!
                        </p>
                    </div>
                    <div class="d-none d-md-block">
                        <i class='bx bx-calendar-star' style="font-size: 4rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. JADWAL HARI INI --}}
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center pb-2">
                    <h5 class="mb-0 fw-bold"><i class='bx bx-time-five text-primary me-2'></i>Jadwal Hari Ini</h5>
                    <a href="{{ route('dashboard.jadwal-saya') }}" class="btn btn-sm btn-label-primary rounded-pill">
                        Detail <i class='bx bx-right-arrow-alt ms-1'></i>
                    </a>
                </div>
                <div class="card-body">
                    @forelse($todaySchedules as $schedule)
                        <div class="schedule-item p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-label-primary rounded fw-bold">
                                    {{ $schedule->real_time['start_formatted'] ?? '' }}
                                </span>
                                <small class="text-muted fw-semibold">{{ $schedule->room->name }}</small>
                            </div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $schedule->course->name }}</h6>
                            <div class="d-flex align-items-center text-muted small">
                                <i class='bx bx-group me-1'></i> {{ $schedule->studyClass->name }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class='bx bx-coffee-togo text-secondary' style="font-size: 3rem;"></i>
                            </div>
                            <h6 class="text-muted">Tidak ada jadwal hari ini.</h6>
                            <small class="text-muted">Nikmati waktu luang Anda!</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 3. ROOM CHECKER & BOOKING --}}
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom-0 pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h5 class="mb-1 fw-bold">Ketersediaan Ruangan</h5>
                            <small class="text-muted">Cek dan booking ruangan dengan mudah.</small>
                        </div>

                        {{-- FILTER TANGGAL --}}
                        <form action="" method="GET" class="position-relative">
                            <i
                                class='bx bx-calendar position-absolute top-50 start-0 translate-middle-y ms-3 text-muted'></i>
                            <input type="date" name="date"
                                class="form-control ps-5 rounded-pill border-0 bg-label-secondary fw-semibold"
                                style="min-width: 180px;" value="{{ $filterDate }}" onchange="this.form.submit()">
                        </form>
                    </div>

                    {{-- TABS --}}
                    <div class="mt-4">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active d-flex align-items-center gap-2" data-bs-toggle="tab"
                                    data-bs-target="#navs-kosong">
                                    <i class='bx bx-check-circle'></i>
                                    Kosong <span
                                        class="badge bg-white text-primary ms-1 rounded-circle">{{ $availableRooms->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link d-flex align-items-center gap-2" data-bs-toggle="tab"
                                    data-bs-target="#navs-terpakai">
                                    <i class='bx bx-x-circle'></i>
                                    Terpakai
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body pt-2">
                    <div class="tab-content p-0">
                        {{-- TAB KOSONG --}}
                        <div class="tab-pane fade show active" id="navs-kosong">
                            <div class="row g-3">
                                @foreach ($availableRooms as $room)
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="room-card p-3 text-center h-100 d-flex flex-column">
                                            <div class="mb-3">
                                                <div class="avatar avatar-md mx-auto mb-2">
                                                    <span class="avatar-initial rounded-circle bg-label-success">
                                                        <i class='bx bxs-door-open fs-4'></i>
                                                    </span>
                                                </div>
                                                <h6 class="card-title mb-1 fw-bold">{{ $room->name }}</h6>
                                                <small class="text-muted">Kapasitas: {{ $room->capacity }} Org</small>
                                            </div>
                                            <button class="btn btn-booking-card w-100 mt-auto rounded-pill btn-booking"
                                                data-bs-toggle="modal" data-bs-target="#modalBooking"
                                                data-room-id="{{ $room->id }}" data-room-name="{{ $room->name }}">
                                                <i class='bx bx-plus me-1'></i> Booking
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- TAB TERPAKAI --}}
                        <div class="tab-pane fade" id="navs-terpakai">
                            @if ($bookedRooms->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless">
                                        <thead class="border-bottom">
                                            <tr class="text-muted text-uppercase small">
                                                <th>Ruangan</th>
                                                <th class="text-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bookedRooms as $room)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $room->name }}</td>
                                                    <td class="text-end">
                                                        <span class="badge bg-label-secondary">Full Booked</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <p class="text-muted">Tidak ada ruangan yang terpakai penuh hari ini.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL BOOKING --}}
    <div class="modal fade modal-academic-custom" id="modalBooking" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="{{ route('booking.store') }}" method="POST">
                @csrf

                <div class="modal-header-decoration">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="icon-circle">
                        <i class='bx bx-calendar-check fs-2 text-white'></i>
                    </div>
                    <h4 class="modal-title fw-bold text-white">Reservasi Ruangan</h4>
                    <p class="text-white-50 small mb-0">Silahkan lengkapi form dibawah ini</p>
                </div>

                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="room-indicator">
                            <i class='bx bxs-door-open me-1'></i>
                            <span id="displayRoomName">Pilih Ruangan</span>
                        </div>
                    </div>

                    <input type="hidden" name="room_id" id="inputRoomId">

                    <div class="row g-3">
                        <div class="col-12 mb-2">
                            <label class="form-label" for="bookingDate">Tanggal Booking</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <input type="text" name="booking_date" id="bookingDate"
                                    class="form-control flatpickr-date" placeholder="Pilih Tanggal"
                                    value="{{ $filterDate }}" required />
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="full-day-box d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-0 fw-semibold">Booking Seharian Full</h6>
                                    <small class="text-muted">Jam operasional (07:00 - 17:00)</small>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_full_day" id="checkFullDay"
                                        value="1">
                                </div>
                            </div>
                        </div>

                        <div id="timeInputsContainer" class="col-12">
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label" for="startTime">Jam Mulai</label>
                                    <input type="text" name="start_time" id="startTime"
                                        class="form-control flatpickr-time text-center" placeholder="00:00" required />
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="endTime">Jam Selesai</label>
                                    <input type="text" name="end_time" id="endTime"
                                        class="form-control flatpickr-time text-center" placeholder="00:00" required />
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="purposeInput">Keperluan / Keterangan</label>
                            <textarea name="purpose" class="form-control" id="purposeInput" rows="3"
                                placeholder="Contoh: Rapat Koordinasi Dosen atau Kelas Pengganti" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">
                        <i class='bx bx-check-double me-1'></i> Konfirmasi Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Init Flatpickr with cleaner styling
        flatpickr(".flatpickr-time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            static: true // Prevents scrolling issues in modal
        });

        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d",
            static: true
        });

        // Handle Checkbox Full Day Animation
        const checkFullDay = document.getElementById('checkFullDay');
        const timeInputsContainer = document.getElementById('timeInputsContainer');
        const timeInputFields = document.querySelectorAll('.flatpickr-time');

        checkFullDay.addEventListener('change', function() {
            if (this.checked) {
                timeInputsContainer.style.maxHeight = '0';
                timeInputsContainer.style.opacity = '0';
                timeInputsContainer.style.marginBottom = '0';
                timeInputFields.forEach(el => el.removeAttribute('required'));
            } else {
                timeInputsContainer.style.maxHeight = '100px';
                timeInputsContainer.style.opacity = '1';
                timeInputsContainer.style.marginBottom = '1rem';
                timeInputFields.forEach(el => el.setAttribute('required', 'true'));
            }
        });

        // Populate Modal Data
        document.querySelectorAll('.btn-booking').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('inputRoomId').value = this.dataset.roomId;
                document.getElementById('displayRoomName').textContent = this.dataset.roomName;
            });
        });
    </script>
@endsection

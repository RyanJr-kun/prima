@extends('layouts/contentNavbarLayout')

@section('title', 'Jadwal & Booking Ruangan')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

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

        .room-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 1rem;
            transition: all 0.3s;
            background: white;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(18, 38, 63, 0.08);
            border-color: #667eea;
        }

        .modal-header-decoration {
            background: var(--primary-gradient);
            padding: 2rem 1rem;
            border-radius: 0.5rem 0.5rem 0 0;
            text-align: center;
            color: white;
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        /* Badge untuk jam sibuk */
        .busy-badge {
            font-size: 0.7rem;
            white-space: normal;
            text-align: left;
            line-height: 1.4;
            display: block;
            background: #fff3cd;
            color: #856404;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ffeeba;
        }
    </style>
@endsection


@section('content')

    {{-- Toast Notification --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        @if (session('success'))
            <div id="successToast" class="bs-toast bg-primary toast fade hide" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="toast-header">
                    <i class="icon-base bx bx-bell icon-xs me-2"></i>
                    <span class="fw-medium me-auto">Notifikasi</span>
                    <small>Baru Saja!</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div id="errorToast" class="bs-toast bg-danger toast fade hide" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="toast-header">
                    <i class="icon-base bx bx-bell icon-xs me-2"></i>
                    <span class="fw-medium me-auto">Notifikasi</span>
                    <small>Baru Saja!</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">{{ session('error') }}</div>
            </div>
        @endif
    </div>

    <div class="row">
        {{-- 1. WELCOME --}}
        <div class="col-lg-12 mb-4">
            <div class="card card-welcome text-white">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="text-white mb-1 fw-bold">{{ $greeting }}, {{ $user->name }}! 👋</h3>
                        <p class="mb-0 opacity-75 fs-6">
                            Hari ini Anda memiliki <span
                                class="badge bg-white text-primary rounded-pill fw-bold mx-1">{{ $todaySchedules->count() }}</span>
                            sesi mengajar.
                        </p>
                    </div>
                    <div class="d-none d-md-block"><i class='bx bx-calendar-star'
                            style="font-size: 4rem; opacity: 0.8;"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-none border">
                <div class="card-header-academic">
                    <div class="header-title-wrapper">
                        <div class="icon-box">
                            <i class='bx bx-calendar-event'></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">
                                Jadwal Hari Ini
                                <small>Agenda & Perkuliahan</small>
                            </h5>
                        </div>
                    </div>

                    <div class="header-action-wrapper">
                        <a href="{{ route('dashboard.jadwal-saya') }}" class="btn-view-all">
                            <span>Lihat Semua</span>
                            <i class='bx bx-right-arrow-alt'></i>
                        </a>
                    </div>
                </div>

                <div class="card-body schedule-card-wrapper">
                    <div class="schedule-container">
                        @forelse($todaySchedules as $schedule)
                            <div class="schedule-item-modern">
                                <div class="schedule-header">
                                    <span class="time-badge">
                                        {{ $schedule->real_time['start_formatted'] ?? '00:00' }} -
                                        {{ $schedule->real_time['end_formatted'] ?? '00:00' }}
                                    </span>
                                    <span class="room-label">
                                        <i class='bx bx-map-pin'></i> {{ $schedule->room->name }}
                                    </span>
                                </div>

                                <span class="course-title">{{ $schedule->course->name }}</span>

                                <div class="class-info">
                                    <span class="class-name">
                                        <i class='bx bx-group'></i> {{ $schedule->studyClass->full_name }}
                                    </span>
                                </div>

                                <i class='bx bx-right-arrow-alt icon-go'></i>
                            </div>
                        @empty
                            <div class="schedule-empty-state">
                                <div class="empty-icon-wrapper">
                                    <i class='bx bx-coffee'></i>
                                </div>
                                <h6 class="text-dark fw-semibold">Tidak Ada Jadwal</h6>
                                <p class="small text-muted text-center">Hari ini Anda bisa sedikit santai atau fokus pada
                                    administrasi lainnya.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. ROOM CHECKER & BOOKING --}}
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h5 class="mb-1 fw-bold">Ketersediaan Ruangan</h5>
                            <small class="text-muted">Cek jam kosong sebelum booking.</small>
                        </div>
                        <form action="" method="GET" class="d-flex gap-2">
                            <input type="date" class="form-control flatpickr-date" name="date"
                                value="{{ $filterDate }}" onchange="this.form.submit()">
                            <select name="campus" class="form-select select2" onchange="this.form.submit()">
                                <option value="">Semua Kampus</option>
                                <option value="kampus_1" {{ $filterCampus == 'kampus_1' ? 'selected' : '' }}>Kampus 1
                                </option>
                                <option value="kampus_2" {{ $filterCampus == 'kampus_2' ? 'selected' : '' }}>Kampus 2
                                </option>
                            </select>
                        </form>
                    </div>

                    <ul class="nav nav-pills mt-3 mb-2" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#navs-avail">
                                <i class='bx bx-check-circle me-1'></i> Tersedia ({{ $displayRooms->count() }})
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body pt-2">
                    <div class="tab-content p-0">
                        <div class="tab-pane fade show active" id="navs-avail">
                            <div class="row g-3">
                                @foreach ($displayRooms as $room)
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="room-card p-3 h-100 d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="fw-bold mb-0">{{ $room->name }}</h6>
                                                @if ($room->availability_color == 'success')
                                                    <span class="badge bg-success rounded-pill"><i
                                                            class='bx bx-check'></i></span>
                                                @else
                                                    <span class="badge bg-warning text-dark rounded-pill"><i
                                                            class='bx bx-time'></i></span>
                                                @endif
                                            </div>

                                            <div class="mb-3 flex-grow-1">
                                                <small class="text-muted d-block mb-2">{{ $room->building }} -
                                                    Lt.{{ $room->floor }}</small>

                                                @if ($room->availability_color == 'success')
                                                    <div class="alert alert-success py-1 px-2 mb-0 small">
                                                        <i class='bx bx-check-circle me-1'></i> Kosong Seharian
                                                    </div>
                                                @else
                                                    <button type="button"
                                                        class="btn btn-sm btn-label-warning w-100 rounded-pill d-flex align-items-center justify-content-center gap-1"
                                                        data-bs-toggle="popover" data-bs-html="true"
                                                        data-bs-trigger="hover focus" data-bs-placement="top"
                                                        title="<div class='text-center fw-bold'>Detail Pemakaian</div>"
                                                        data-bs-content="{{ $room->popover_content }}">
                                                        <i class='bx bx-list-ul'></i> Lihat Pemakai
                                                    </button>
                                                @endif
                                            </div>

                                            <button class="btn btn-outline-primary w-100 btn-sm rounded-pill btn-booking"
                                                data-bs-toggle="modal" data-bs-target="#modalBooking"
                                                data-room-id="{{ $room->id }}" data-room-name="{{ $room->name }}"
                                                data-busy-notes="{{ $room->busy_notes }}">
                                                Booking
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- List Booking Saya --}}
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Pengajuan Booking Saya</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($myBookings as $booking)
                                <tr>
                                    <td>{{ $booking->room->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                                    <td>
                                        @if ($booking->status == 'pending')
                                            <span class="badge bg-warning">Menunggu</span>
                                        @elseif($booking->status == 'approved')
                                            <span class="badge bg-success">Disetujui</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>{{ $booking->rejection_note ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBooking" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="{{ route('booking.store') }}" method="POST">
                @csrf
                <div class="modal-header-decoration">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"></button>
                    <div class="icon-circle"><i class='bx bx-calendar-check fs-2 text-white'></i></div>
                    <h4 class="modal-title fw-bold text-white">Reservasi Ruangan</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h5 id="displayRoomName" class="mb-1 text-primary fw-bold">Pilih Ruangan</h5>
                        <div id="modalBusyAlert" class="alert alert-warning d-none text-start mx-auto mt-2"
                            style="max-width: 90%;">
                            <small><strong><i class='bx bx-info-circle'></i> Perhatian:</strong> Ruangan ini sudah terpakai
                                pada jam:</small>
                            <br>
                            <small id="displayBusyNotes" class="fw-bold"></small>
                        </div>
                    </div>

                    <input type="hidden" name="room_id" id="inputRoomId">
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="text" name="booking_date" class="form-control flatpickr-date"
                            value="{{ $filterDate }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Jam Mulai</label>
                            <input type="text" name="start_time" class="form-control form-control-sm flatpickr-time"
                                placeholder="00:00">

                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam Selesai</label>
                            <input type="text" name="end_time" class="form-control form-control-sm flatpickr-time"
                                placeholder="00:00">

                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan</label>
                        <textarea name="purpose" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer pt-0 border-top-0 justify-content-center">
                    <button type="submit" class="btn btn-primary px-5">Ajukan Booking</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr(".flatpickr-time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d"
        });

        // Script untuk oper data ke modal
        document.querySelectorAll('.btn-booking').forEach(btn => {
            btn.addEventListener('click', function() {
                // Set ID dan Nama Ruangan
                document.getElementById('inputRoomId').value = this.dataset.roomId;
                document.getElementById('displayRoomName').textContent = this.dataset.roomName;

                // Cek apakah ada jadwal sibuk
                const busyNotes = this.dataset.busyNotes;
                const alertBox = document.getElementById('modalBusyAlert');
                const noteText = document.getElementById('displayBusyNotes');

                if (busyNotes && busyNotes !== 'Bisa digunakan kapan saja') {
                    noteText.textContent = busyNotes;
                    alertBox.classList.remove('d-none');
                } else {
                    alertBox.classList.add('d-none');
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Popover
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    container: 'body', // Penting agar tidak terpotong overflow card
                    trigger: 'hover focus'
                })
            })

            const successToast = document.getElementById('successToast');
            if (successToast) {
                new bootstrap.Toast(successToast, {
                    delay: 3000
                }).show();
            }
            const errorToast = document.getElementById('errorToast');
            if (errorToast) {
                new bootstrap.Toast(errorToast, {
                    delay: 3000
                }).show();
            }
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

@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Manajemen Ruangan')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        /* --- VARIABLES --- */
        :root {
            --ad-primary: #696cff;
            --ad-secondary: #8592a3;
            --ad-success: #71dd37;
            --ad-warning: #ffab00;
            --ad-danger: #ff3e1d;
            --ad-dark: #233446;
            --ad-body: #f5f5f9;
            --ad-card-bg: #ffffff;
            --ad-radius: 16px;
            --ad-shadow: 0 0.5rem 1.5rem rgba(18, 38, 63, 0.05);
            --ad-shadow-hover: 0 1rem 3rem rgba(18, 38, 63, 0.1);
            --ad-font: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--ad-font) !important;
            background-color: var(--ad-body);
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

    <div class="row g-4">
        <div class="col-12">
            <div class="row g-4 mb-4 align-items-stretch">
                <div class="col-12 col-xl-8">
                    <div class="admin-header-card admin-header-welcome">
                        <div class="admin-header-welcome-content">
                            <h4 class="header-title text-white">Selamat Datang, Admin! 🎉</h4>
                            <p class="header-subtitle text-white">
                                Saat ini terdapat <strong>{{ $pendingBookings->count() }}</strong> permintaan booking
                                ruangan baru
                                yang menunggu persetujuan Anda.
                            </p>
                            <a href="#pendingTable" class="btn btn-glass text-decoration-none">
                                Tinjau Sekarang <i class='bx bx-right-arrow-alt ms-1'></i>
                            </a>
                        </div>
                        <div class="d-none d-md-block">
                            <img src="{{ asset('assets\img\illustrations\man-with-laptop.png') }}" alt="Admin Illustration"
                                class="admin-header-img">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="row g-4 h-100">
                        <div class="col-sm-6 col-xl-12" style="height: 50%;">
                            <div class="admin-header-card admin-header-stat">
                                <div class="admin-header-icon icon-soft-primary">
                                    <i class='bx bx-buildings'></i>
                                </div>
                                <div>
                                    <h3 class="stat-value">{{ $allRooms->count() }}</h3>
                                    <span class="stat-label">Total Ruangan</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-12" style="height: 50%;">
                            <div class="admin-header-card admin-header-stat">
                                <div class="admin-header-icon icon-soft-warning">
                                    <i class='bx bx-time-five'></i>
                                </div>
                                <div>
                                    <h3 class="stat-value">{{ $pendingBookings->count() }}</h3>
                                    <span class="stat-label">Menunggu Review</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="admin-dash-card" id="pendingTable">
                <div class="admin-dash-table-header">
                    <div>
                        <h5 class="admin-dash-title">Permintaan Booking</h5>
                        <small class="admin-dash-subtitle">Daftar booking yang menunggu persetujuan</small>
                    </div>
                    @if ($pendingBookings->count() > 0)
                        <span class="badge bg-label-warning rounded-pill px-3">{{ $pendingBookings->count() }} Baru</span>
                    @else
                        <span class="badge bg-label-success rounded-pill px-3">Semua Beres</span>
                    @endif
                </div>
                <div class="table-responsive admin-dash-scroll">
                    <table class="admin-dash-table">
                        <thead>
                            <tr>
                                <th>Dosen / Pemohon</th>
                                <th>Detail Booking</th>
                                <th>Tanggal & Waktu</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingBookings as $booking)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($booking->user->name, 0, 2) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span
                                                    class="d-block fw-semibold text-dark">{{ $booking->user->name }}</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    {{ $booking->user->nidn ?? 'Staff' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info mb-1">{{ $booking->room->name }}</span>
                                        <div class="small text-muted text-truncate" style="max-width: 150px;"
                                            title="{{ $booking->purpose }}">
                                            {{ $booking->purpose }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">
                                                {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}
                                            </span>
                                            <small class="text-muted">
                                                @if ($booking->is_full_day)
                                                    <span class="badge bg-label-primary" style="font-size: 0.7em">Full
                                                        Day</span>
                                                @else
                                                    {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                                @endif
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <form action="{{ route('booking.approve', $booking->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="admin-dash-btn-action btn-approve"
                                                    data-bs-toggle="tooltip" title="Setujui">
                                                    <i class='bx bx-check'></i>
                                                </button>
                                            </form>
                                            <button type="button" class="admin-dash-btn-action btn-reject"
                                                data-bs-toggle="modal" data-bs-target="#modalReject"
                                                data-id="{{ $booking->id }}" title="Tolak Permintaan">
                                                <i class='bx bx-x'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class='bx bx-check-circle fs-1 text-muted mb-2'></i>
                                            <p class="text-muted mt-2 small">Tidak ada permintaan booking pending.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


        </div>
        <div class="col-lg-4">
            <div class="admin-dash-card">
                <div class="admin-dash-table-header">
                    <h5 class="admin-dash-title">Aktivitas Terkini</h5>
                    <small class="text-muted">Pantauan Real-time</small>
                </div>

                <div class="p-4 admin-dash-scroll">
                    <ul class="admin-dash-timeline">
                        @forelse ($activities as $act)
                            <li class="admin-dash-timeline-item">
                                <span class="admin-dash-timeline-point bg-{{ $act->color }}">
                                </span>

                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class='bx {{ $act->icon }} text-{{ $act->color }}'></i>
                                            <span class="fw-bold text-dark font-small">{{ $act->name }}</span>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.7rem">
                                            {{ \Carbon\Carbon::parse($act->time)->diffForHumans() }}
                                        </small>
                                    </div>

                                    {{-- Deskripsi Aktivitas --}}
                                    <p class="mb-0 text-muted small ps-4" style="line-height: 1.4;">
                                        {!! $act->desc !!}
                                    </p>
                                </div>
                            </li>
                        @empty
                            <div class="text-center py-5">
                                <img src="https://cdni.iconscout.com/illustration/premium/thumb/sleeping-cat-8236374-6632420.png"
                                    width="80" alt="Sleep" style="opacity: 0.5">
                                <p class="small text-muted mt-2">Belum ada aktivitas terekam.</p>
                            </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="admin-dash-card">
                <div class="admin-dash-table-header flex-wrap gap-2">
                    <div>
                        <h5 class="admin-dash-title">Monitoring Ruangan</h5>
                        <small class="admin-dash-subtitle">Pantau status penggunaan ruangan real-time</small>
                    </div>

                    {{-- Filter Form --}}
                    <form action="" method="GET" class="d-flex gap-2">
                        <select name="campus" class="form-select select2" onchange="this.form.submit()">
                            <option value="">Semua Kampus</option>
                            <option value="kampus_1" {{ $filterCampus == 'kampus_1' ? 'selected' : '' }}>Kampus 1</option>
                            <option value="kampus_2" {{ $filterCampus == 'kampus_2' ? 'selected' : '' }}>Kampus 2</option>
                        </select>
                        <input type="date" name="date" class="form-control flatpickr-date"
                            value="{{ $filterDate }}" onchange="this.form.submit()">
                    </form>
                </div>

                <div class="p-4">
                    <div class="row g-3">
                        @foreach ($allRooms as $room)
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="admin-dash-room-item h-100 d-flex flex-column justify-content-center">

                                    {{-- Status Dot --}}
                                    <span class="admin-dash-status-dot"
                                        style="background-color: {{ $room->status_hari_ini == 'Terpakai' ? 'var(--ad-warning)' : 'var(--ad-success)' }}">
                                    </span>

                                    <div class="mb-2 mt-2">
                                        <i
                                            class='bx {{ $room->status_hari_ini == 'Terpakai' ? 'bxs-user-voice text-warning' : 'bxs-check-shield text-success' }} fs-1'></i>
                                    </div>

                                    <h6 class="mb-1 fw-bold text-dark">{{ $room->name }}</h6>

                                    {{-- LOGIC TAMPILAN POPOVER --}}
                                    @if ($room->status_hari_ini == 'Terpakai')
                                        <div class="mt-2 mb-3">
                                            {{-- Tombol Trigger Popover --}}
                                            <button type="button"
                                                class="btn btn-sm btn-label-warning w-100 rounded-pill d-flex align-items-center justify-content-center gap-1"
                                                data-bs-toggle="popover" data-bs-html="true"
                                                data-bs-trigger="hover focus" data-bs-placement="top"
                                                title="<div class='text-center fw-bold'>Detail Pemakaian</div>"
                                                data-bs-content="{{ $room->popover_content }}">
                                                <i class='bx bx-list-ul'></i> Lihat Pemakai
                                            </button>
                                        </div>
                                    @else
                                        <div class="mb-3 mt-2">
                                            <span class="badge bg-label-success rounded-pill px-3">Available</span>
                                        </div>
                                    @endif

                                    {{-- Footer Card --}}
                                    <div class="mt-auto pt-2 border-top">
                                        <small class="text-muted" style="font-size: 0.7rem">
                                            {{ $room->building }} - Lt.{{ $room->floor }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REJECT --}}
    <div class="modal fade" id="modalReject" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formReject" class="modal-content" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">Tolak Permintaan Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold">Alasan Penolakan</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"
                            placeholder="Contoh: Ruangan sedang direnovasi, atau Jadwal bentrok dengan acara Rektorat..." required></textarea>
                        <div class="form-text text-muted">
                            Alasan ini akan dikirimkan ke email pemohon.
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class='bx bx-x-circle me-1'></i> Tolak Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d"
        });

        document.addEventListener('DOMContentLoaded', function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    container: 'body',
                    trigger: 'hover focus'
                })
            })

            const modalReject = document.getElementById('modalReject');
            modalReject.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const bookingId = button.getAttribute('data-id');
                const form = document.getElementById('formReject');
                form.action = '/booking/' + bookingId + '/reject';
            });

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

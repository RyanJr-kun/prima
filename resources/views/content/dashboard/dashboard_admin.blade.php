@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Manajemen Ruangan')

@section('vendor-style')
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


    {{-- SECTION 2: MAIN CONTENT SPLIT --}}
    <div class="row g-4">
        <div class="col-12">
            {{-- SECTION 1: HEADER & STATS --}}
            <div class="row g-4 mb-4 align-items-stretch"> {{-- align-items-stretch penting agar tinggi sama --}}
                {{-- 1. Welcome Card --}}
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

                        {{-- Gambar Ilustrasi --}}
                        <div class="d-none d-md-block">
                            <img src="{{ asset('assets\img\illustrations\man-with-laptop.png') }}" alt="Admin Illustration"
                                class="admin-header-img">
                        </div>
                    </div>
                </div>

                {{-- 2. Mini Stats Column --}}
                <div class="col-12 col-xl-4">
                    <div class="row g-4 h-100"> {{-- h-100 agar row mengisi penuh tinggi parent --}}

                        {{-- Stat 1: Total Ruangan --}}
                        <div class="col-sm-6 col-xl-12" style="height: 50%;"> {{-- Bagi tinggi jadi 50% di desktop --}}
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

                        {{-- Stat 2: Pending Approval --}}
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
        {{-- LEFT COLUMN: APPROVAL & MONITORING (8/12) --}}
        <div class="col-lg-8">

            {{-- 2A. TABLE APPROVAL --}}
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

                {{-- WRAPPER SCROLL DI SINI --}}
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
                                {{-- ... isi row ... --}}
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png"
                                            alt="Empty" style="width: 80px; opacity: 0.5;"> {{-- Perkecil gambar --}}
                                        <p class="text-muted mt-2 small">Tidak ada permintaan booking pending.</p>
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
                </div>
                <div class="p-4 admin-dash-scroll">
                    <ul class="admin-dash-timeline">
                        @foreach ($activities as $act)
                            <li class="admin-dash-timeline-item">
                                <span class="admin-dash-timeline-point"
                                    style="background-color: {{ $act->activity_type == 'booking' ? 'var(--ad-primary)' : 'var(--ad-success)' }}">
                                </span>
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span
                                            class="fw-bold text-dark font-small">{{ $act->name ?? $act->user->name }}</span>
                                        <small class="text-muted" style="font-size: 0.7rem">
                                            {{ \Carbon\Carbon::parse($act->time)->diffForHumans() }}
                                        </small>
                                    </div>
                                    <p class="mb-0 text-muted small" style="line-height: 1.4;">
                                        {{ $act->activity_desc }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if ($activities->isEmpty())
                        <div class="text-center py-4">
                            <small class="text-muted">Belum ada aktivitas tercatat.</small>
                        </div>
                    @endif
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
                        <select name="campus" class="admin-dash-input" onchange="this.form.submit()">
                            <option value="">Semua Kampus</option>
                            <option value="kampus_1" {{ $filterCampus == 'kampus_1' ? 'selected' : '' }}>Kampus 1</option>
                            <option value="kampus_2" {{ $filterCampus == 'kampus_2' ? 'selected' : '' }}>Kampus 2</option>
                        </select>
                        <input type="date" name="date" class="admin-dash-input" value="{{ $filterDate }}"
                            onchange="this.form.submit()">
                    </form>
                </div>

                <div class="p-4">
                    <div class="row g-3">
                        @foreach ($allRooms as $room)
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="admin-dash-room-item">
                                    {{-- Status Dot --}}
                                    <span class="admin-dash-status-dot"
                                        style="background-color: {{ $room->status_hari_ini == 'Terpakai' ? 'var(--ad-secondary)' : 'var(--ad-success)' }}">
                                    </span>


                                    <div class="mb-2">
                                        <i
                                            class='bx {{ $room->status_hari_ini == 'Terpakai' ? 'bxs-lock-alt text-secondary' : 'bxs-door-open text-primary' }} fs-1'></i>
                                    </div>
                                    <h6 class="mb-1 fw-bold text-dark">{{ $room->name }}</h6>
                                    <small
                                        class="badge {{ $room->status_hari_ini == 'Terpakai' ? 'bg-label-secondary' : 'bg-label-success' }}">
                                        {{ $room->status_hari_ini }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Reject Hidden --}}
    <form id="rejectForm" method="POST" style="display:none">
        @csrf @method('PATCH')
        <input type="hidden" name="reason">
    </form>

    <script>
        // Custom Reject Logic (Bisa diganti SweetAlert jika mau)
        function confirmReject(id) {
            let reason = prompt("Silakan masukkan alasan penolakan:");
            if (reason) {
                let form = document.getElementById('rejectForm');
                form.action = "/booking/" + id + "/reject";
                form.querySelector('input[name="reason"]').value = reason;
                form.submit();
            }
        }
    </script>

@endsection

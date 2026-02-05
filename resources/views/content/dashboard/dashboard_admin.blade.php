@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Manajemen Ruangan')

@section('vendor-style')
    {{-- Mengimpor Font Plus Jakarta Sans agar sama dengan Home --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            --card-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        /* Modern Card Styling */
        .card {
            border: none;
            border-radius: 1rem;
            /* Lebih bulat (rounded-4) */
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 1rem 3rem rgba(18, 38, 63, 0.08);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        .card-header h5 {
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #344767;
        }

        /* Gradient Badge */
        .badge-gradient-warning {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%);
            color: #861616;
            border: none;
        }

        /* Table Styling */
        .table thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 700;
            color: #8898aa;
            border-bottom: 1px solid #f0f2f5;
        }

        .table td {
            vertical-align: middle;
            font-weight: 500;
            padding: 1rem 1.25rem;
        }

        /* Action Buttons */
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-approve {
            background: rgba(113, 221, 55, 0.1);
            color: #71dd37;
        }

        .btn-approve:hover {
            background: #71dd37;
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-reject {
            background: rgba(255, 62, 29, 0.1);
            color: #ff3e1d;
        }

        .btn-reject:hover {
            background: #ff3e1d;
            color: #fff;
            transform: translateY(-2px);
        }

        /* Timeline Customization */
        .timeline .timeline-point-primary {
            background: var(--primary-gradient);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .timeline-event {
            padding-bottom: 1.5rem;
        }

        /* Room Box Styling */
        .room-box {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            transition: all 0.2s;
            background: #fff;
        }

        .room-box:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .room-status-badge {
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')
    {{-- Welcome Banner (Optional, adds "Premium" feel) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-transparent shadow-none border-0">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body py-0">
                            <h4 class="text-primary fw-bold mb-1">Dashboard 👋</h4>
                            <p class="mb-0 text-muted">Pantau aktivitas ruangan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- 1. APPROVAL CARD (Jobdesk Utama Admin) --}}
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Permintaan Booking</h5>
                    @if ($pendingBookings->count() > 0)
                        <span class="badge badge-gradient-warning shadow-sm rounded-pill px-3">
                            <i class='bx bxs-bell-ring bx-tada me-1'></i> {{ $pendingBookings->count() }} Pending
                        </span>
                    @else
                        <span class="badge bg-label-success rounded-pill">All Clear</span>
                    @endif
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Dosen</th>
                                <th>Ruang</th>
                                <th>Jadwal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($pendingBookings as $booking)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($booking->user->name, 0, 2) }}</span>
                                            </div>

                                            <div>
                                                <span class="fw-bold d-block text-dark">{{ $booking->user->name }}</span>
                                                <small class="text-muted" style="font-size: 0.75rem">NIDN:
                                                    {{ $booking->user->nidn ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $booking->room->name }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span
                                                class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}</span>
                                            <small class="text-muted">
                                                <i class='bx bx-time-five me-1'></i>
                                                {{ $booking->is_full_day ? 'Seharian' : \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <form action="{{ route('booking.approve', $booking->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-icon btn-approve"
                                                    data-bs-toggle="tooltip" title="Setujui">
                                                    <i class="bx bx-check fs-4"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-icon btn-reject"
                                                onclick="confirmReject({{ $booking->id }})" data-bs-toggle="tooltip"
                                                title="Tolak">
                                                <i class="bx bx-x fs-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <div class="bg-label-secondary p-3 rounded-circle mb-3">
                                                <i class='bx bx-check-circle fs-1 text-muted'></i>
                                            </div>
                                            <h6 class="text-muted mb-0">Tidak ada permintaan pending saat ini.</h6>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 2. ACTIVITY HISTORY --}}
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Aktivitas Terkini</h5>
                </div>
                <div class="card-body">
                    <ul class="timeline timeline-dashed">
                        @foreach ($activities as $act)
                            <li class="timeline-item timeline-item-transparent ps-4">
                                <span
                                    class="timeline-point {{ $act->activity_type == 'booking' ? 'timeline-point-primary' : 'timeline-point-success' }}"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-1 d-flex justify-content-between">
                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem">
                                            {{ $act->name ?? $act->user->name }}</h6>
                                        <small class="text-muted"
                                            style="font-size: 0.7rem">{{ \Carbon\Carbon::parse($act->time)->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 text-muted" style="font-size: 0.85rem; line-height: 1.5;">
                                        {{ $act->activity_desc }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- 3. FILTER ROOM MONITORING --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-1">Monitoring Ruangan</h5>
                        <small class="text-muted">Cek ketersediaan ruangan secara real-time</small>
                    </div>

                    {{-- Styled Date Picker --}}
                    <form action="" method="GET" class="d-flex align-items-center bg-light rounded px-2 py-1 border">
                        <i class='bx bx-calendar text-muted me-2'></i>
                        <input type="date" name="date"
                            class="form-control form-control-sm border-0 bg-transparent shadow-none ps-0"
                            style="width: 130px; font-weight: 600; color: #566a7f;" value="{{ $filterDate }}"
                            onchange="this.form.submit()">
                    </form>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($allRooms as $room)
                            <div class="col-6 col-md-3 col-lg-2">
                                <div
                                    class="room-box p-3 text-center h-100 d-flex flex-column justify-content-center align-items-center position-relative">

                                    {{-- Status Indicator Dot --}}
                                    <span
                                        class="position-absolute top-0 end-0 mt-2 me-2 p-1 rounded-circle {{ $room->status_hari_ini == 'Terpakai' ? 'bg-secondary' : 'bg-success' }}"></span>

                                    <div class="mb-2">
                                        <i
                                            class='bx {{ $room->status_hari_ini == 'Terpakai' ? 'bxs-lock-alt text-secondary' : 'bxs-door-open text-primary' }} fs-2'></i>
                                    </div>

                                    <strong class="d-block text-dark mb-1">{{ $room->name }}</strong>

                                    <span
                                        class="room-status-badge {{ $room->status_hari_ini == 'Terpakai' ? 'bg-label-secondary text-secondary' : 'bg-label-success text-success' }}">
                                        {{ $room->status_hari_ini }}
                                    </span>
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
        function confirmReject(id) {
            // Menggunakan styling prompt bawaan browser, 
            // tapi bisa diupgrade ke SweetAlert2 jika diinginkan untuk match design
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

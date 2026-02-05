@extends('layouts/contentNavbarLayout')

@section('title', 'Pengaturan Notifikasi')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-align-top">
                <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-md-0 gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pengaturan.index') }}"><i
                                class="icon-base bx bx-user icon-sm me-1_5"></i>
                            Akun</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);"><i
                                class="icon-base bx bx-bell icon-sm me-1_5"></i> Notifikasi</a>
                    </li>
                </ul>
            </div>
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="card">
                        <h5 class="card-header">Preferensi Notifikasi</h5>
                        <div class="card-body">
                            {{-- ALERT SUKSES --}}
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="alert alert-info">
                                Notifikasi sistem akan dikirimkan melalui Email menggunakan layanan Brevo.
                            </div>

                            {{-- FORM UPDATE --}}
                            <form action="{{ route('notifikasi.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <h6>Aktivitas Akademik</h6>

                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notif_jadwal"
                                                id="notif_jadwal"
                                                {{ $settings['notif_jadwal'] ?? false ? 'checked' : '' }} />
                                            <label class="form-check-label" for="notif_jadwal">
                                                Perubahan Jadwal Kuliah
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notif_bkd" id="notif_bkd"
                                                {{ $settings['notif_bkd'] ?? false ? 'checked' : '' }} />
                                            <label class="form-check-label" for="notif_bkd">
                                                Pengingat Batas Waktu BKD
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notif_approval"
                                                id="notif_approval"
                                                {{ $settings['notif_approval'] ?? false ? 'checked' : '' }} />
                                            <label class="form-check-label" for="notif_approval">
                                                Status Approval Dokumen (Disetujui/Ditolak)
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <h6>Keamanan Akun</h6>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="notif_login"
                                                id="notif_login" {{ $settings['notif_login'] ?? false ? 'checked' : '' }} />
                                            <label class="form-check-label" for="notif_login">
                                                Email saya jika ada login dari perangkat baru
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary me-2">Simpan Preferensi</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Riwayat Notifikasi</h5>
                            @if ($user->unreadNotifications->count() > 0)
                                <a href="{{ route('notifikasi.readAll') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-check-double me-1"></i> Tandai Semua Dibaca
                                </a>
                            @endif
                        </div>

                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($notifications as $notif)
                                    <li
                                        class="list-group-item list-group-item-action {{ $notif->read_at ? '' : 'bg-label-secondary' }}">
                                        <a href="{{ $notif->data['url'] ?? '#' }}"
                                            class="d-flex align-items-center text-decoration-none text-body">
                                            {{-- ICON --}}
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <span
                                                        class="avatar-initial rounded-circle bg-label-{{ $notif->data['color'] ?? 'primary' }}">
                                                        <i class="bx {{ $notif->data['icon'] ?? 'bx-bell' }}"></i>
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- KONTEN --}}
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="mb-0">{{ $notif->data['title'] ?? 'Notifikasi' }}</h6>
                                                    <small
                                                        class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                                                </div>
                                                <p class="mb-0 small text-muted">{{ $notif->data['message'] ?? '' }}</p>
                                            </div>

                                            {{-- STATUS DOT (Jika belum baca) --}}
                                            @if (!$notif->read_at)
                                                <div class="flex-shrink-0 ms-2">
                                                    <span class="badge badge-dot bg-primary"></span>
                                                </div>
                                            @endif
                                        </a>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center py-5">
                                        <div class="mb-3">
                                            <i class="bx bx-bell-off fs-1 text-muted"></i>
                                        </div>
                                        <p class="text-muted mb-0">Belum ada riwayat notifikasi.</p>
                                    </li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- PAGINATION --}}
                        @if ($notifications->hasPages())
                            <div class="card-footer d-flex justify-content-center border-top">
                                {{ $notifications->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>


@endsection

@extends('layouts/contentNavbarLayout')
@section('title', 'Manajemen BKD Prodi - PRIMA')

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

    @php
        $doc = $approvalDoc;
        $totalDosen = $dosens->count();
        $totalSKSProdi = $dosens->sum('sks_prodi_ini');

        $verifiedCount = $dosens
            ->filter(function ($d) {
                $wl = $d->workloads->first();
                return $wl && $wl->is_verified;
            })
            ->count();

        $verifiedPercent = $totalDosen > 0 ? ($verifiedCount / $totalDosen) * 100 : 0;

        $maxSKS = $dosens->max('sks_prodi_ini');
        $minSKS = $dosens->min('sks_prodi_ini');
    @endphp

    <div class="row mb-4">
        {{-- Card Statistik --}}
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-1">Prodi {{ $prodi->name }} ({{ $prodi->jenjang }})</h5>
                    <small class="text-muted">Periode Akademik: {{ $activePeriod->name }}</small>

                    <div class="mt-3">
                        <div class="row text-center g-2">
                            {{-- Kiri: Total Beban --}}
                            <div class="col-6">
                                <div
                                    class="border rounded p-2 border-primary h-100 d-flex flex-column justify-content-center">
                                    <small class="d-block text-muted text-uppercase" style="font-size: 0.65rem">Total Beban
                                        Prodi</small>
                                    <strong class="fs-5 text-primary">{{ number_format($totalSKSProdi, 2) }} SKS</strong>
                                </div>
                            </div>

                            {{-- Kanan: Progress Validasi --}}
                            <div class="col-6">
                                <div class="border rounded p-2 border-success h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted text-uppercase" style="font-size: 0.65rem">Dosen
                                            Disesuaikan</small>
                                        <small class="fw-bold text-success">{{ round($verifiedPercent) }}%</small>
                                    </div>

                                    <strong class="fs-7 text-success">{{ $verifiedCount }} <span class="text-muted fs-7">/
                                            {{ $totalDosen }}</span></strong>

                                    <div class="progress mt-2 bg-label-secondary" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $verifiedPercent }}%" aria-valuenow="{{ $verifiedPercent }}"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 small text-muted text-center">
                            <i class='bx bx-stats'></i> Tertinggi: <strong>{{ number_format($maxSKS, 2) }}</strong> |
                            Terendah: <strong>{{ number_format($minSKS, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Action Submit --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h6 class="fw-bold mb-2">Status Dokumen Rekap</h6>

                    {{-- Panggil status_text dan status_color dari Model --}}
                    <span class="badge bg-label-{{ $doc ? $doc->status_color : 'secondary' }} mb-3 fs-6">
                        {{ $doc ? $doc->status_text : 'Draft (Belum Diajukan)' }}
                    </span>

                    @php
                        $currentStatus = $doc ? $doc->status : 'draft';
                    @endphp

                    @if ($currentStatus == 'draft' || $currentStatus == 'rejected')
                        {{-- Form Submit (Tetap sama) --}}
                        <form action="{{ route('prodi.bkd.submit') }}" method="POST"
                            onsubmit="return confirm('Ajukan dokumen rekapitulasi BKD ke Wadir?')">
                            @csrf
                            <input type="hidden" name="prodi_id" value="{{ $prodi->id }}">
                            <input type="hidden" name="academic_period_id" value="{{ $activePeriod->id }}">

                            <button type="submit" class="btn btn-primary w-100 shadow-sm"
                                {{ $totalSKSProdi <= 0 ? 'disabled' : '' }}>
                                <i class='bx bx-send me-1'></i>
                                {{ $currentStatus == 'rejected' ? 'Ajukan Revisi' : 'Ajukan Dokumen' }}
                            </button>
                        </form>

                        @if ($currentStatus == 'rejected' && $doc->feedback_message)
                            <div class="alert alert-danger mt-3 mb-0 text-start small">
                                <strong>Catatan Revisi:</strong><br>
                                {{ $doc->feedback_message }}
                            </div>
                        @endif
                    @else
                        <button class="btn btn-outline-secondary w-100" disabled>
                            <i class='bx bx-time-five me-1'></i> Sedang Diproses
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL LIST DOSEN --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Beban Kerja Dosen</h5>

            {{-- Search Box Sederhana (Opsional, bisa pakai DataTables nanti) --}}
            <div class="input-group input-group-sm w-auto">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="text" id="searchDosen" class="form-control" placeholder="Cari Nama Dosen...">
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover" id="tableDosen">
                <thead>
                    <tr>
                        <th width="4%" class="text-center">No</th>
                        <th>Nama Dosen & NIDN</th>
                        <th class="text-center">Total SKS</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Terakhir Update</th>
                        <th class="text-center" width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dosens as $index => $dosen)
                        @php
                            $wl = $dosen->workloads->first();
                            $totalSKS = $dosen->sks_prodi_ini ?? 0;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        @if ($dosen->profile_photo_path)
                                            <img src="{{ asset('storage/' . $dosen->profile_photo_path) }}"
                                                alt="Avatar" class="rounded-circle">
                                        @else
                                            <span
                                                class="avatar-initial rounded-circle bg-label-primary">{{ substr($dosen->name, 0, 2) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark d-block">{{ $dosen->name }}</span>
                                        <small class="text-muted">NIDN : {{ $dosen->nidn ?? ' -' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($wl)
                                    <span class="fw-bold fs-6 text-primary">{{ number_format($totalSKS, 2) }}</span>
                                    <br>
                                    <small class="text-muted" style="font-size: 0.65rem">
                                        (Total Global: {{ number_format($wl->total_sks_pendidikan, 2) }})
                                    </small>
                                @else
                                    <span class="badge bg-label-secondary">Belum Sync</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($wl)
                                    @if ($wl->is_verified)
                                        <span class="badge bg-success bg-glow">
                                            <i class='bx bx-check-double me-1'></i> Sudah Disesuaikan
                                        </span>
                                        <div class="small text-muted mt-1" style="font-size: 0.65rem;">
                                            {{ $wl->updated_at->format('d/m/y H:i') }}
                                        </div>
                                    @else
                                        <span class="badge bg-label-warning">
                                            <i class='bx bx-time-five me-1'></i> Belum Disesuaikan
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-label-secondary">Belum Sync</span>
                                @endif
                            </td>
                            <td class="text-center small text-muted">
                                {{ $wl ? $wl->updated_at->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('bkd-dosen.edit', $dosen->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-cog me-1"></i> Kelola
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="{{ asset('assets\img\illustrations\empty-box.png') }}" width="100"
                                    style="opacity: 0.5">
                                <p class="text-muted mt-2">Belum ada data dosen di prodi ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('page-script')
    <script>
        // Script Search Sederhana
        document.getElementById('searchDosen').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#tableDosen tbody tr');

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-ajukan');
                if (deleteBtn) {
                    e.preventDefault();
                    const form = deleteBtn.closest('form');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Pastikan Beban Kerja Setiap Dosen Sudah Disesuaikan!",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, ajukan!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            title: 'my-0 py-0',
                            htmlContainer: 'py-0 my-0',
                            confirmButton: 'btn btn-sm btn-primary me-3',
                            cancelButton: 'btn btn-sm btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
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
@endsection

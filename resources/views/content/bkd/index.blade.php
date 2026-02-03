@extends('layouts/contentNavbarLayout')
@section('title', 'Beban Kerja Dosen - PRIMA')
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

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Beban Kerja Dosen (BKD)</h5>
                    <p class="text-muted mb-0">Periode: {{ $activePeriod->name ?? '-' }}</p>
                </div>
                <div class="d-flex gap-2">
                    {{-- Tombol Generate --}}
                    <form id="formSync" action="{{ route('beban-kerja-dosen.generate') }}" method="POST">
                        @csrf
                        <button type="button" id="btnSync" class="btn btn-outline-primary">
                            <i class="bx bx-refresh me-1"></i> Sync
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total SKS (Real)</span>
                        <h3 class="mb-0 fw-bold text-primary">
                            {{ number_format($workload->total_sks_pendidikan ?? 0, 2) }}
                        </h3>
                        <small class="text-success fw-semibold">
                            <i class="bx bx-up-arrow-alt"></i> Pendidikan
                        </small>
                    </div>
                    <div class="avatar avatar-md bg-label-primary rounded p-2">
                        <i class="bx bx-book-open fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Kegiatan</span>
                        <h3 class="mb-0 fw-bold text-info">
                            {{ $activities->count() }}
                        </h3>
                        <small class="text-muted">Mata Kuliah</small>
                    </div>
                    <div class="avatar avatar-md bg-label-info rounded p-2">
                        <i class="bx bx-list-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Status Laporan</span>
                        @php
                            $status = ucfirst($workload->status ?? 'Draft');
                            $statusColor = match ($status) {
                                'Approved' => 'success',
                                'Submitted' => 'warning',
                                'Rejected' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <h4 class="mb-0 fw-bold text-{{ $statusColor }}">
                            {{ $status }}
                        </h4>
                        <small class="text-muted">Posisi saat ini</small>
                    </div>
                    <div class="avatar avatar-md bg-label-{{ $statusColor }} rounded p-2">
                        <i class="bx bx-file fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            @php
                $conclusion = $workload->conclusion ?? 'belum_dihitung';
                $isEligible = strtolower($conclusion) === 'memenuhi';
                $conclColor = $isEligible ? 'success' : 'danger';
                $conclIcon = $isEligible ? 'bx-check-shield' : 'bx-x-circle';
                $conclText = $isEligible ? 'Memenuhi' : 'Belum Memenuhi';
            @endphp
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Kesimpulan Akhir</span>
                        <h4 class="mb-0 fw-bold text-{{ $conclColor }}">
                            {{ $conclText }}
                        </h4>
                        <small class="text-muted">Syarat BKD</small>
                    </div>
                    <div class="avatar avatar-md bg-label-{{ $conclColor }} rounded p-2">
                        <i class="bx {{ $conclIcon }} fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Rincian Kegiatan Pendidikan</h5>
            <button type="button" id="btnSave" class="btn btn-primary">
                <i class="bx bx-save me-1"></i> Simpan Perubahan
            </button>
        </div>

        <div class="table-responsive text-nowrap">
            <form id="form-bkd" action="{{ route('beban-kerja-dosen.update-all') }}" method="POST">
                @csrf
                @method('PUT')

                <table class="table table-hover table-bordered">
                    <thead class="bg-label-secondary">
                        <tr>
                            <th width="2%" class="text-center">No</th>
                            <th width="45%">Mata Kuliah</th>
                            <th width="15%" class="text-center">Kelas</th>
                            <th width="10%" class="text-center">SKS</th>
                            <th width="10%" class="text-center">Pertemuan</th>
                            <th width="13%" class="text-center">Ujian</th>
                            <th width="10%" class="text-center">SKS Real</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $key => $act)
                            @php
                                $parts = explode(' - Kelas ', $act->activity_name);
                                $namaMatkul = $parts[0] ?? $act->activity_name;
                                $fullKelas = $parts[1] ?? '-';

                                $isMalam = \Illuminate\Support\Str::contains(strtolower($fullKelas), 'malam');
                                $shiftLabel = $isMalam ? 'MALAM' : 'PAGI';
                                $shiftColor = $isMalam ? 'bg-label-dark' : 'bg-label-warning';

                                $namaKelasClean = str_replace(['(Pagi)', '(Malam)'], '', $fullKelas);
                            @endphp

                            <tr>
                                <td class="text-center">{{ ++$key }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $namaMatkul }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-primary">{{ trim($namaKelasClean) }}</span>

                                    <span class="badge {{ $shiftColor }} fw-bold" style="font-size: 0.65rem;">
                                        {{ $shiftLabel }}
                                    </span>
                                </td>

                                <td class="text-center text-muted">
                                    {{ $act->sks_load }}
                                </td>

                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="activities[{{ $act->id }}][realisasi_pertemuan]"
                                            class="form-control text-center" value="{{ $act->realisasi_pertemuan }}"
                                            min="0" max="16">
                                    </div>
                                </td>

                                <td>
                                    <input type="text" name="activities[{{ $act->id }}][jenis_ujian]"
                                        class="form-control form-control-sm" value="{{ $act->jenis_ujian }}"
                                        placeholder="Cth: UTS, UAS">
                                </td>

                                <td class="text-center fw-bold text-primary">
                                    {{ number_format($act->sks_real ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Belum ada data kegiatan. Silakan klik tombol <b>"Tarik Data Jadwal"</b> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
    </div>

@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toast Notification (Sudah Benar)
            const successToast = document.getElementById('successToast');
            if (successToast) {
                new bootstrap.Toast(successToast, {
                    delay: 5000
                }).show();
            }
            const errorToast = document.getElementById('errorToast');
            if (errorToast) {
                new bootstrap.Toast(errorToast, {
                    delay: 5000
                }).show();
            }

            // SweetAlert untuk Tombol Sync
            const btnSync = document.getElementById('btnSync');
            if (btnSync) {
                btnSync.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Sinkronisasi Data?',
                        text: "Sistem akan mengambil data terbaru & Data Lama akan ditimpa.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Sinkronkan!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            title: 'my-0 py-0',
                            htmlContainer: 'py-0 my-0',
                            text: 'small text-muted py-0 my-0',
                            confirmButton: 'btn btn-sm btn-primary me-3',
                            cancelButton: 'btn btn-sm btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses...',
                                text: 'Mohon tunggu sebentar.',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            document.getElementById('formSync').submit();
                        }
                    });
                });
            }

            // SweetAlert untuk Tombol Simpan
            const btnSave = document.getElementById('btnSave');
            if (btnSave) {
                btnSave.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Simpan Perubahan?',
                        text: "Pastikan data realisasi pertemuan dan ujian sudah benar.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Simpan',
                        cancelButtonText: 'Batal',
                        customClass: {
                            title: 'my-0 py-0',
                            htmlContainer: 'py-0 my-0',
                            text: 'small text-muted py-0 my-0',
                            confirmButton: 'btn btn-sm btn-primary me-3',
                            cancelButton: 'btn btn-sm btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('form-bkd').submit();
                        }
                    });
                });
            }
        });
    </script>
@endsection

@extends('layouts/contentNavbarLayout')
@section('title', 'Kelola BKD Dosen - PRIMA')

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
        $docStatus = \App\Models\AprovalDocument::where([
            'academic_period_id' => $activePeriod->id,
            'prodi_id' => $targetDosen->prodi_id,
            'type' => 'beban_kerja_dosen',
        ])->value('status');

        $isLocked = $docStatus && !in_array($docStatus, ['draft', 'rejected']);
    @endphp

    {{-- HEADER --}}
    <div class="card mb-4">
        <div class="card-body py-4 my-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1">
                        <span class="text-muted fw-light">Manajemen BKD | </span> {{ $targetDosen->name }}
                    </h6>
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="bx bx-id-card"></i> {{ $targetDosen->nidn ?? 'NIDN -' }}
                        <span class="text-muted">|</span>
                        <i class="bx bx-calendar"></i> Periode {{ $activePeriod->name }}
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('bkd-dosen.list') }}" class="btn btn-label-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>

                    @if (!$isLocked)
                        <form action="{{ route('bkd-dosen.generate') }}" method="POST"
                            onsubmit="return confirm('Data BKD akan di-reset sesuai Jadwal Distribusi terbaru. Data manual mungkin hilang. Lanjutkan?');">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $targetDosen->id }}">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bx bx-refresh me-1"></i> Sinkronisasi Ulang
                            </button>
                        </form>
                    @else
                        <button class="btn btn-secondary" disabled>
                            <i class="bx bx-lock-alt me-1"></i> Mode Baca Saja
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($isLocked)
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bx bx-lock-alt me-2 fs-4"></i>
            <div>
                Data ini sedang <strong>Terkunci</strong> karena dokumen rekapitulasi sedang dalam proses verifikasi
                (Status: {{ strtoupper(str_replace('_', ' ', $docStatus)) }}).
                Anda tidak dapat mengubah data sampai proses selesai atau dikembalikan (Revisi).
            </div>
        </div>
    @endif

    {{-- FORM UTAMA --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom p-3">
            <h6 class="mb-0 fw-bold"><i class="bx bx-list-check me-2"></i>Rincian Kegiatan Pendidikan</h6>

            @if (!$isLocked)
                <button type="button" onclick="document.getElementById('formUpdateBkd').submit()" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i> Simpan
                </button>
            @endif
        </div>

        <div class="table-responsive text-nowrap">
            <form id="formUpdateBkd" action="{{ route('bkd-dosen.update-all') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Kirim User ID untuk redirect back yang benar (opsional, tergantung controller) --}}
                <input type="hidden" name="target_user_id" value="{{ $targetDosen->id }}">

                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-middle py-2" width="4%">No</th>
                            <th rowspan="2" class=" align-middle py-2">Mata Kuliah</th>
                            <th rowspan="2" width="8%" class="text-center align-middle py-2">Kelas</th>
                            <th colspan="2" class="text-center border-bottom py-2">Beban SKS</th>
                            <th colspan="3" class="text-center border-bottom py-2">Realisasi & Penugasan</th>
                            <th rowspan="2" class="text-center align-middle text-dark py-2" width="8%">Total Diakui
                            </th>
                        </tr>
                        <tr>
                            <th class="text-center text-muted py-2" width="7%" title="SKS Asli Matkul (Kurikulum)">
                                <small>Matkul</small>
                            </th>
                            <th class="text-center bg-white py-2" width="10%" title="SKS yang dibebankan ke Dosen ini">
                                <small>Bagian
                                    <i class="bx bx-alert" data-bs-toggle="popover" data-bs-html="true"
                                        data-bs-trigger="hover focus" data-bs-placement="top"
                                        title="<div class='text-center fw-bold'>Maksud Bagian?</div>"
                                        data-bs-content="<strong>Catatan:</strong> Kolom 'Bagian' adalah SKS yang diakui untuk dosen tersebut.
                Untuk Team Teaching, silakan bagi manual SKS-nya di kolom ini.
                Perhitungan Akhir = <code>(Pertemuan / 16) x SKS Bagian</code>.">
                                    </i>
                                </small>
                            </th>

                            <th class="text-center py-2" width="8%"><small>Pertemuan</small></th>
                            <th class="text-center py-2" width="5%"><small>Soal UTS</small></th>
                            <th class="text-center py-2" width="5%"><small>Soal UAS</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $index => $act)
                            @php
                                // Styling Nama
                                $parts = explode(' - Kelas ', $act->activity_name);
                                $namaMatkul = $parts[0] ?? $act->activity_name;
                                $kelasFull = $parts[1] ?? '';
                                $isMalam = \Illuminate\Support\Str::contains(strtolower($kelasFull), 'malam');
                                $badgeShift = $isMalam ? 'bg-label-dark' : 'bg-label-primary';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <span class="fw-semibold text-dark d-block text-truncate" style="max-width: 300px;">
                                        {{ $namaMatkul }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($kelasFull)
                                        <span class="badge {{ $badgeShift }}">
                                            Kelas {{ $kelasFull }}
                                        </span>
                                    @endif
                                </td>

                                {{-- 2. SKS Matkul (Info Only) --}}
                                <td class="text-center text-muted">
                                    {{ $act->sks_load }}
                                </td>

                                {{-- 3. SKS Bagian (CRITICAL INPUT) --}}
                                <td class="p-1">
                                    <input type="number" step="0.01"
                                        name="activities[{{ $act->id }}][sks_assigned]"
                                        class="form-control form-control-sm text-center fw-bold text-primary border-0"
                                        style="background-color: #f0f4ff;" value="{{ $act->sks_assigned }}"
                                        {{ $isLocked ? 'readonly' : '' }}>
                                </td>

                                {{-- 4. Pertemuan --}}
                                <td class="p-1">
                                    <input type="number" name="activities[{{ $act->id }}][realisasi_pertemuan]"
                                        class="form-control form-control-sm text-center"
                                        value="{{ $act->realisasi_pertemuan }}" min="0" max="16"
                                        {{ $isLocked ? 'readonly' : '' }}>
                                </td>

                                {{-- 6. Checkbox Tugas --}}
                                <td class="text-center align-middle">
                                    <input class="form-check-input" type="checkbox"
                                        name="activities[{{ $act->id }}][is_uts_maker]" value="1"
                                        {{ $act->is_uts_maker ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                </td>
                                <td class="text-center align-middle">
                                    <input class="form-check-input" type="checkbox"
                                        name="activities[{{ $act->id }}][is_uas_maker]" value="1"
                                        {{ $act->is_uas_maker ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                </td>

                                {{-- 7. Hasil Akhir --}}
                                <td class="text-center align-middle fw-bold fs-6 text-success bg-label-success-subtle">
                                    {{ number_format($act->sks_real, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class='bx bx-data fs-1 mb-2'></i><br>
                                    Belum ada data kegiatan.<br>
                                    @if (!$isLocked)
                                        Silakan klik tombol <strong>Sinkronisasi Ulang</strong> di atas.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8" class="text-end fw-bold text-uppercase">Total Beban Kerja Pendidikan :</td>
                            <td colspan="2" class="text-center fw-bold fs-5 text-primary">
                                {{ number_format($workload->total_sks_pendidikan, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>
        </div>
    </div>

@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    container: 'body',
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
@endsection

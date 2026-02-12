@extends('layouts/contentNavbarLayout')
@section('title', 'BKD Saya - PRIMA')

@section('content')

    {{-- HEADER PERIODE --}}

    @if ($workload)
        <div class="row mb-4 g-3">
            {{-- Card 1: Periode & Status --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-medium d-block mb-1 text-muted">Periode Akademik</span>
                            <h5 class="card-title mb-2 text-primary fw-bold">{{ $activePeriod->name ?? 'Tidak Aktif' }}</h5>
                            <small class="text-muted d-flex align-items-center cursor-pointer" data-bs-toggle="popover"
                                data-bs-html="true" data-bs-trigger="hover focus" data-bs-placement="top"
                                title="<div class='text-center bg-label-primary fw-bold'>Informasi</div>"
                                data-bs-content="Ini adalah halaman <strong>Read-Only</strong>.<br>Penentuan beban kerja dikelola oleh <strong>Kaprodi</strong>.">
                                <i class="bx bx-info-circle text-warning me-1"></i>
                                <span>Mode Baca Saja</span>
                            </small>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-calendar"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Total SKS --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-medium d-block mb-1 text-muted">Total SKS Diakui</span>
                            <h3 class="card-title mb-2 fw-bold">{{ number_format($workload->total_sks_pendidikan, 2) }}</h3>
                            <small class="text-success fw-semibold">
                                <i class='bx bx-check-shield'></i> Beban Pendidikan
                            </small>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-chart"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Jumlah Kelas --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-medium d-block mb-1 text-muted">Jumlah Mata Kuliah</span>
                            <h3 class="card-title mb-2 fw-bold">
                                {{ $workload->activities->where('category', 'pendidikan')->count() }}</h3>
                            <small class="text-info fw-semibold">
                                <i class='bx bx-layer'></i> Kelas Diampu
                            </small>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-book-open"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL RINCIAN KEGIATAN --}}
        <div class="card">
            <div class="card-header py-4">
                <h6 class="mb-0 fw-bold"><i class='bx bx-list-ul me-2'></i>Rincian Kegiatan Pendidikan</h6>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Mata Kuliah</th>
                            <th width="10%">Kelas</th>
                            <th width="8%" class="text-center">SKS Matkul</th>
                            <th width="8%" class="text-center">Pertemuan</th>
                            <th width="8%" class="text-center">Ujian</th>
                            <th width="8%" class="text-center">SKS Real</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workload->activities->where('category', 'pendidikan') as $index => $act)
                            @php
                                // Parsing Nama Kelas untuk tampilan rapi
                                $parts = explode(' - Kelas ', $act->activity_name);
                                $namaMatkul = $parts[0] ?? $act->activity_name;
                                $kelasFull = $parts[1] ?? '';
                                $isMalam = \Illuminate\Support\Str::contains(strtolower($kelasFull), 'malam');
                                $badgeShift = $isMalam ? 'bg-label-dark' : 'bg-label-primary';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-bold text-dark d-block">{{ $namaMatkul }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($kelasFull)
                                        <span class="badge {{ $badgeShift }}">
                                            Kelas {{ $kelasFull }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center text-muted">
                                    {{ $act->sks_load }}
                                </td>

                                <td class="text-center">
                                    <span class="fw-bold">{{ $act->realisasi_pertemuan }}</span> / 16
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-3">
                                        <div class="d-flex flex-column align-items-center" data-bs-toggle="tooltip"
                                            title="Pembuat Soal UTS">
                                            <span class="small text-muted mb-1" style="font-size: 0.65rem">UTS</span>
                                            @if ($act->is_uts_maker)
                                                <i class='bx bxs-check-circle text-success fs-5'></i>
                                            @else
                                                <i class='bx bx-x text-secondary fs-5' style="opacity: 0.3"></i>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-column align-items-center" data-bs-toggle="tooltip"
                                            title="Pembuat Soal UAS">
                                            <span class="small text-muted mb-1" style="font-size: 0.65rem">UAS</span>
                                            @if ($act->is_uas_maker)
                                                <i class='bx bxs-check-circle text-success fs-5'></i>
                                            @else
                                                <i class='bx bx-x text-secondary fs-5' style="opacity: 0.3"></i>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-success fs-6">
                                    {{ number_format($act->sks_real, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png"
                                        alt="Empty" width="150" style="opacity: 0.5">
                                    <p class="text-muted mt-3">Belum ada data kegiatan pendidikan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">TOTAL SKS PENDIDIKAN :</td>
                            <td class="text-center border fw-bold text-primary fs-5">
                                {{ number_format($workload->total_sks_pendidikan, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        {{-- TAMPILAN JIKA WORKLOAD BELUM DI-GENERATE KAPRODI --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class='bx bx-data text-secondary' style="font-size: 5rem; opacity: 0.3"></i>
                </div>
                <h4>Data Belum Tersedia</h4>
                <p class="text-muted">
                    Kaprodi belum melakukan sinkronisasi/generate BKD untuk periode
                    <strong>{{ $activePeriod->name }}</strong>.<br>
                    Silakan tunggu hingga Kaprodi menyelesaikan pengaturan beban kerja.
                </p>
            </div>
        </div>
    @endif

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
        });
    </script>
@endsection

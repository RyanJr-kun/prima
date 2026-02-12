@extends('layouts/contentNavbarLayout')
@section('title', 'Rekapitulasi BKD - Admin')

@section('content')

    {{-- HEADER & FILTER --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <h5 class="fw-bold mb-0">Monitoring BKD Dosen</h5>
                    <p class="text-muted mb-0">Periode: {{ $activePeriod->name ?? 'Tidak Aktif' }}</p>
                </div>
                <div class="col-md-4">
                    @if (!$isKaprodi)
                        <form action="{{ route('beban-kerja-dosen.show-doc') }}" method="GET"
                            class="d-flex gap-2 justify-content-md-end">
                            <select name="prodi_id" class="form-select select2" onchange="this.form.submit()">
                                <option value="">-- Pilih Prodi --</option>
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ $prodiId == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->jenjang }} {{ $prodi->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($prodiId)

        {{-- STATISTIK QUICK --}}
        @php
            $totalDosen = $dosens->count();
            $sudahIsi = $dosens
                ->filter(fn($d) => $d->workloads->isNotEmpty() && $d->workloads->first()->total_sks_pendidikan > 0)
                ->count();
            $memenuhi = $dosens
                ->filter(fn($d) => $d->workloads->isNotEmpty() && $d->workloads->first()->conclusion == 'memenuhi')
                ->count();
            $persen = $totalDosen > 0 ? ($sudahIsi / $totalDosen) * 100 : 0;
        @endphp

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Progres Pengisian Prodi</span>
                            <span>{{ $sudahIsi }} / {{ $totalDosen }} Dosen</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persen }}%">
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">
                            <i class="bx bx-check-circle text-success"></i> {{ $memenuhi }} Dosen Memenuhi Syarat (12
                            SKS)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL MONITORING --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Dosen</h5>

                {{-- TOMBOL AJUKAN DOKUMEN --}}
                @if (!$approvalDoc)
                    <form action="{{ route('beban-kerja-dosen.submit') }}" method="POST"
                        onsubmit="return confirm('Buat dokumen rekapitulasi untuk prodi ini?')">
                        @csrf
                        <input type="hidden" name="academic_period_id" value="{{ $activePeriod->id }}">
                        <input type="hidden" name="prodi_id" value="{{ $prodiId }}">

                        <button type="submit" class="btn btn-primary" {{ $memenuhi < 1 ? 'disabled' : '' }}>
                            <i class="bx bx-file me-1"></i> Buat Dokumen Approval
                        </button>
                    </form>

                    {{-- KONDISI 2: Dokumen ADA tapi DITOLAK (Revisi) --}}
                @elseif ($approvalDoc->status == 'rejected')
                    <form action="{{ route('beban-kerja-dosen.submit') }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin data sudah diperbaiki dan ingin mengajukan ulang?')">
                        @csrf
                        <input type="hidden" name="academic_period_id" value="{{ $activePeriod->id }}">
                        <input type="hidden" name="prodi_id" value="{{ $prodiId }}">

                        {{-- Tombol Warning untuk Revisi --}}
                        <button type="submit" class="btn btn-warning">
                            <i class="bx bx-redo me-1"></i> Ajukan Ulang (Revisi)
                        </button>
                    </form>

                    {{-- Opsional: Tampilkan alasan penolakan kecil di samping tombol --}}
                    <span class="text-danger small align-self-center ms-2" title="Alasan Penolakan">
                        <i class="bx bx-info-circle"></i> Perlu Revisi
                    </span>

                    {{-- KONDISI 3: Dokumen Sedang Proses atau Sudah Disetujui --}}
                @else
                    @php
                        $btnClass = $approvalDoc->status == 'approved_direktur' ? 'btn-success' : 'btn-outline-primary';
                        $btnIcon = $approvalDoc->status == 'approved_direktur' ? 'bx-check-double' : 'bx-time-five';
                        $btnText =
                            $approvalDoc->status == 'approved_direktur' ? 'Selesai & Disahkan' : 'Sedang Diproses';
                    @endphp

                    <button class="btn {{ $btnClass }}" disabled>
                        <i class="bx {{ $btnIcon }} me-1"></i> {{ $btnText }}
                    </button>
                @endif
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Dosen</th>
                            <th class="text-center">Total SKS</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Kesimpulan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dosens as $dosen)
                            @php
                                $wl = $dosen->workloads->first();
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $dosen->name }}</strong><br>
                                    <small class="text-muted">{{ $dosen->nipy ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    @if ($wl)
                                        <span
                                            class="badge bg-label-primary">{{ number_format($wl->total_sks_pendidikan, 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($wl)
                                        <span class="badge bg-label-info">Draft</span>
                                    @else
                                        <span class="badge bg-label-secondary">Belum Isi</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($wl)
                                        @if ($wl->conclusion == 'memenuhi')
                                            <i class="bx bxs-check-circle text-success fs-4" title="Memenuhi"></i>
                                        @else
                                            <i class="bx bxs-x-circle text-danger fs-4" title="Belum Memenuhi"></i>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{-- Tombol Intip / Detail (Bisa diarahkan ke view detail individu) --}}
                                    {{-- <a href="#" class="btn btn-sm btn-icon btn-outline-secondary">
                                    <i class="bx bx-show"></i>
                                </a> --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- JIKA SUDAH ADA DOKUMEN, TAMPILKAN CARD PARTIAL YANG KAMU BUAT TADI --}}
        @if ($approvalDoc)
            <div class="mt-4">
                <h5 class="mb-3">Status Dokumen Approval</h5>
                {{-- Include Partial Card BKD --}}
                @include('content.dokumen.partials.card-bkd', ['doc' => $approvalDoc])
            </div>
        @endif
    @else
        <div class="alert alert-info mt-3">Silakan pilih Program Studi terlebih dahulu.</div>
    @endif

@endsection

@section('page-script')
    <script type="module">
        // Fungsi inisialisasi Select2
        const initSelect2 = () => {
            // Cek apakah jQuery dan Select2 sudah siap
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: $this.find('option[value=""]').length >
                            0, // Hanya allowClear jika ada value=""
                        width: '100%',
                        minimumResultsForSearch: 10 // Sembunyikan search box jika opsi < 10
                    });
                });
            } else {
                // Jika belum siap, coba lagi dalam 100ms
                setTimeout(initSelect2, 100);
            }
        };

        // Jalankan saat script dimuat
        initSelect2();
    </script>
@endsection

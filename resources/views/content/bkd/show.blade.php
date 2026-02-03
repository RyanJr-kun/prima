@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Dokumen BKD')

@section('content')

    {{-- HEADER ACTIONS --}}
    <div class="card mb-4 py-0">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1 text-primary">
                        <i class="bx bx-file-find me-2"></i>Review Dokumen BKD
                    </h5>
                    <span class="text-muted">
                        {{ $doc->prodi->jenjang }} {{ $doc->prodi->name }} | Periode {{ $doc->academicPeriod->name }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('beban-kerja-dosen.rekap') }}" class="btn btn-label-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                    {{-- <a href="{{ route('beban-kerja-dosen.print-doc', $doc->id) }}" target="_blank" class="btn btn-danger">
                        <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                    </a> --}}
                </div>
            </div>
        </div>
    </div>

    {{-- STATUS CARD --}}
    <div class="card mb-4 border border-{{ $doc->status_color }}">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-2 text-center border-end">
                    <span class="d-block text-muted small text-uppercase">Status Dokumen</span>
                    <h5 class="mb-0 mt-1 badge bg-label-{{ $doc->status_color }} fs-6">
                        {{ $doc->status_text }}
                    </h5>
                </div>
                <div class="col-md-3 text-center border-end">
                    <span class="d-block text-muted small text-uppercase">Total Dosen</span>
                    <h4 class="mb-0 mt-1 fw-bold">{{ count($reportData) }}</h4>
                </div>
                <div class="col-md-3 text-center border-end">
                    <span class="d-block text-muted small text-uppercase">Total SKS (Real)</span>
                    <h4 class="mb-0 mt-1 fw-bold text-primary">
                        {{ collect($reportData)->sum('total_sks_real') }}
                    </h4>
                </div>
                <div class="col-md-4 px-4">
                    <span class="d-block text-muted small mb-2">Posisi Approval:</span>
                    <div class="progress" style="height: 8px;">
                        @php
                            $progress = match ($doc->status) {
                                'draft' => 10,
                                'submitted' => 30, // Di Kaprodi
                                'approved_kaprodi' => 50, // Di Wadir 1
                                'approved_wadir1' => 75, // Di Wadir 2
                                'approved_wadir2' => 90, // Di Direktur
                                'approved_direktur' => 100,
                                default => 0,
                            };
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"></div>
                    </div>
                    <small class="text-success mt-1 d-block">
                        <i class="bx bx-check-circle"></i> {{ str_replace('_', ' ', ucfirst($doc->status)) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- PREVIEW TABEL (Kertas Putih) --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between">
            <h6 class="mb-0">Preview Isi Laporan</h6>
            <small class="text-muted">Tampilan ini disesuaikan dengan format cetak</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0" style="font-size: 0.85rem;">
                    <thead class="table-dark text-center align-middle">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Dosen</th>
                            <th width="20%">Mata Kuliah</th>
                            <th width="5%">Kelas</th>
                            <th width="10%">Semester/Kelas</th>
                            <th width="5%">SKS</th>
                            <th width="5%">Jml<br>Kelas</th>
                            <th width="5%">Jml<br>SKS</th>
                            <th width="5%">Pertemuan</th>
                            <th width="10%">Ujian</th>
                            <th width="5%">SKS<br>Real</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportData as $idx => $dosen)
                            @php
                                $rowspan = count($dosen['matkuls']) + 1; // +1 utk baris Total
                            @endphp

                            {{-- BARIS PERTAMA (Data Dosen + Matkul Pertama) --}}
                            @if (count($dosen['matkuls']) > 0)
                                @foreach ($dosen['matkuls'] as $key => $mk)
                                    <tr>
                                        {{-- Kolom Dosen (Hanya muncul sekali) --}}
                                        @if ($key === 0)
                                            <td rowspan="{{ $rowspan }}"
                                                class="text-center align-middle bg-white fw-bold">
                                                {{ $idx + 1 }}
                                            </td>
                                            <td rowspan="{{ $rowspan }}" class="align-middle bg-white">
                                                <strong>{{ $dosen['user']->name }}</strong>
                                            </td>
                                        @endif

                                        {{-- Data Matkul --}}
                                        <td>{{ $mk['nama_matkul'] }}</td>
                                        <td class="text-center">{{ $mk['kelas_type'] }}</td>
                                        <td>{{ $mk['daftar_kelas'] }}</td>
                                        <td class="text-center">{{ $mk['sks_per_mk'] }}</td>
                                        <td class="text-center">{{ $mk['jml_kelas'] }}</td>
                                        <td class="text-center">{{ $mk['jml_sks_total'] }}</td>
                                        <td class="text-center">{{ $mk['pertemuan'] }}</td>
                                        <td class="text-center small">{{ $mk['ujian'] }}</td>
                                        <td class="text-center fw-bold">{{ $mk['sks_real'] }}</td>
                                    </tr>
                                @endforeach

                                {{-- BARIS TOTAL PER DOSEN --}}
                                <tr class="bg-label-secondary fw-bold">
                                    <td colspan="6" class="text-end">Total BKD</td>
                                    <td class="text-center">{{ $dosen['total_sks_bkd'] }}</td>
                                    <td colspan="1"></td>
                                    <td class="text-center text-primary">{{ $dosen['total_sks_real'] }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $dosen['user']->name }}</td>
                                    <td colspan="9" class="text-center text-muted">Tidak ada data mengajar</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

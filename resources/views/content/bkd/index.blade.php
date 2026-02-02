@extends('layouts/contentNavbarLayout')
@section('title', 'Beban Kerja Dosen - PRIMA')
@section('content')

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold">Beban Kerja Dosen</span>
                    <p class="text-muted mb-0">Isi data realisasi pertemuan dan jenis ujian untuk setiap kegiatan mengajar.
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <form action="{{ route('beban-kerja-dosen.generate') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Generate BKD</button>
                    </form>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

            </div>
        </div>
    </div>
    <div class="row my-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <span class="d-block mb-1">Total SKS Pendidikan</span>
                    <h3 class="card-title text-white mb-0">
                        {{ $workload->total_sks_pendidikan ?? 0 }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <span class="d-block mb-1">Total Kegiatan/Kelas</span>
                    <h3 class="card-title text-white mb-0">
                        {{ $activities->count() }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <span class="d-block mb-1 text-muted">Status Laporan</span>
                    <h4 class="card-title mb-0 text-warning">
                        {{ ucfirst($workload->status ?? 'Draft') }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <span class="d-block mb-1 text-muted">Kesimpulan</span>
                    <h4
                        class="card-title mb-0 {{ ($workload->conclusion ?? '') == 'Memenuhi' ? 'text-success' : 'text-danger' }}">
                        {{ ucfirst($workload->conclusion ?? 'Belum Dihitung') }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERT & NOTIFIKASI --}}
    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div class="table-responsive text-nowrap">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kegiatan (Matkul - Kelas)</th>
                    <th>SKS</th>
                    <th width="15%">Pertemuan (Realisasi)</th> {{-- Input Manual --}}
                    <th width="20%">Jenis Ujian</th> {{-- Input Manual --}}
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($activities as $act)
                    <form action="{{ route('beban-kerja-dosen.update-all', $act->id) }}" method="POST">
                        @csrf @method('PUT')
                        <tr>
                            <td>{{ $act->activity_name }}</td>
                            <td>{{ $act->sks_load }}</td>

                            <td>
                                {{-- Dosen bisa ganti angka 14 jadi 7 atau 10 disini --}}
                                <input type="number" name="realisasi_pertemuan" class="form-control form-control-sm"
                                    value="{{ $act->realisasi_pertemuan }}">
                            </td>

                            <td>
                                {{-- Dosen bisa ganti jadi 'UAS' saja --}}
                                <input type="text" name="jenis_ujian" class="form-control form-control-sm"
                                    value="{{ $act->jenis_ujian }}">
                            </td>

                            <td>
                                <button type="submit" class="btn btn-xs btn-primary">Simpan</button>
                            </td>
                        </tr>
                    </form>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>
    </div>
    </div>
    </div>

@endsection

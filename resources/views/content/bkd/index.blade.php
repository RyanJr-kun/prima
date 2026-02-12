@extends('layouts/contentNavbarLayout')
@section('title', 'Manajemen Beban Kerja Dosen')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Daftar Beban Kerja Dosen</h5>
                <small class="text-muted">Periode: {{ $activePeriod->name ?? '-' }}</small>
            </div>

            {{-- Tombol Sync Massal --}}
            @if ($prodiId)
                <form action="{{ route('beban-kerja-dosen.sync') }}" method="POST"
                    onsubmit="return confirm('Tarik data terbaru dari Distribusi Matkul?')">
                    @csrf
                    <input type="hidden" name="prodi_id" value="{{ $prodiId }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-refresh me-1"></i> Sinkronisasi Data Matkul
                    </button>
                </form>
            @endif
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama Dosen</th>
                        <th class="text-center">Total SKS</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dosens as $dosen)
                        @php
                            $wl = $dosen->workloads->first();
                            $totalSks = $wl ? $wl->total_sks_pendidikan : 0;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $dosen->name }}</strong><br>
                                <small class="text-muted">{{ $dosen->nidn ?? '-' }}</small>
                            </td>
                            <td class="text-center">
                                @if ($totalSks >= 12)
                                    <span class="badge bg-label-success">{{ $totalSks }} SKS</span>
                                @elseif($totalSks > 0)
                                    <span class="badge bg-label-warning">{{ $totalSks }} SKS</span>
                                @else
                                    <span class="badge bg-label-secondary">Kosong</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($wl && $wl->conclusion == 'memenuhi')
                                    <i class='bx bxs-check-circle text-success fs-4' title="Memenuhi"></i>
                                @else
                                    <i class='bx bxs-x-circle text-danger fs-4' title="Belum Memenuhi"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('beban-kerja-dosen.edit', $dosen->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-edit me-1"></i> Kelola Tugas
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data dosen.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

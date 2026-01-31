@extends('layouts/contentNavbarLayout')

@section('title', 'Detail Kalender Akademik')

@section('content')
    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center ">
                <div>
                    <h5 class="fw-bold mb-1">Kalender Akademik : <span
                            class="fw-bold ">{{ $doc->academicPeriod->name }}</span></h5>
                    <div class="text-muted">
                        Status: <span class="badge small bg-{{ $doc->status_color }}">{{ $doc->status_text }}</span>
                    </div>
                </div>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between">
            <span class="fw-bold">Rincian Kegiatan</span>
            <small class="text-muted">Diajukan oleh: {{ $doc->lastActionUser->name ?? '-' }}</small>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="30%">Kegiatan</th>
                        <th width="20%">Tanggal</th>
                        <th width="50%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $index => $event)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $event->name }}</td>
                            <td>
                                <span>{{ $event->start_date->format('d M Y') }} s/d
                                    {{ $event->end_date->format('d M Y') }}</span>
                            </td>
                            <td>{!! nl2br(e($event->description ?? '-')) !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

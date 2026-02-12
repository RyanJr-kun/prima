@extends('layouts/contentNavbarLayout')

@section('title', 'Detail Distribusi Mata Kuliah')

@section('content')
    <div class="card mb-4 p-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Distribusi Matkul: {{ $doc->prodi->jenjang }} {{ $doc->prodi->name }}</h5>
                    <div class="text-muted">{{ $doc->academicPeriod->name }} |
                        <span class="badge bg-label-{{ $doc->status_color }}">{{ $doc->status_text }}</span>
                    </div>
                </div>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @forelse($distributions as $classId => $items)
        @php
            $className = $items->first()->studyClass->name ?? 'Kelas Tidak Dikenal';
            $kelas = $items->first()->studyClass;
        @endphp

        <div class="card mb-4">
            <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between">
                <div>
                    <h5 class="mb-1 fw-bold text-primary display-6" style="font-size: 1.2rem;">
                        <i class='bx bx-chalkboard me-2'></i>{{ $kelas->full_name ?? $kelas->name }}
                    </h5>
                    <div class="text-muted d-flex gap-3 small flex-wrap">
                        <span><i class='bx bx-building'></i> {{ $kelas->prodi->name }}</span>
                        <span><i class='bx bx-calendar'></i> Semester {{ $kelas->semester }}
                            ({{ ucfirst($kelas->shift) }})
                        </span>
                        <span><i class='bx bx-group'></i> {{ $kelas->total_students }} Mhs</span>
                        <span class="text-info"><i class='bx bx-user-voice'></i> PA:
                            {{ $kelas->academicAdvisor->name ?? 'Belum diset' }}</span>
                    </div>
                </div>
                <div>
                    <span class="badge bg-label-primary fs-6">{{ $items->count() }} Mata Kuliah</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center fw-bold" width="4%">No</th>
                            <th rowspan="2" class="fw-bold">Kode</th>
                            <th rowspan="2" class="fw-bold">Mata Kuliah</th>
                            <th colspan="4" class="text-center border py-0 fw-bold">SKS</th>
                            <th rowspan="2" class="fw-bold">Dosen Pengampu</th>
                            <th rowspan="2" class="fw-bold">Dosen Team / PDDIKTI</th>
                            <th rowspan="2" class="fw-bold">Referensi</th>
                            <th rowspan="2" class="fw-bold">Luaran</th>
                        </tr>
                        <tr>
                            <th class="text-center border py-0" width="3%"><small>T</small></th>
                            <th class="text-center border py-0" width="3%"><small>P</small></th>
                            <th class="text-center border py-0" width="3%"><small>L</small></th>
                            <th class="text-center border py-0" width="3%"><small>JML</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $dist)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $dist->course->code }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($dist->course->name ?? '-', 40) }}</td>
                                <td class="text-center border">{{ $dist->course->sks_teori ?? 0 }}</td>
                                <td class="text-center border">{{ $dist->course->sks_praktik ?? 0 }}</td>
                                <td class="text-center border">{{ $dist->course->sks_lapangan ?? 0 }}</td>
                                <td class="text-center border">{{ $dist->course->sks_total ?? 0 }}</td>

                                <td>
                                    @if ($dist->user)
                                        <small>{{ $dist->user->name }}</small>
                                    @endif
                                    @if ($dist->teachingLecturers->count() > 0)
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach ($dist->teachingLecturers as $dosen)
                                                @if ($dist->user_id !== $dosen->id)
                                                    <li>• {{ $dosen->name }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-danger small ">- Belum ada Pengajar -</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($dist->pddiktiLecturers->count() > 0)
                                        <ul class="list-unstyled mb-0 small text-secondary">
                                            @foreach ($dist->pddiktiLecturers as $dosen)
                                                <li>• {{ $dosen->name }} <br></li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="badge bg-label-warning">Belum Lapor</span>
                                    @endif
                                </td>
                                <td>{{ $dist->referensi }}</td>
                                <td>{{ $dist->luaran }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-warning">Belum ada data distribusi mata kuliah.</div>
    @endforelse

@endsection

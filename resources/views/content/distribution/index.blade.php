@extends('layouts/contentNavbarLayout')
@section('title', 'Distribusi Mata Kuliah - PRIMA')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <h4 class="fw-bold">Distribusi Perkuliahan ({{ $activePeriod->name }})</h4>
    <a href="{{ route('distributions.create') }}" class="btn btn-primary">
        + Tambah Distribusi
    </a>
</div>

{{-- LOOPING GROUPING PER KELAS --}}
@forelse($distributions as $classId => $items)
    @php 
        $kelasInfo = $items->first()->studyClass; 
    @endphp

    <div class="card mb-4">
        <div class="card-header bg-label-primary">
            <h5 class="mb-0 text-primary">
                <i class='bx bx-building'></i> {{ $kelasInfo->full_name }} 
                <span class="badge bg-white text-primary ms-2">{{ $items->count() }} Matkul</span>
            </h5>
            <small>PA: {{ $kelasInfo->academicAdvisor->name ?? '-' }} | Mhs: {{ $kelasInfo->total_students }}</small>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Kode</th>
                        <th rowspan="2">Mata Kuliah</th>

                        <th colspan="4" class="border py-1 text-center">SKS</th>
                        <th rowspan="2">Dosen Pengampu</th>
                        <th rowspan="2">Dosen PDDIKTI</th>
                        <th rowspan="2">Aksi</th>
                    </tr>
                    <tr >
                        <th class="text-center py-1 border">T</th>
                        <th class="text-center py-1 border">P</th>
                        <th class="text-center py-1 border">PL</th>
                        <th class="text-center py-1 border">JML</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $key => $dist)
                    <tr>
                        <td>{{ ++$key }}</td>
                        {{-- <td>{{ $dist->course->semester }}</td> --}}
                        <td>{{ $dist->course->code }}</td>
                        <td class="fw-bold">{{ $dist->course->name }}</td>
                        <td class="text-center border">{{ $dist->course->sks_teori }}</td>
                        <td class="text-center border">{{ $dist->course->sks_praktik }}</td>
                        <td class="text-center border">{{ $dist->course->sks_lapangan }}</td>
                        <td class="text-center border">{{ $dist->course->sksTotal }}</td>
                        <td>{{ $dist->user->name }}</td>  
                        <td>{{ $dist->teamTeaching->name ?? '-' }}</td>
                        <td>
                            <form action="{{ route('distributions.destroy', $dist->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-label-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="alert alert-warning">Belum ada data distribusi mata kuliah. Silakan input data.</div>
@endforelse

@endsection
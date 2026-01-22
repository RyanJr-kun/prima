@extends('layouts/contentNavbarLayout')
@section('title', 'Distribusi Mata Kuliah - PRIMA')

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
                        <tr>
                            <th class="text-center py-1 border">T</th>
                            <th class="text-center py-1 border">P</th>
                            <th class="text-center py-1 border">PL</th>
                            <th class="text-center py-1 border">JML</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $key => $dist)
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

@section('page-script')
    <script>
        document.body.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-record');
            if (deleteBtn) {
                e.preventDefault();
                const form = deleteBtn.closest('form');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        title: 'my-0 py-0',
                        htmlContainer: 'py-0 my-0',
                        confirmButton: 'btn btn-sm btn-primary me-3',
                        cancelButton: 'btn btn-sm btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        // Toast Notification Logic
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
    </script>
@endsection

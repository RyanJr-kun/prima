@extends('layouts/contentNavbarLayout')
@section('title', 'Distribusi Mata Kuliah - PRIMA')

@section('content')

    @php
        $isLocked = false;
        if (isset($documentStatus) && !in_array($documentStatus, ['draft', 'rejected'])) {
            $isLocked = true;
        }
    @endphp

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


    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('distribusi-mata-kuliah.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Periode Akademik</label>
                        <select name="period_id" class="form-select select2">
                            @foreach ($periods as $p)
                                <option value="{{ $p->id }}"
                                    {{ request('period_id', $activePeriod->id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Program Studi</label>
                        <select name="prodi_id" class="form-select select2" {{ $isKaprodi ? 'disabled' : '' }}>

                            @if (!$isKaprodi)
                                <option value="">Semua Program Studi</option>
                            @endif

                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}"
                                    {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->jenjang }} - {{ $prodi->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($isKaprodi)
                            <input type="hidden" name="prodi_id" value="{{ request('prodi_id') }}">
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select select2" data-placeholder="Pilih Semester ...">
                            <option value="">Semua Semester</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>
                                    Semester {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" title="Filter Data"><i
                                class='bx bx-filter-alt'></i>&nbsp;Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- LOGIC STATUS DOKUMEN & TOMBOL SUBMIT Versi Desktop --}}
    @if (request('prodi_id') && $activePeriod)
        <div
            class="card shadow mb-4 d-none d-md-block bg-label-{{ $documentData ? $documentData->status_color : 'secondary' }}">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">Status Dokumen :
                        <small class="badge bg-{{ $documentData ? $documentData->status_color : 'secondary' }}">
                            {{ $documentData ? $documentData->status_text : 'Draft (Belum Diajukan)' }}
                        </small>
                    </h5>

                    @if ($documentData && $documentData->feedback_message && $documentData->status == 'rejected')
                        <div class="text-danger mt-1">
                            <i class='bx bx-error-circle'></i> <strong>Revisi:</strong>
                            {{ $documentData->feedback_message }}
                        </div>
                    @else
                        <small class="text-muted">Pastikan semua data sudah benar sebelum mengajukan.</small>
                    @endif
                </div>
                <div>
                    @php
                        $isLocked = $documentData && !in_array($documentData->status, ['draft', 'rejected']);
                    @endphp

                    @if (!$isLocked)
                        <form action="{{ route('distribusi-mata-kuliah.submit') }}" method="POST"
                            id="submitDistribusiForm">
                            @csrf
                            <input type="hidden" name="period_id" value="{{ $activePeriod->id }}">
                            <input type="hidden" name="prodi_id" value="{{ request('prodi_id') }}">

                            <button type="button" class="btn btn-outline-primary" id="btnSubmitDistribusi">
                                <i class='bx bx-send me-1'></i> Ajukan
                            </button>
                        </form>
                    @else
                        {{-- Info Terkunci --}}
                        {{-- <div class="text-end small text-muted mt-1">
                            Menunggu:
                            @switch($documentStatus)
                                @case('submitted')
                                    Kaprodi
                                @break

                                @case('approved_kaprodi')
                                    Wadir 1
                                @break

                                @case('approved_wadir1')
                                    Wadir 2
                                @break

                                @case('approved_wadir2')
                                    Direktur
                                @break

                                @case('approved_direktur')
                                    Selesai
                                @break
                            @endswitch
                        </div> --}}
                    @endif
                </div>
            </div>
        </div>
    @endif
    {{-- LOGIC STATUS DOKUMEN & TOMBOL SUBMIT Versi Mobile --}}
    @if (request('prodi_id') && $activePeriod)
        <div
            class="card shadow-sm mb-4 d-md-none bg-label-{{ $documentData ? $documentData->status_color : 'secondary' }}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Status Dokumen</h6>
                    <span class="badge bg-{{ $documentData ? $documentData->status_color : 'secondary' }}">
                        {{ $documentData ? $documentData->status_text : 'Draft' }}
                    </span>
                </div>

                @if ($documentData && $documentData->feedback_message && $documentData->status == 'rejected')
                    <div class="alert alert-danger p-2 small mb-3">
                        <i class='bx bx-error-circle me-1'></i>
                        <strong>Revisi:</strong> {{ $documentData->feedback_message }}
                    </div>
                @else
                    <p class="small text-muted mb-3">
                        {{ $documentData ? 'Terakhir diupdate ' . $documentData->updated_at->diffForHumans() : 'Pastikan data benar sebelum diajukan.' }}
                    </p>
                @endif

                @php
                    $isLocked = $documentData && !in_array($documentData->status, ['draft', 'rejected']);
                @endphp

                @if (!$isLocked)
                    <form action="{{ route('distribusi-mata-kuliah.submit') }}" method="POST"
                        id="submitDistribusiFormMobile">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $activePeriod->id }}">
                        <input type="hidden" name="prodi_id" value="{{ request('prodi_id') }}">

                        <button type="button" class="btn btn-outline-primary w-100" id="btnSubmitDistribusiMobile">
                            <i class='bx bx-send me-1'></i> Ajukan
                        </button>
                    </form>
                @else
                    <button class="btn btn-secondary w-100 mb-2" disabled>
                        <i class='bx bx-lock-alt me-1'></i> Menunggu Persetujuan
                    </button>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Posisi Saat Ini:</span>
                        <strong>
                            @switch($documentStatus)
                                @case('submitted')
                                    Kaprodi
                                @break

                                @case('approved_kaprodi')
                                    Wadir 1
                                @break

                                @case('approved_wadir1')
                                    Wadir 2
                                @break

                                @case('approved_wadir2')
                                    Direktur
                                @break

                                @case('approved_direktur')
                                    Selesai
                                @break
                            @endswitch
                        </strong>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Nama Periode dan tools --}}
    <div class="card mb-4">
        <div class="card-body">
            {{-- Desktop View --}}
            <div class="d-none d-md-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Distribusi Perkuliahan</h5>
                    <small class="d-block mb-3 border-bottom">Management Distribusi Mata Kuliah</small>
                    <span class="badge bg-label-primary"><i class="bx bx-calendar"></i> Periode:
                        {{ $activePeriod->name ?? 'Belum Ada' }}</span>
                </div>
                <div class="d-flex gap-2">
                    @if (!$isLocked)
                        @if ($activePeriod)
                            <form action="{{ route('distributions.generate') }}" method="POST" id="generateForm">
                                @csrf
                                <input type="hidden" name="period_id" value="{{ $activePeriod->id }}">
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#generateModal">
                                    <i class="bx bx-cog me-1"></i> Generate
                                </button>
                            </form>
                        @endif
                        @if ($activePeriod)
                            <a href="{{ route('distributions.export', array_merge(['period_id' => $activePeriod->id], request()->all())) }}"
                                class="btn btn-success">
                                <i class="bx bx-download me-1"></i>Export
                            </a>
                        @endif
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#updateModal">
                            <i class="bx bx-upload me-1"></i> Import
                        </button>
                    @endif
                    <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddDistribusi" id="btnCreate">
                        <span><i class="bx bx-plus me-2"></i>Distribusi</span>
                    </button>
                </div>
            </div>

            {{-- Mobile View --}}
            <div class="d-md-none">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Distribusi Perkuliahan</h5>
                        <small class="text-muted d-block">Management Distribusi</small>
                    </div>
                    <span class="badge bg-label-primary rounded-pill">
                        <i class="bx bx-calendar"></i> {{ $activePeriod->name ?? '-' }}
                    </span>
                </div>

                <div class="row g-2">
                    @if (!$isLocked)
                        @if ($activePeriod)
                            <div class="col-4">
                                <button type="button"
                                    class="btn btn-label-warning w-100 h-100 p-2 d-flex flex-column align-items-center justify-content-center gap-1"
                                    data-bs-toggle="modal" data-bs-target="#generateModal">
                                    <i class="bx bx-cog fs-4"></i>
                                    <span class="small fw-medium">Generate</span>
                                </button>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('distributions.export', array_merge(['period_id' => $activePeriod->id], request()->all())) }}"
                                    class="btn btn-label-success w-100 h-100 p-2 d-flex flex-column align-items-center justify-content-center gap-1">
                                    <i class="bx bx-download fs-4"></i>
                                    <span class="small fw-medium">Export</span>
                                </a>
                            </div>
                        @endif
                        <div class="col-4">
                            <button type="button"
                                class="btn btn-label-primary w-100 h-100 p-2 d-flex flex-column align-items-center justify-content-center gap-1"
                                data-bs-toggle="modal" data-bs-target="#updateModal">
                                <i class="bx bx-upload fs-4"></i>
                                <span class="small fw-medium">Import</span>
                            </button>
                        </div>
                    @endif
                    <div class="col-12">
                        <button class="btn btn-primary w-100 add-new p-2 shadow-sm" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDistribusi">
                            <i class="bx bx-plus-circle me-1"></i> Tambah Distribusi Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $groupedByClass = $distributions->groupBy('study_class_id');
    @endphp

    @forelse($groupedByClass as $classId => $items)
        @php
            $kelas = $items->first()->studyClass;
        @endphp
        <div class="card mb-4 shadow-sm border">

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

            {{-- BODY CARD: TABEL MATKUL --}}
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="4%" rowspan="2" class="text-center align-center">
                                <input type="checkbox" class="form-check-input select-all-group" data-bs-toggle="tooltip"
                                    title="Pilih semua di kelas ini">
                            </th>
                            <th rowspan="2" class="text-center fw-bold" width="4%">No</th>
                            <th rowspan="2" class="fw-bold">Kode</th>
                            <th rowspan="2" class="fw-bold">Mata Kuliah</th>
                            <th colspan="4" class="text-center border-start border-end py-0 fw-bold">SKS</th>
                            <th rowspan="2" class="fw-bold">Dosen Pengampu</th>
                            <th rowspan="2" class="fw-bold">Dosen Team / PDDIKTI</th>
                            <th rowspan="2" class="fw-bold">Referensi</th>
                            <th rowspan="2" class="fw-bold">Luaran</th>
                            <th width="4%" rowspan="2" class="fw-bold text-center">Aksi</th>
                        </tr>
                        <tr>
                            <th class="text-center border-start border-top py-0" width="3%"><small>T</small></th>
                            <th class="text-center border py-0" width="3%"><small>P</small></th>
                            <th class="text-center border py-0" width="3%"><small>L</small></th>
                            <th class="text-center border py-0" width="3%"><small>JML</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $dist)
                            <tr>

                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input item-checkbox"
                                        value="{{ $dist->id }}">
                                </td>

                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td><span
                                        class="badge bg-label-secondary font-monospace">{{ $dist->course->code ?? '-' }}</span>
                                </td>
                                <td><strong class="text-dark">{{ $dist->course->name ?? '-' }}</strong></td>
                                <td class="text-center border">{{ $dist->course->sks_teori }}</td>
                                <td class="text-center border">{{ $dist->course->sks_praktik }}</td>
                                <td class="text-center border">{{ $dist->course->sks_lapangan }}</td>
                                <td class="text-center border">{{ $dist->course->sksTotal }}</td>
                                <td>
                                    @if ($dist->teachingLecturers->count() > 0)
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach ($dist->teachingLecturers as $dosen)
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bx bx-user-circle me-1 text-primary"></i>
                                                    <span class="text-truncate" style="max-width: 180px;"
                                                        title="{{ $dosen->name }}">
                                                        {{ $dosen->name }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="badge bg-label-danger small fst-italic">Belum Ada</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($dist->pddiktiLecturers->count() > 0)
                                        <ul class="list-unstyled mb-0 small text-muted">
                                            @foreach ($dist->pddiktiLecturers as $dosen)
                                                <li class="text-truncate" style="max-width: 180px;">
                                                    <i
                                                        class="bx bx-check-double me-1 text-success"></i>{{ $dosen->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-warning small fst-italic">- Belum Ada -</span>
                                    @endif
                                </td>

                                <td>{{ $dist->referensi ?? '-' }}</td>
                                <td>{{ $dist->luaran ?? '-' }}</td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-sm btn-icon btn-label-warning edit-record"
                                            data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDistribusi"
                                            data-id="{{ $dist->id }}" data-class-id="{{ $dist->study_class_id }}"
                                            data-course-id="{{ $dist->course_id }}"
                                            data-referensi="{{ $dist->referensi }}" data-luaran="{{ $dist->luaran }}"
                                            data-teaching-ids="{{ json_encode($dist->teachingLecturers->pluck('id')) }}"
                                            data-pddikti-ids="{{ json_encode($dist->pddiktiLecturers->pluck('id')) }}"
                                            data-action="{{ route('distribusi-mata-kuliah.update', $dist->id) }}">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <form action="{{ route('distribusi-mata-kuliah.destroy', $dist->id) }}"
                                            method="POST">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                class="btn btn-sm btn-icon btn-label-danger delete-record"
                                                data-bs-toggle="tooltip" title="Hapus">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card p-5 text-center">
            <div class="mb-3">
                <span class="badge bg-label-warning p-3 rounded-circle">
                    <i class="bx bx-data fs-1"></i>
                </span>
            </div>
            <h4>Belum Ada Data Distribusi</h4>
            <p class="text-muted">Silakan klik tombol <strong>Generate</strong> atau <strong>Tambah Distribusi</strong>
                untuk memulai.</p>
        </div>
    @endforelse

    {{--  Delete masal --}}
    <div id="bulkDeleteBar"
        class="card position-fixed bottom-0 start-50 translate-middle-x mb-4 shadow-lg border-primary d-none"
        style="z-index: 1050; width: 90%; max-width: 600px; border-top: 5px solid #696cff;">
        <div class="card-body d-flex justify-content-between align-items-center p-3">
            <div>
                <span class="fw-bold text-primary"><span id="selectedCount">0</span> Item Dipilih</span>
                <small class="text-muted d-block">Siap untuk dihapus</small>
            </div>
            <div>
                <form action="{{ route('distribusi-mata-kuliah.bulk-destroy') }}" method="POST" id="bulkDeleteForm">
                    @csrf
                    @method('DELETE')
                    {{-- Input hidden ini akan diisi via JS --}}
                    <div id="bulkDeleteInputs"></div>

                    <button type="button" class="btn btn-label-secondary me-2" id="btnCancelBulk">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmBulk">
                        <i class="bx bx-trash me-1"></i> Hapus Terpilih
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- opsi generate --}}
    <div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('distributions.generate') }}" method="POST" class="modal-content"
                id="formGenerate">
                @csrf
                <input type="hidden" name="period_id" value="{{ $activePeriod->id }}">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white mb-2"><i class="bx bx-cog me-2"></i>Generate Distribusi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning text-start small">
                        <i class="bx bx-info-circle me-1"></i>
                        Sistem akan membuatkan slot mata kuliah untuk kelas yang dipilih berdasarkan Kurikulum.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Kelas:</label>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Cari kelas atau pilih berdasarkan semester</small>
                            <div class="btn-group btn-group-xs">
                                <button type="button" class="btn btn-outline-primary" id="btnSelectAllGen">Pilih
                                    Semua</button>
                                <button type="button" class="btn btn-outline-secondary" id="btnClearGen">Reset</button>
                            </div>
                        </div>

                        {{-- Ganti Checkbox dengan Select2 Multiple --}}
                        <select name="class_ids[]" id="selectKelasGenerate" class="form-select" multiple="multiple"
                            style="width: 100%" required>
                            {{-- Grouping berdasarkan Semester agar rapi --}}
                            @foreach ($classes->groupBy('semester') as $smt => $items)
                                <optgroup label="Semester {{ $smt }}">
                                    @foreach ($items as $kls)
                                        <option value="{{ $kls->id }}" data-prodi="{{ $kls->prodi->code }}"
                                            data-shift="{{ ucfirst($kls->shift) }}"
                                            data-students="{{ $kls->total_students }}">
                                            {{ $kls->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Mulai Generate</button>
                </div>
            </form>
        </div>
    </div>

    {{-- edit manual --}}
    <div class="offcanvas offcanvas-end" id="offcanvasAddDistribusi" aria-labelledby="offcanvasAddDistribusiLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddDistribusiLabel" class="offcanvas-title">Tambah Distribusi Mata Kuliah</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 grow-0 p-6 h-100">
            <form class="add-new-distribusi pt-0" id="addNewDistribusiForm"
                action="{{ route('distribusi-mata-kuliah.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <select name="study_class_ids[]" id="selectKelas" class="form-select select2"
                        data-placeholder="Pilih Kelas (Bisa Lebih dari Satu)..." multiple required>
                        @foreach ($classes as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->full_name }}</option>
                        @endforeach
                    </select>
                    <div id="editIdsContainer"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mata Kuliah</label>
                    <select name="course_id" id="selectMatkul" class="form-select select2"
                        data-placeholder="Pilih Mata Kuliah ..." required>
                        <option value=""> Pilih Kelas Terlebih Dahulu </option>
                    </select>
                    <small class="text-muted">Mata kuliah otomatis muncul sesuai kurikulum kelas yang dipilih.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dosen Pengajar</label>
                    <select name="teaching_ids[]" id="edit_teaching_ids" class="form-select select2"
                        data-placeholder="Pilih Dosen Pengajar" multiple="multiple" style="width: 100%">
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dosen Pelaporan</label>
                    <select name="pddikti_ids[]" id="edit_pddikti_ids" class="form-select select2"
                        data-placeholder="Pilih Dosen PDDIKTI" multiple="multiple" style="width: 100%">
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Referensi</label>
                    <textarea name="referensi" id="edit_referensi" rows="3" class="form-control"
                        placeholder="Contoh: Buku Algoritma 2024"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Luaran Mata Kuliah</label>
                    <textarea name="luaran" id="edit_luaran" rows="3" class="form-control"
                        placeholder="Contoh: Mahasiswa mampu membuat program..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100" id="saveBtn">Simpan Perubahan</button>
                <button type="button" class="btn btn-outline-secondary w-100 mt-2"
                    data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>

    {{-- modal import update dosen --}}
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('distributions.import-update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Dosen Massal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small>
                                <i class="bx bx-info-circle"></i>
                                Upload file Excel hasil <b>Export</b> yang sudah Anda lengkapi nama dosennya.
                                Jangan ubah kolom <b>ID_DISTRIBUSI</b>.
                            </small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File Excel</label>
                            <input type="file" name="file" class="form-control" required accept=".xlsx, .xls">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Mulai Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const CONFIG = {
                formId: '#addNewDistribusiForm',
                offcanvasId: '#offcanvasAddDistribusi',
                selectKelasId: '#selectKelas', // ID yang benar sesuai HTML
                selectMatkulId: '#selectMatkul',
                urlStore: "{{ route('distribusi-mata-kuliah.store') }}",
                urlAjaxCourses: "{{ route('ajax.courses', ['classId' => 'PLACEHOLDER']) }}"
            };

            // ==========================================
            // 2. HELPER FUNCTIONS (MODULAR)
            // ==========================================

            /**
             * Inisialisasi Select2 dengan penanganan Error
             */
            const initSelect2 = () => {
                if (typeof $ === 'undefined' || !$.fn.select2) {
                    console.warn('jQuery atau Select2 belum siap, mencoba ulang...');
                    setTimeout(initSelect2, 500);
                    return;
                }

                // A. Select2 di dalam Offcanvas (Wajib dropdownParent)
                $(`${CONFIG.offcanvasId} .select2`).select2({
                    dropdownParent: $(CONFIG.offcanvasId),
                    width: '100%',
                    allowClear: true,
                    placeholder: function() {
                        return $(this).data('placeholder');
                    }
                });

                // B. Select2 Global lainnya (Filter, dll)
                $('.select2').not(`${CONFIG.offcanvasId} .select2`).select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: function() {
                        return $(this).data('placeholder');
                    }
                });
            };

            /**
             * Load Mata Kuliah via AJAX
             */
            const loadCourses = (classId, selectedCourseId = null) => {
                const $courseSelect = $(CONFIG.selectMatkulId);
                const $form = $(CONFIG.formId);

                // Jangan reset jika classId kosong saat mode edit (menghindari clear data tak sengaja)
                if (!classId) {
                    if (!$form.data('isLoadingData')) {
                        $courseSelect.empty().append('<option value="">Pilih Kelas Terlebih Dahulu</option>');
                    }
                    return;
                }

                // Set status loading
                $courseSelect.empty().append('<option value="">Memuat data...</option>').prop('disabled', true);

                // Ganti PLACEHOLDER dengan ID Kelas yang sebenarnya
                const url = CONFIG.urlAjaxCourses.replace('PLACEHOLDER', classId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {
                        $courseSelect.empty();
                        $courseSelect.append('<option value="">-- Pilih Mata Kuliah --</option>');

                        if (data.length === 0) {
                            $courseSelect.append(
                                '<option value="" disabled>Tidak ada matkul di kurikulum ini</option>'
                            );
                        } else {
                            $.each(data, function(key, val) {
                                // Cek apakah matkul ini yang harus dipilih (Mode Edit)
                                let isSelected = (selectedCourseId == val.id) ? 'selected' :
                                    '';
                                let optionText =
                                    `[${val.code}] ${val.name} (Smt ${val.semester})`;
                                $courseSelect.append(
                                    `<option value="${val.id}" ${isSelected}>${optionText}</option>`
                                );
                            });
                        }

                        $courseSelect.prop('disabled', false);
                        $courseSelect.trigger('change'); // Refresh UI Select2
                    },
                    error: function(xhr) {
                        console.error('Error fetching courses:', xhr);
                        $courseSelect.empty().append('<option value="">Gagal memuat data</option>')
                            .prop('disabled', false);
                    }
                });
            };

            // ==========================================
            // 3. MAIN EXECUTION (DOCUMENT READY)
            // ==========================================
            $(function() {
                initSelect2();

                // Variable DOM
                const $form = $(CONFIG.formId);
                const $offcanvas = $(CONFIG.offcanvasId);
                const $selectKelas = $(CONFIG.selectKelasId);
                const bsOffcanvas = new bootstrap.Offcanvas(document.querySelector(CONFIG.offcanvasId));

                // --- EVENT A: Ganti Kelas (Trigger Load Matkul) ---
                $selectKelas.on('change', function() {
                    // Hanya jalankan jika BUKAN sedang proses loading data edit otomatis
                    if (!$form.data('isLoadingData')) {
                        const val = $(this).val();
                        // Ambil ID pertama dari array (karena matkul kurikulum sama satu kelas)
                        const classId = Array.isArray(val) ? val[0] : val;
                        loadCourses(classId);
                    }
                });

                // --- EVENT B: Tombol Edit Klik ---
                $('body').on('click', '.edit-record', function() {
                    const button = $(this);
                    const submitUrl = button.data('action');

                    // Flagging: Beritahu sistem kita sedang mengisi data secara otomatis
                    $form.data('isLoadingData', true);

                    // 1. Setup UI Form
                    $('#offcanvasAddDistribusiLabel').text('Edit Distribusi (Single)');
                    $('#saveBtn').text('Simpan Perubahan');
                    $form.attr('action', submitUrl);

                    // 2. Tambah Method PUT
                    if ($form.find('input[name="_method"]').length === 0) {
                        $form.append('<input type="hidden" name="_method" value="PUT">');
                    }

                    // 3. Isi Input Text
                    $('#edit_referensi').val(button.data('referensi'));
                    $('#edit_luaran').val(button.data('luaran'));

                    // 4. Isi Select Kelas (PENTING: Gunakan Array untuk Select2 Multiple)
                    const classId = button.data('class-id');
                    // Kita bungkus dalam array [classId] agar select2 membacanya sebagai selected item
                    $selectKelas.val([classId]).trigger('change');

                    // 5. Load Data Matkul & Dosen
                    const courseId = button.data('course-id');
                    const teachingIds = button.data('teaching-ids');
                    const pddiktiIds = button.data('pddikti-ids');

                    // Panggil Load Courses manual dengan parameter courseId agar terpilih
                    if (classId) {
                        loadCourses(classId, courseId);
                    }

                    $('#edit_teaching_ids').val(teachingIds).trigger('change');
                    $('#edit_pddikti_ids').val(pddiktiIds).trigger('change');

                    // 6. Buka Offcanvas
                    bsOffcanvas.show();

                    // 7. Lepas Flag Loading
                    setTimeout(() => {
                        $form.data('isLoadingData', false);
                    }, 500);
                });

                // --- EVENT C: Tombol Tambah Baru Klik ---
                $('.add-new').on('click', function() {
                    $form.data('isLoadingData', false);

                    // Reset Total
                    $form[0].reset(); // Reset native form
                    $selectKelas.val(null).trigger('change'); // Reset Select2 Kelas
                    $(CONFIG.selectMatkulId).empty().trigger('change'); // Reset Select2 Matkul
                    $('#edit_teaching_ids').val(null).trigger('change');
                    $('#edit_pddikti_ids').val(null).trigger('change');
                    $('input[name="_method"]').remove(); // Hapus method PUT

                    $('#offcanvasAddDistribusiLabel').text('Tambah Distribusi Mata Kuliah');
                    $('#saveBtn').text('Simpan');
                    $form.attr('action', CONFIG.urlStore);

                    bsOffcanvas.show();
                });
            });

            // --- Script Bulk Delete ---
            const bulkDeleteBar = document.getElementById('bulkDeleteBar');
            const selectedCountSpan = document.getElementById('selectedCount');
            const bulkDeleteInputs = document.getElementById('bulkDeleteInputs');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            let selectedIds = new Set();

            // Handle Select All per Group
            document.querySelectorAll('.select-all-group').forEach(headerCheckbox => {
                headerCheckbox.addEventListener('change', function() {
                    const table = this.closest('table');
                    const checkboxes = table.querySelectorAll('.item-checkbox');
                    checkboxes.forEach(cb => {
                        cb.checked = this.checked;
                        updateSelection(cb.value, this.checked);
                    });
                    updateUI();
                });
            });

            // Handle Individual Checkbox
            document.body.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-checkbox')) {
                    updateSelection(e.target.value, e.target.checked);
                    updateUI();

                    // Uncheck header jika ada yg di-uncheck
                    const table = e.target.closest('table');
                    const headerCheckbox = table.querySelector('.select-all-group');
                    if (!e.target.checked && headerCheckbox) headerCheckbox.checked = false;
                }
            });

            function updateSelection(val, isSelected) {
                // Value bisa berupa string "1,2,3" (karena grouping ID)
                // Kita harus memecahnya menjadi ID tunggal
                const ids = val.toString().split(',');
                ids.forEach(id => {
                    if (isSelected) selectedIds.add(id);
                    else selectedIds.delete(id);
                });
            }

            function updateUI() {
                selectedCountSpan.textContent = selectedIds.size;
                if (selectedIds.size > 0) {
                    bulkDeleteBar.classList.remove('d-none');
                    bulkDeleteBar.classList.add('d-flex');
                } else {
                    bulkDeleteBar.classList.add('d-none');
                    bulkDeleteBar.classList.remove('d-flex');
                }
            }

            document.getElementById('btnCancelBulk').addEventListener('click', function() {
                selectedIds.clear();
                document.querySelectorAll('.item-checkbox, .select-all-group').forEach(cb => cb.checked =
                    false);
                updateUI();
            });

            document.getElementById('btnConfirmBulk').addEventListener('click', function() {
                Swal.fire({
                    title: 'Hapus ' + selectedIds.size + ' Data?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        bulkDeleteInputs.innerHTML = '';
                        selectedIds.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = id;
                            bulkDeleteInputs.appendChild(input);
                        });
                        bulkDeleteForm.submit();
                    }
                });
            });

            // --- Script Generate Modal (Select2) ---
            const generateModal = document.getElementById('generateModal');
            if (generateModal) {
                generateModal.addEventListener('shown.bs.modal', function() {
                    $('#selectKelasGenerate').select2({
                        placeholder: "Pilih kelas...",
                        dropdownParent: $('#generateModal'),
                        closeOnSelect: false,
                        allowClear: true,
                        // (Opsional) Format template result/selection Anda bisa dimasukkan disini
                    });
                });
            }
            $('#btnSelectAllGen').click(function() {
                let allValues = [];
                $('#selectKelasGenerate option').each(function() {
                    if ($(this).val()) allValues.push($(this).val());
                });
                $('#selectKelasGenerate').val(allValues).trigger('change');
            });
            $('#btnClearGen').click(function() {
                $('#selectKelasGenerate').val(null).trigger('change');
            });

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
                            confirmButton: 'btn btn-primary me-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                }
            });

            const successToast = document.getElementById('successToast');
            if (successToast) {
                new bootstrap.Toast(successToast, {
                    delay: 5000
                }).show();
            }
            const errorToast = document.getElementById('errorToast');
            if (errorToast) {
                new bootstrap.Toast(errorToast, {
                    delay: 5000
                }).show();
            }

            // Handler Submit Distribusi (Ajukan ke Kaprodi)
            const btnSubmitDistribusi = document.getElementById('btnSubmitDistribusi');
            if (btnSubmitDistribusi) {
                btnSubmitDistribusi.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Ajukan Dokumen?',
                        text: "Data akan dikunci dan diteruskan ke Kaprodi. Pastikan data sudah benar!",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ajukan!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            title: 'my-0 py-0',
                            htmlContainer: 'py-0 my-0',
                            confirmButton: 'btn btn-primary me-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('submitDistribusiForm').submit();
                        }
                    });
                });
            }

            // Handler Submit Distribusi Mobile
            const btnSubmitDistribusiMobile = document.getElementById('btnSubmitDistribusiMobile');
            if (btnSubmitDistribusiMobile) {
                btnSubmitDistribusiMobile.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Ajukan Dokumen?',
                        text: "Data akan dikunci dan diteruskan ke Kaprodi. Pastikan data sudah benar!",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ajukan!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            title: 'my-0 py-0',
                            htmlContainer: 'py-0 my-0',
                            confirmButton: 'btn btn-primary me-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('submitDistribusiFormMobile').submit();
                        }
                    });
                });
            }


            @if ($errors->any())
                const offcanvasError = new bootstrap.Offcanvas(offcanvasEl);
                offcanvasError.show();
            @endif
        });
    </script>
@endsection

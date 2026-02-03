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
                        <select name="prodi_id" class="form-select select2">
                            <option value="">Semua Program Studi</option>
                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}"
                                    {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->jenjang }} - {{ $prodi->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select select2">
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
        <div class="card shadow mb-4 d-none d-md-block bg-label-{{ $documentData ? $documentData->status_color : '' }}">
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

                            <button type="button" class="btn btn-primary" id="btnSubmitDistribusi">
                                <i class='bx bx-send me-1'></i> Ajukan ke Kaprodi
                            </button>
                        </form>
                    @else
                        {{-- Info Terkunci --}}

                        <button class="btn btn-secondary" disabled>
                            <i class='bx bx-lock-alt me-1'></i> Menungggu acc...
                        </button>
                        <div class="text-end small text-muted mt-1">
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
                        </div>
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

                        <button type="button" class="btn btn-primary w-100" id="btnSubmitDistribusiMobile">
                            <i class='bx bx-send me-1'></i> Ajukan ke Kaprodi
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
            <div class="d-flex justify-content-between align-items-center">
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
                                <button type="button" class="btn btn-warning" id="btnGenerate">
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
                        <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasAddDistribusi" id="btnCreate">
                            <span><i class="bx bx-plus me-2"></i>Distribusi</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- tools buat import/kelas + tambah satuan --}}
    {{-- <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    
                <div class="col-6 text-end">
                     <button type="button" class="btn btn-success me-2" data-bs-toggle="modal"
                        data-bs-target="#importModal">
                        <i class="bx bx-spreadsheet me-1"></i> Import
                    </button>

                </div>
            </div>

        </div>
    </div> --}}

    @forelse($distributions as $groupKey => $groupItems)
        @php
            $sample = $groupItems->first()->studyClass;
            $listKelas = $groupItems->unique('study_class_id')->map(function ($item) {
                return $item->studyClass;
            });
            $kurikulumName = $sample->kurikulum->tanggal ?? '-';
            preg_match('/\d{4}/', $kurikulumName, $matches);
            $tahunKurikulum = $matches[0] ?? $kurikulumName;
        @endphp

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-label-primary p-3">
                <div class="row align-items-center">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <h4 class="text-primary fw-bold mb-1">
                            <i class='bx bx-building'></i> {{ $sample->prodi->name }}
                        </h4>
                        <div class="fs-6 text-secondary">
                            <span class="badge bg-primary me-1">Semester {{ $sample->semester }}</span>
                            <span class="badge bg-label-secondary">Angkatan {{ $sample->angkatan }}</span>
                            <span class="badge bg-label-danger">{{ ucfirst($sample->shift) }}</span>
                        </div>
                    </div>
                    <div class="col-md-5 mb-2 mb-md-0 border-start ps-md-4">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Daftar Kelas:</small>
                        <ul class="list-unstyled mb-0 mt-1">
                            @foreach ($listKelas as $kelas)
                                <li class="mb-1 d-flex align-items-center text-body">
                                    <span class="fw-bold me-2">{{ $kelas->name }}</span>
                                    <small class="text-muted me-2">({{ $kelas->total_students }} Mhs)</small>
                                    <small class="text-muted fst-italic">
                                        <i class='bx bx-user-voice' style="font-size: 0.8rem"></i>
                                        PA: {{ $kelas->academicAdvisor->name ?? 'Belum diset' }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-2 text-md-end">
                        <small class="d-block text-muted">Kurikulum</small>
                        <span class="fw-bold fs-5">{{ $tahunKurikulum }}</span>
                    </div>
                </div>
            </div>
            <div class="table-responsive text-nowrap ">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            @if (!$isLocked)
                                <th width="5%" class="text-center py-0 border-none">
                                    <input type="checkbox" class="form-check-input select-all-group"
                                        data-bs-toggle="tooltip" title="Pilih semua di kelas ini">
                                </th>
                            @endif
                            <th rowspan="2" class="text-center fw-bold" width="5%">No</th>
                            <th rowspan="2" class="fw-bold">Kode</th>
                            <th rowspan="2" class="fw-bold">Mata Kuliah</th>
                            <th colspan="4" class="text-center border-start border-end py-0 fw-bold">SKS</th>
                            <th rowspan="2" class="fw-bold">Dosen Pengampu</th>
                            <th rowspan="2" class="fw-bold">Dosen Team / PDDIKTI</th>
                            <th rowspan="2" class="fw-bold">Referensi</th>
                            <th rowspan="2" class="fw-bold">Luaran</th>
                            @if (!$isLocked)
                                <th rowspan="2" class="fw-bold text-center">Aksi</th>
                            @endif
                        </tr>
                        <tr>
                            @if (!$isLocked)
                                <th class="py-0 border-none"></th>
                            @endif
                            <th class="text-center border-start border-top py-0" width="5%"><small>T</small></th>
                            <th class="text-center border py-0" width="5%"><small>P</small></th>
                            <th class="text-center border py-0" width="5%"><small>L</small></th>
                            <th class="text-center border py-0" width="5%"><small>JML</small></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($groupItems->groupBy('course_id') as $courseId => $dists)
                            @php
                                $dist = $dists->first();
                            @endphp
                            <tr>
                                @if (!$isLocked)
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input item-checkbox"
                                            value="{{ $dist->id }}">
                                    </td>
                                @endif
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td><span class="badge bg-label-dark">{{ $dist->course->code ?? '-' }}</span></td>
                                <td>
                                    <strong class="text-muted"
                                        title="{{ $dist->course->name ?? '-' }}">{{ \Illuminate\Support\Str::limit($dist->course->name ?? '-', 25) }}</strong>
                                </td>
                                <td class="text-center border">{{ $dist->course->sks_teori }}</td>
                                <td class="text-center border">{{ $dist->course->sks_praktik }}</td>
                                <td class="text-center border">{{ $dist->course->sks_lapangan }}</td>
                                <td class="text-center fw-bold border">{{ $dist->course->sksTotal }}</td>
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
                                        <span class="text-danger small">- Belum Ada Pengajar -</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($dist->pddiktiLecturers->count() > 0)
                                        <ul class="list-unstyled mb-0 small text-secondary">
                                            @foreach ($dist->pddiktiLecturers as $dosen)
                                                <li><i class="bx bx-check-circle text-success"
                                                        style="font-size: 0.8rem"></i> {{ $dosen->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="badge bg-label-warning">Belum Lapor</span>
                                    @endif
                                </td>

                                <td>{{ $dist->referensi ?? '-' }}</td>
                                <td>{{ $dist->luaran ?? '-' }}</td>

                                @if (!$isLocked)
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:;" class="text-body edit-record me-2"
                                                data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDistribusi"
                                                data-id="{{ $dist->id }}"
                                                data-study-class-id="{{ $dist->study_class_id }}"
                                                data-course-id="{{ $dist->course_id }}"
                                                data-referensi="{{ $dist->referensi }}"
                                                data-luaran="{{ $dist->luaran }}"
                                                data-teaching-ids="{{ $dist->teachingLecturers->pluck('id') }}"
                                                data-pddikti-ids="{{ $dist->pddiktiLecturers->pluck('id') }}"
                                                data-url="{{ route('distribusi-mata-kuliah.edit', $dist->id) }}"
                                                data-action="{{ route('distribusi-mata-kuliah.update', $dist->id) }}">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            </a>
                                            <form action="{{ route('distribusi-mata-kuliah.destroy', $dist->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <a href="javascript:;" class="text-body  delete-record"
                                                    data-bs-toggle="tooltip" data-bs-offset="0,6"
                                                    data-bs-placement="bottom" data-bs-html="true"
                                                    title="Delete Distribusi">
                                                    <i class="bx bx-trash text-danger bx-sm"></i>
                                                </a>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bx bx-error-circle me-2"></i>
            <div>Belum ada data distribusi mata kuliah untuk periode ini. Silakan Generate atau Import data.</div>
        </div>
    @endforelse

    {{-- FLOATING ACTION BAR FOR MASS DELETE --}}
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

    {{-- edit manual --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddDistribusi"
        aria-labelledby="offcanvasAddDistribusiLabel">
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
                    <select name="study_class_id" id="selectKelas" class="form-select select2-edit"
                        aria-placeholder="pilih kelas" required>
                        <option value="">Pilih Kelas</option>
                        @foreach ($classes as $kelas)
                            <option value="{{ $kelas->id }}">
                                {{ $kelas->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mata Kuliah</label>
                    <select name="course_id" id="selectMatkul" class="form-select select2-edit" required>
                        <option value=""> Pilih Kelas Terlebih Dahulu </option>
                    </select>
                    <small class="text-muted">Mata kuliah otomatis muncul sesuai kurikulum kelas yang dipilih.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dosen Pengajar</label>
                    <select name="teaching_ids[]" id="edit_teaching_ids" class="form-select select2-edit"
                        multiple="multiple" style="width: 100%">
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dosen Pelaporan</label>
                    <select name="pddikti_ids[]" id="edit_pddikti_ids" class="form-select select2-edit"
                        multiple="multiple" style="width: 100%">
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

    {{-- modal import data biasa --}}
    {{-- <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('distribusi-mata-kuliah.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small>Download format excel: <a href="{{ route('distribusi-mata-kuliah.template') }}"
                                    class="fw-bold">Klik Disini</a></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Target Kelas</label>
                            <select name="study_class_id" class="form-select select2-import" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($study_classes as $kls)
                                    <option value="{{ $kls->id }}">{{ $kls->prodi->jenjang }}
                                        {{ $kls->prodi->code }} - {{ $kls->name }} ({{ ucfirst($kls->shift) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Excel</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div> --}}

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
            function initSelect2() {
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    if ($('.select2-edit').hasClass("select2-hidden-accessible")) {
                        $('.select2-edit').select2('destroy');
                    }
                    $('.select2-edit').select2({
                        dropdownParent: $('#offcanvasAddDistribusi'),
                        width: '100%',
                        placeholder: '-- Pilih Dosen --',
                        allowClear: true
                    });
                }
            }

            var myOffcanvas = document.getElementById('offcanvasAddDistribusi');
            myOffcanvas.addEventListener('shown.bs.offcanvas', function() {
                initSelect2();
            });

            $('body').on('click', '.edit-record', function() {
                var button = $(this);
                var id = button.data('id');
                var fetchUrl = button.data('url');
                var submitUrl = button.data('action');

                $('#addNewDistribusiForm').attr('action', submitUrl);
                $('#offcanvasAddDistribusiLabel').text('Edit Distribusi Mata Kuliah');
                $('#saveBtn').text('Simpan Perubahan');

                let methodInput = $('#addNewDistribusiForm').find('input[name="_method"]');
                if (methodInput.length === 0) {
                    $('#addNewDistribusiForm').append('<input type="hidden" name="_method" value="PUT">');
                }

                $.ajax({
                    url: fetchUrl,
                    type: 'GET',
                    success: function(response) {
                        $('#edit_referensi').val(response.referensi);
                        $('#edit_luaran').val(response.luaran);
                        $('#selectKelas').val(response.study_class_id).trigger('change');

                        loadCourses(response.study_class_id, response.course_id);

                        let teachingIds = response.teaching_lecturers.map(item => item.id);
                        let pddiktiIds = response.pddikti_lecturers.map(item => item.id);

                        $('#edit_teaching_ids').val(teachingIds).trigger('change');
                        $('#edit_pddikti_ids').val(pddiktiIds).trigger('change');
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        alert('Gagal mengambil data distribusi.');
                    }
                });
            });

            initSelect2();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const form = document.getElementById('addNewDistribusiForm');
            const offcanvasEl = document.getElementById('offcanvasAddDistribusi');
            const offcanvasTitle = document.getElementById('offcanvasAddDistribusiLabel');
            const saveBtn = document.getElementById('saveBtn');
            const defaultAction = "{{ route('distribusi-mata-kuliah.store') }}";

            function loadCourses(classId, selectedCourseId = null) {
                const courseSelect = $('#selectMatkul');

                // Reset dulu
                courseSelect.empty().append('<option value="">Memuat data...</option>');

                if (!classId) {
                    courseSelect.empty().append('<option value="">Pilih Kelas Terlebih Dahulu</option>');
                    return;
                }

                $.ajax({
                    url: '/ajax/get-courses-by-class/' + classId,
                    type: 'GET',
                    success: function(data) {
                        courseSelect.empty();
                        courseSelect.append('<option value="">-- Pilih Mata Kuliah --</option>');

                        if (data.length === 0) {
                            courseSelect.append(
                                '<option value="" disabled>Tidak ada matkul di kurikulum ini</option>'
                            );
                        }

                        $.each(data, function(key, val) {
                            let optionText = `[${val.code}] ${val.name} (Smt ${val.semester})`;
                            let isSelected = (selectedCourseId && selectedCourseId == val.id) ?
                                'selected' : '';

                            courseSelect.append(
                                `<option value="${val.id}" ${isSelected}>${optionText}</option>`
                            );
                        });

                        courseSelect.trigger('change');
                    },
                    error: function(xhr) {
                        console.error('Error fetching courses:', xhr);
                        courseSelect.empty().append('<option value="">Gagal memuat data</option>');
                    }
                });
            }

            if (typeof $ !== 'undefined') {
                $('#selectKelas').on('change', function() {
                    const classId = $(this).val();
                    if (!form.dataset.isEditing) {
                        loadCourses(classId);
                    }
                });
            }

            document.body.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-record');
                if (editBtn) {
                    const d = editBtn.dataset;

                    offcanvasTitle.textContent = 'Edit Distribusi Mata Kuliah';
                    saveBtn.textContent = 'Simpan Perubahan';
                    form.action = d.action;
                    form.dataset.isEditing = true;

                    let methodInput = form.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);
                    }

                    document.getElementById('edit_referensi').value = d.referensi || '';
                    document.getElementById('edit_luaran').value = d.luaran || '';

                    if (window.$) {
                        $('#selectKelas').val(d.studyClassId).trigger('change');
                        loadCourses(d.studyClassId, d.courseId);

                        if (d.teachingIds) {
                            var teachingIds = JSON.parse(d.teachingIds);
                            $('#edit_teaching_ids').val(teachingIds).trigger('change');
                        }

                        if (d.pddiktiIds) {
                            var pddiktiIds = JSON.parse(d.pddiktiIds);
                            $('#edit_pddikti_ids').val(pddiktiIds).trigger('change');
                        }
                    }
                }
            });

            const btnGenerate = document.getElementById('btnGenerate');
            if (btnGenerate) {
                btnGenerate.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Generate Distribusi?',
                        text: "Sistem akan membuat draft distribusi mata kuliah berdasarkan data kelas & kurikulum.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Generate!',
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
                            Swal.fire({
                                title: 'Sedang Memproses...',
                                text: 'Mohon tunggu, sedang generate data.',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            document.getElementById('generateForm').submit();
                        }
                    });
                });
            }

            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                offcanvasTitle.textContent = 'Tambah Distribusi Mata Kuliah';
                saveBtn.textContent = 'Simpan';
                form.action = defaultAction;
                form.reset();
                delete form.dataset.isEditing;

                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();

                if (window.$) {
                    $('#selectKelas').val('').trigger('change');
                    $('#selectMatkul').empty().trigger('change');
                    $('#edit_teaching_ids').val(null).trigger('change');
                    $('#edit_pddikti_ids').val(null).trigger('change');
                }
            });

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

            // --- SweetAlert Delete Logic (Tetap Sama) ---
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

            // --- Auto Show Error (Tetap Sama) ---
            @if ($errors->any())
                const offcanvasError = new bootstrap.Offcanvas(offcanvasEl);
                offcanvasError.show();
            @endif

            // const scrollContainers = document.querySelectorAll('.horizontal-scroll');
            // scrollContainers.forEach(container => {
            //     new PerfectScrollbar(container, {
            //         suppressScrollY: true,
            //         wheelPropagation: false
            //     });
            // });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bulkDeleteBar = document.getElementById('bulkDeleteBar');
            const selectedCountSpan = document.getElementById('selectedCount');
            const bulkDeleteInputs = document.getElementById('bulkDeleteInputs');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            let selectedIds = new Set();

            // 1. Handle Select All per Group (Per Kelas)
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

            // 2. Handle Individual Checkbox
            document.body.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-checkbox')) {
                    updateSelection(e.target.value, e.target.checked);
                    updateUI();

                    // Uncheck header "Select All" jika ada satu item diuncheck
                    const table = e.target.closest('table');
                    const headerCheckbox = table.querySelector('.select-all-group');
                    if (!e.target.checked && headerCheckbox) {
                        headerCheckbox.checked = false;
                    }
                }
            });

            // Helper: Update Set ID
            function updateSelection(id, isSelected) {
                if (isSelected) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
            }

            // Helper: Update Tampilan Bar
            function updateUI() {
                selectedCountSpan.textContent = selectedIds.size;

                if (selectedIds.size > 0) {
                    bulkDeleteBar.classList.remove('d-none');
                    bulkDeleteBar.classList.add('d-flex'); // Supaya flexbox jalan
                } else {
                    bulkDeleteBar.classList.add('d-none');
                    bulkDeleteBar.classList.remove('d-flex');
                }
            }

            // 3. Tombol Batal
            document.getElementById('btnCancelBulk').addEventListener('click', function() {
                selectedIds.clear();
                document.querySelectorAll('.item-checkbox, .select-all-group').forEach(cb => cb.checked =
                    false);
                updateUI();
            });

            // 4. Tombol Konfirmasi Hapus (SweetAlert)
            document.getElementById('btnConfirmBulk').addEventListener('click', function() {
                Swal.fire({
                    title: 'Hapus ' + selectedIds.size + ' Data?',
                    text: "Anda akan menghapus distribusi mata kuliah yang dipilih. Data yang sudah ada jadwalnya mungkin tidak akan terhapus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Semua!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Masukkan ID ke form
                        bulkDeleteInputs.innerHTML = '';
                        selectedIds.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = id;
                            bulkDeleteInputs.appendChild(input);
                        });

                        // Submit
                        bulkDeleteForm.submit();
                    }
                });
            });
        });
    </script>
@endsection

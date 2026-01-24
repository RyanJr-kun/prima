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

    {{-- filter --}}
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

    {{-- Nama Periode dan tools --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0 text-primary">
                    <i class="bx bx-calendar"></i> Periode: {{ $activePeriod->name ?? 'Belum Ada' }}
                </h5>

                <div class="d-flex gap-2">
                    @if ($activePeriod)
                        <form action="{{ route('distributions.generate') }}" method="POST" id="generateForm">
                            @csrf
                            <input type="hidden" name="period_id" value="{{ $activePeriod->id }}">
                            <button type="submit" class="btn btn-warning">
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

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateModal">
                        <i class="bx bx-upload me-1"></i> Import
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- tools buat import/kelas + tambah satuan --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <iv>
                        <h5 class="fw-bold mb-0">Distribusi Perkuliahan</h5>
                        <small>Management Distribusi Mata Kuliah</small>
                </div>
                <div class="col-6 text-end">
                    {{-- <button type="button" class="btn btn-success me-2" data-bs-toggle="modal"
                        data-bs-target="#importModal">
                        <i class="bx bx-spreadsheet me-1"></i> Import
                    </button> --}}
                    <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddDistribusi" id="btnCreate">
                        <span><i class="bx bx-plus me-2"></i>Distribusi</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

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
                            <span class="badge bg-label-info">{{ ucfirst($sample->shift) }}</span>
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
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center fw-bold" width="5%">No</th>
                            <th rowspan="2" class="fw-bold">Kode</th>
                            <th rowspan="2" class="fw-bold">Mata Kuliah</th>
                            <th colspan="4" class="text-center border py-0 fw-bold">SKS</th>
                            <th rowspan="2" class="fw-bold">Dosen Pengampu</th>
                            <th rowspan="2" class="fw-bold">Dosen Team / PDDIKTI</th>
                            <th rowspan="2" class="fw-bold text-center">Aksi</th>
                        </tr>
                        <tr>
                            <th class="text-center border py-0" width="5%"><small>T</small></th>
                            <th class="text-center border py-0" width="5%"><small>P</small></th>
                            <th class="text-center border py-0" width="5%"><small>L</small></th>
                            <th class="text-center border py-0" width="5%"><small>Total</small></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($groupItems->groupBy('course_id') as $courseId => $dists)
                            @php
                                $dist = $dists->first();
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td><span class="badge bg-label-dark">{{ $dist->course->code ?? '-' }}</span></td>
                                <td>
                                    <strong class="text-muted"
                                        title="{{ $dist->course->name ?? '-' }}">{{ \Illuminate\Support\Str::limit($dist->course->name ?? '-', 35) }}</strong>
                                </td>
                                <td class="text-center border-start">{{ $dist->course->sks_teori }}</td>
                                <td class="text-center">{{ $dist->course->sks_praktik }}</td>
                                <td class="text-center">{{ $dist->course->sks_lapangan }}</td>
                                <td class="text-center fw-bold border-end">{{ $dist->course->sksTotal }}</td>
                                <td>
                                    @if ($dist->user)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($dist->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <span>{{ $dist->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-label-danger">Belum diset</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($dist->pddiktiUser)
                                        <span class="text-secondary">{{ $dist->pddiktiUser->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:;" class="text-body edit-record me-2"
                                            data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDistribusi"
                                            data-id="{{ $dist->id }}"
                                            data-study-class-id="{{ $dist->study_class_id }}"
                                            data-course-id="{{ $dist->course_id }}"
                                            data-course-name="{{ $dist->course->name }} - {{ $dist->course->code }}"
                                            data-user-id="{{ $dist->user_id }}"
                                            data-pddikti-user-id="{{ $dist->pddikti_user_id }}"
                                            data-referensi="{{ $dist->referensi }}" data-luaran="{{ $dist->luaran }}"
                                            data-action="{{ route('distribusi-mata-kuliah.update', $dist->id) }}">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        </a>
                                        <form action="{{ route('distribusi-mata-kuliah.destroy', $dist->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <a href="javascript:;" class="text-body  delete-record"
                                                data-bs-toggle="tooltip" data-bs-offset="0,6" data-bs-placement="bottom"
                                                data-bs-html="true" title="Delete Distribusi">
                                                <i class="bx bx-trash text-danger bx-sm"></i>
                                            </a>
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
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bx bx-error-circle me-2"></i>
            <div>Belum ada data distribusi mata kuliah untuk periode ini. Silakan Generate atau Import data.</div>
        </div>
    @endforelse

    {{-- edit manual --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddDistribusi"
        aria-labelledby="offcanvasAddDistribusiLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddDistribusiLabel" class="offcanvas-title">Tambah Distribusi Mata Kuliah</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 grow-0 p-6 h-100">
            <form class="add-new-distribusi pt-0" id="addNewDistribusiForm"
                action="{{ route('distribusi-mata-kuliah.create') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <select name="study_class_id" id="selectKelas" class="form-select select2" required>
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
                    <select name="course_id" id="selectMatkul" class="form-select select2" required>
                        <option value=""> Pilih Kelas Terlebih Dahulu </option>
                    </select>
                    <small class="text-muted">Mata kuliah otomatis muncul sesuai kurikulum kelas yang dipilih.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dosen Pengampu Utama</label>
                    <select name="user_id" id="edit_user_id" class="form-select select2-edit" style="width: 100%">
                        <option value="">-- Pilih Dosen --</option>
                        @if (isset($dosens) && count($dosens) > 0)
                            @foreach ($dosens as $dosen)
                                <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                            @endforeach
                        @else
                            <option value="" disabled>Data Dosen Kosong (Cek Controller)</option>
                        @endif
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dosen Team / PDDIKTI</label>
                    <select name="pddikti_user_id" id="edit_pddikti_user_id" class="form-select select2-edit"
                        style="width: 100%">
                        <option value="">-- Kosong --</option>

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
            <form action="{{ route('distribusi-matkul.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small>Download format excel: <a href="{{ route('distribusi-matkul.template') }}"
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
            // ... (Kode variabel form, offcanvas, dll tetap sama) ...

            // 1. FUNGSI INISIALISASI SELECT2 YANG KUAT
            function initSelect2() {
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    // Hancurkan dulu jika sudah ada (mencegah duplikasi/error)
                    if ($('.select2-edit').hasClass("select2-hidden-accessible")) {
                        $('.select2-edit').select2('destroy');
                    }

                    // Bangun Ulang
                    $('.select2-edit').select2({
                        dropdownParent: $('#offcanvasAddDistribusi'), // Pastikan ID ini BENAR
                        width: '100%',
                        placeholder: '-- Pilih Dosen --',
                        allowClear: true
                    });
                }
            }

            // 2. JALANKAN SAAT TOMBOL TAMBAH/EDIT DIKLIK
            // Ini memastikan Select2 baru dibuat saat Offcanvas BENAR-BENAR akan muncul
            var myOffcanvas = document.getElementById('offcanvasAddDistribusi');
            myOffcanvas.addEventListener('shown.bs.offcanvas', function() {
                initSelect2(); // Re-init saat canvas muncul
            });

            // 3. EDIT RECORD HANDLER (LOGIKA DATA)
            $('body').on('click', '.edit-record', function() {
                var d = $(this).data();

                // Set Action Form
                $('#addNewDistribusiForm').attr('action', d.action);

                // Populate Data Biasa
                $('#selectKelas').val(d.studyClassId).trigger('change');

                // LOGIKA DOSEN (PENTING)
                // Kita set value dengan sedikit delay agar Select2 siap dulu
                setTimeout(function() {
                    if ($('#edit_user_id').find("option[value='" + d.userId + "']").length) {
                        $('#edit_user_id').val(d.userId).trigger('change');
                    } else {
                        console.log('ID Dosen tidak ditemukan di opsi:', d.userId);
                    }

                    $('#edit_pddikti_user_id').val(d.pddiktiUserId).trigger('change');
                }, 200);
            });

            // Panggil sekali di awal untuk jaga-jaga
            initSelect2();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addNewDistribusiForm');
            const offcanvasEl = document.getElementById('offcanvasAddDistribusi');
            const offcanvasTitle = document.getElementById('offcanvasAddDistribusiLabel');
            const saveBtn = document.getElementById('saveBtn');
            const defaultAction = "{{ route('distribusi-mata-kuliah.store') }}"; // Pastikan route store default

            // --- 1. LOGIKA AJAX UNTUK MENGISI MATKUL (SOLUSI SELECT KOSONG) ---
            // Fungsi untuk mengambil course berdasarkan kelas
            function loadCourses(classId, selectedCourseId = null) {
                const courseSelect = $('#selectMatkul');

                // Reset dulu
                courseSelect.empty().append('<option value="">Memuat data...</option>');

                if (!classId) {
                    courseSelect.empty().append('<option value="">Pilih Kelas Terlebih Dahulu</option>');
                    return;
                }

                // Panggil Route AJAX yang sudah ada di web.php
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
                            // Gabungkan Kode dan Nama agar informatif
                            let optionText = `[${val.code}] ${val.name} (Smt ${val.semester})`;
                            let isSelected = (selectedCourseId && selectedCourseId == val.id) ?
                                'selected' : '';

                            courseSelect.append(
                                `<option value="${val.id}" ${isSelected}>${optionText}</option>`
                            );
                        });

                        // Refresh Select2 setelah data masuk
                        courseSelect.trigger('change');
                    },
                    error: function(xhr) {
                        console.error('Error fetching courses:', xhr);
                        courseSelect.empty().append('<option value="">Gagal memuat data</option>');
                    }
                });
            }

            // Event Listener saat Kelas dipilih (Hanya aktif jika menggunakan JQuery untuk Select2)
            if (typeof $ !== 'undefined') {
                $('#selectKelas').on('change', function() {
                    const classId = $(this).val();
                    // Hanya load jika bukan mode edit yang sedang inisialisasi manual
                    // (Kita biarkan logika edit menangani load-nya sendiri agar bisa set value)
                    if (!form.dataset.isEditing) {
                        loadCourses(classId);
                    }
                });
            }


            // --- 2. LOGIKA KLIK TOMBOL EDIT ---
            document.body.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-record');
                if (editBtn) {
                    const d = editBtn.dataset;

                    // Setup Tampilan Offcanvas
                    offcanvasTitle.textContent = 'Edit Distribusi Mata Kuliah';
                    saveBtn.textContent = 'Simpan Perubahan';
                    form.action = d.action; // Action dari data-action tombol

                    // Tambahkan flag agar event change #selectKelas tidak menimpa loading kita
                    form.dataset.isEditing = true;

                    // Inject Method PUT
                    let methodInput = form.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);
                    }

                    // Populate Textareas
                    document.getElementById('edit_referensi').value = d.referensi || '';
                    document.getElementById('edit_luaran').value = d.luaran || '';

                    // Populate Select2 dengan JQuery
                    if (window.$) {
                        // 1. Set Kelas
                        $('#selectKelas').val(d.studyClassId).trigger('change');

                        // 2. Load Matkul dan Set Value setelah load selesai
                        // Kita panggil loadCourses dengan parameter ke-2 (selectedCourseId)
                        loadCourses(d.studyClassId, d.courseId);

                        // 3. Set Dosen
                        $('#edit_user_id').val(d.userId).trigger('change');
                        $('#edit_pddikti_user_id').val(d.pddiktiUserId).trigger('change');
                    }
                }
            });


            // --- 3. RESET SAAT OFFCANVAS DITUTUP ---
            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                offcanvasTitle.textContent = 'Tambah Distribusi Mata Kuliah';
                saveBtn.textContent = 'Simpan';
                form.action = defaultAction;
                form.reset();
                delete form.dataset.isEditing; // Hapus flag edit

                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();

                if (window.$) {
                    $('#selectKelas').val('').trigger('change');
                    // Kosongkan Matkul
                    $('#selectMatkul').empty().append(
                        '<option value="">Pilih Kelas Terlebih Dahulu</option>').trigger('change');
                    $('#edit_user_id').val('').trigger('change');
                    $('#edit_pddikti_user_id').val('').trigger('change');
                }
            });

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
                            confirmButton: 'btn btn-primary me-3',
                            cancelButton: 'btn btn-label-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                }
            });

            // --- Auto Show Error (Tetap Sama) ---
            @if ($errors->any())
                const offcanvasError = new bootstrap.Offcanvas(offcanvasEl);
                offcanvasError.show();
            @endif
        });
    </script>
@endsection

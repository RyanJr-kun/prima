@extends('layouts/contentNavbarLayout')
@section('title', 'Mata Kuliah - PRIMA')
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

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('master.mata-kuliah.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cari Matkul / Kode</label>
                        <input type="text" name="q" class="form-control" placeholder="Nama atau Kode Matkul"
                            value="{{ request('q') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Program Studi</label>
                        <select name="prodi_id" class="form-select select2">
                            <option value="">Semua Program Studi</option>
                            @if (isset($prodis))
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id }}"
                                        {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->jenjang }} - {{ $prodi->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select select2">
                            <option value="">Semua Semester</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>
                                    Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>
                            Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="row">

                <div class="col-md-6">
                    <h5 class="card-title fw-bold mb-0">Mata Kuliah</h5>
                    <small class="d-none d-md-block text-muted">Management Data Matkul Disini.</small>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-center">
                        <form id="syncCourseForm" action="{{ route('mata-kuliah.sync-siakad') }}" method="POST">
                            @csrf
                            <button type="button" class="btn btn-outline-danger" id="btnSyncCourse">
                                <i class="bx bx-refresh me-1"></i> Sync
                            </button>
                        </form>
                        <button type="button" class="btn btn-success mx-2" data-bs-toggle="modal"
                            data-bs-target="#importModal">
                            <i class="bx bx-spreadsheet me-1"></i> Import
                        </button>
                        <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasAddCourse" id="btnCreate">
                            <span><i class="bx bx-plus me-1"></i> Matkul</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table class="table border-top" id="tableUser">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Kode</th>
                        <th rowspan="2">Nama Matkul</th>
                        <th colspan="4" class="text-center py-1 border">SKS</th>
                        <th rowspan="2" class="text-center">Semester</th>
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
                    @foreach ($courses as $key => $matkul)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $matkul->code }}</td>
                            <td>
                                <h6 class="mb-0">{{ \Illuminate\Support\Str::limit($matkul->name ?? '-', 35) }}</h6>
                            </td>
                            <td class="text-center border">{{ $matkul->sks_teori }}</td>
                            <td class="text-center border">{{ $matkul->sks_praktik }}</td>
                            <td class="text-center border">{{ $matkul->sks_lapangan }}</td>
                            <td class="text-center border">{{ $matkul->sksTotal }}</td>

                            <td class="text-center">
                                <span class="badge bg-label-info">Semester {{ $matkul->semester }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:;" class="text-body view-record me-2" data-bs-toggle="popover"
                                        data-bs-trigger="hover focus" data-bs-placement="left" data-bs-html="true"
                                        data-bs-custom-class="popover-primary"
                                        title="<span class='fw-medium'><i class='bx bx-book-open me-1'></i> Detail Mata Kuliah</span>"
                                        data-bs-content="
                                            <div class='d-flex flex-column gap-2 p-1' style='min-width: 240px;'>
                                                <div class='border-bottom'>
                                                    <span class='text-muted small d-block mb-1'>Mata Kuliah:</span>
                                                    <span class='text-body small fw-bold'>{{ $matkul->name }}</span>
                                                </div>
                                                <div class='d-flex justify-content-between align-items-center border-bottom pb-1'>
                                                    <span class='text-muted small'>Kode MK</span>
                                                    <span class='fw-bold small'>{{ $matkul->code }}</span>
                                                </div>
                                                <div class='d-flex justify-content-between align-items-center border-bottom pb-1'>
                                                    <span class='text-muted small'>Prodi</span>
                                                    <span class='fw-bold small'>{{ $matkul->kurikulum->prodi->jenjang }} {{ $matkul->kurikulum->prodi->code }}</span>
                                                </div>
                                                <div class='d-flex justify-content-between align-items-center border-bottom pb-1'>
                                                    <span class='text-muted small'>Total SKS</span>
                                                    <span class='badge bg-label-primary'>{{ $matkul->sksTotal }} SKS</span>
                                                </div>
                                                <div class='d-flex justify-content-between align-items-center border-bottom pb-1'>
                                                    <span class='text-muted small'>Rincian (T/P/L)</span>
                                                    <span class='fw-medium small'>{{ $matkul->sks_teori }} / {{ $matkul->sks_praktik }} / {{ $matkul->sks_lapangan }}</span>
                                                </div>
                                                <div class='align-items-center border-bottom pb-1'>
                                                    <span class='text-muted small'>Fasilitas</span>
                                                   @foreach ($matkul->required_tags ?? [] as $tag)
<span class='badge d-block bg-label-info'>{{ $tags[$tag] ?? $tag }}</span>
@endforeach
                                                </div>
                                                <div>
                                                    <span class='text-muted small d-block mb-1'>Kurikulum:</span>
                                                    <span class='text-body small fw-medium'>{{ $matkul->kurikulum->name }}</span>
                                                </div>
                                            </div>
                                        ">
                                        <i class="bx bx-eye text-muted bx-sm"></i>
                                    </a>
                                    <a href="javascript:;" class="text-body edit-record me-2" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasAddCourse" data-id="{{ $matkul->id }}"
                                        data-code="{{ $matkul->code }}" data-name="{{ $matkul->name }}"
                                        data-semester="{{ $matkul->semester }}"
                                        data-kurikulum-id="{{ $matkul->kurikulum_id }}"
                                        data-sks-teori="{{ $matkul->sks_teori }}"
                                        data-sks-praktik="{{ $matkul->sks_praktik }}"
                                        data-sks-lapangan="{{ $matkul->sks_lapangan }}"
                                        data-required-tags="{{ json_encode($matkul->required_tags ?? []) }}"
                                        data-action="{{ route('master.mata-kuliah.update', $matkul->id) }}">
                                        <i class="bx bx-edit text-muted bx-sm"></i>
                                    </a>
                                    <form action="{{ route('master.mata-kuliah.destroy', $matkul->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <a href="javascript:;" class="text-body delete-record">
                                            <i class="bx bx-trash text-muted bx-sm"></i>
                                        </a>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddCourse"
            aria-labelledby="offcanvasAddCourseLabel">
            <div class="offcanvas-header border-bottom">
                <h5 id="offcanvasAddCourseLabel" class="offcanvas-title">Tambah Mata Kuliah</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body mx-0 grow-0 p-6 h-100">

                <form class="add-new-course pt-0" id="addNewCourseForm" action="{{ route('master.mata-kuliah.store') }}"
                    method="POST">
                    @csrf

                    {{-- Input Kode, Nama, SKS (Sama Seperti Sebelumnya, Tidak Ada Perubahan) --}}
                    <div class="mb-3">
                        <label class="form-label" for="code">Kode</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                            placeholder="TRPL" name="code" value="{{ old('code') }}" />
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="add-name-course">Nama Mata Kuliah</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="add-name-course" placeholder="Contoh: Pemrograman Web I" name="name"
                            value="{{ old('name') }}" />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <label class="form-label" for="#">Masukan Data SKS</label>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Teori</label>
                            <input type="text" class="form-control @error('sks_teori') is-invalid @enderror"
                                id="add-sks-teori" name="sks_teori" value="{{ old('sks_teori', 0) }}" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Praktik</label>
                            <input type="number" class="form-control @error('sks_praktik') is-invalid @enderror"
                                id="add-sks-praktik" name="sks_praktik" value="{{ old('sks_praktik', 0) }}" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Lapangan</label>
                            <input type="number" class="form-control @error('sks_lapangan') is-invalid @enderror"
                                id="add-sks-lapangan" name="sks_lapangan" value="{{ old('sks_lapangan', 0) }}" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Kebutuhan Fasilitas (opsional)
                            <i class="bx bx-help-circle text-muted ms-1" data-bs-toggle="popover" data-bs-placement="top"
                                data-bs-trigger="hover" title="Informasi"
                                data-bs-content="Pilih jika mata kuliah ini WAJIB menggunakan alat tertentu (Bisa pilih lebih dari satu)."></i>
                        </label>
                        <select name="required_tags[]" id="required_tags" class="form-select select2"
                            multiple="multiple" data-placeholder="Pilih Fasilitas">
                            @foreach ($tags as $key => $label)
                                @if ($key !== 'general')
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('required_tags')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="semester">Semester</label>
                        <select name="semester" id="semester" class="form-select select2" required
                            data-placeholder="Pilih Semester">
                            <option value=""></option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                        @error('semester')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="kurikulum_id">Kurikulum</label>
                        <select name="kurikulum_id" id="kurikulum_id" class="form-select select2"
                            data-placeholder="Pilih Kurikulum">
                            <option value=""></option>
                            @if (isset($kurikulums))
                                @foreach ($kurikulums as $kurikulum)
                                    <option value="{{ $kurikulum->id }}">{{ $kurikulum->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('kurikulum_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary me-3" id="saveBtn">Submit</button>
                    <button type="reset" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </form>
            </div>
        </div>
        <div class="modal fade" id="importModal" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('mata-kuliah.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Import Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <small>Download format excel: <a href="{{ route('mata-kuliah.template') }}"
                                        class="fw-bold">Klik Disini</a></small>
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
        </div>
    </div>
@endsection

@section('page-script')
    <script type="module">
        const initSelect2 = () => {
            if (typeof $ !== 'undefined' && $.fn.select2) {

                $('.card-body .select2').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Pilih...'
                });

                $('#offcanvasAddCourse .select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: true,
                        dropdownParent: $('#offcanvasAddCourse'),
                        width: '100%',
                        templateSelection: function(data) {
                            if (!data.id) {
                                return data.text;
                            }

                            const code = $(data.element).data('code');
                            return code ? code : data.text;
                        }
                    });
                });

            } else {

                setTimeout(initSelect2, 100);
            }
        };


        initSelect2();

        const offcanvasElement = document.getElementById('offcanvasAddCourse');
        offcanvasElement.addEventListener('shown.bs.offcanvas', function() {
            initSelect2();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const syncBtn = document.getElementById('btnSyncCourse');
            const syncForm = document.getElementById('syncCourseForm');

            if (syncBtn) {
                syncBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Sync Mata Kuliah?',
                        text: "Data SKS dan Kurikulum akan diperbarui dari Siakad.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Sync!',
                        showLoaderOnConfirm: true,
                        allowOutsideClick: () => !Swal.isLoading(),
                        preConfirm: () => {
                            return new Promise((resolve) => {
                                syncForm.submit();
                                // Promise gantung agar loading terus berputar sampai reload
                            });
                        }
                    });
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addNewCourseForm');
            const offcanvasEl = document.getElementById('offcanvasAddCourse');
            const offcanvasTitle = document.getElementById('offcanvasAddCourseLabel');
            const saveBtn = document.getElementById('saveBtn');
            const defaultAction = form.action;
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));

            const popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    html: true,
                    sanitize: false
                });
            });


            document.body.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-record');
                if (editBtn) {
                    const d = editBtn.dataset;

                    offcanvasTitle.textContent = 'Edit Mata kuliah';
                    saveBtn.textContent = 'Simpan Perubahan';
                    form.action = d.action;

                    let methodInput = form.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);
                    }

                    document.getElementById('code').value = d.code;
                    document.getElementById('add-name-course').value = d.name;
                    document.getElementById('add-sks-teori').value = d.sksTeori;
                    document.getElementById('add-sks-praktik').value = d.sksPraktik;
                    document.getElementById('add-sks-lapangan').value = d.sksLapangan;

                    if (window.$) {
                        //single select
                        $('#semester').val(d.semester).trigger('change');
                        $('#kurikulum_id').val(d.kurikulumId).trigger('change');

                        //multi select
                        try {
                            let tagsArray = JSON.parse(d.requiredTags);

                            $('#required_tags').val(tagsArray).trigger('change');
                        } catch (error) {
                            console.error("Gagal parsing tags:", error);
                            $('#required_tags').val([]).trigger('change');
                        }

                    } else {

                        console.warn("jQuery tidak terdeteksi, Select2 mungkin error");
                    }
                }
            });


            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                offcanvasTitle.textContent = 'Tambah Kurikulum';
                saveBtn.textContent = 'Submit';
                form.action = defaultAction;
                form.reset();

                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();

                // Reset Select2
                if (window.$) {
                    $('#semester').val('').trigger('change');
                    $('#kurikulum_id').val('').trigger('change');
                    $('#required_tags').val([]).trigger('change');
                }
            });

            // Handler untuk tombol delete dengan SweetAlert
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

            // Buka Offcanvas otomatis jika ada error validasi
            @if ($errors->any())
                const offcanvasError = new bootstrap.Offcanvas(offcanvasEl);
                offcanvasError.show();
            @endif


        });
    </script>
@endsection

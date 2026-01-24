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

                <div class="col-6">
                    <h5 class="card-title fw-bold mb-0">Mata Kuliah</h5>
                    <small class="d-none d-md-block text-muted">Management Data Matkul Disini.</small>
                </div>
                <div class="col-6 text-end">
                    <button type="button" class="btn btn-success my-1" data-bs-toggle="modal"
                        data-bs-target="#importModal">
                        <i class="bx bx-spreadsheet me-1"></i> Import
                    </button>
                    <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddCourse" id="btnCreate">
                        <span><i class="bx bx-plus me-2"></i>Matkul</span>
                    </button>

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
                                <h6 class="mb-0">{{ $matkul->name }}</h6>
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
                                                <div class='d-flex justify-content-between align-items-center border-bottom pb-1'>
                                                    <span class='text-muted small'>Fasilitas</span>
                                                    <span class='badge bg-label-warning'>{{ $tags[$matkul->required_tag] ?? 'Standar' }}</span>
                                                </div>
                                                <div>
                                                    <span class='text-muted small d-block mb-1'>Kurikulum:</span>
                                                    <span class='text-body small fw-medium'>{{ $matkul->kurikulum->name }}</span>
                                                </div>
                                            </div>
                                        ">
                                        <i class="bx bx-eye text-muted bx-sm"></i>
                                    </a>
                                    <a href="javascript:;" class="text-body edit-record me-2"
                                        class="text-body edit-record me-2" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasAddCourse" data-id="{{ $matkul->id }}"
                                        data-code="{{ $matkul->code }}" data-name="{{ $matkul->name }}"
                                        data-semester="{{ $matkul->semester }}"
                                        data-kurikulum-id="{{ $matkul->kurikulum_id }}"
                                        data-sks-teori ="{{ $matkul->sks_teori }}"
                                        data-sks-praktik ="{{ $matkul->sks_praktik }}"
                                        data-sks-lapangan ="{{ $matkul->sks_lapangan }}"
                                        data-required-tag="{{ $matkul->required_tag }}"
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
                            <label class="form-label" for="add-sks-teori">Teori</label>
                            <input type="text" class="form-control @error('sks_teori') is-invalid @enderror"
                                id="add-sks-teori" placeholder="3" name="sks_teori" value="{{ old('sks_teori') }}" />
                            @error('sks_teori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="add-sks-praktik">Praktik</label>
                            <input type="number" class="form-control @error('sks_praktik') is-invalid @enderror"
                                id="add-sks-praktik" placeholder="5" name="sks_praktik"
                                value="{{ old('sks_praktik') }}" />
                            @error('sks_praktik')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="add-sks-lapangan">Lapangan</label>
                            <input type="number" class="form-control @error('sks_lapangan') is-invalid @enderror"
                                id="add-sks-lapangan" placeholder="1" name="sks_lapangan"
                                value="{{ old('sks_lapangan') }}" />
                            @error('sks_lapangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            Kebutuhan Fasilitas (opsional)
                            <i class="bx bx-help-circle text-muted ms-1" data-bs-toggle="popover" data-bs-placement="top"
                                data-bs-trigger="hover" title="Informasi"
                                data-bs-content="Pilih jika mata kuliah ini WAJIB menggunakan alat tertentu (Misal: Matkul 'Web' wajib 'Lab Komputer')."></i>
                        </label>
                        <select name="required_tag" id="required_tag"
                            class="form-select select2 @error('required_tag') is-invalid @enderror"
                            data-placeholder="Pilih Fasilitas">
                            <option value=""></option>
                            @foreach ($tags as $key => $label)
                                @if ($key)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endif
                            @endforeach
                            @error('required_tag')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="semester">Semester</label>
                        <select name="semester" id="semester"
                            class="form-select select2 @error('semester') is-invalid @enderror" required
                            data-placeholder="Pilih Semester">
                            <option value=""></option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="kurikulum_id">Kurikulum</label>
                        <select name="kurikulum_id" id="kurikulum_id"
                            class="form-select select2 @error('kurikulum_id') is-invalid @enderror"
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
@endsection

@section('page-script')
    <script type="module">
        // Fungsi inisialisasi
        const initSelect2 = () => {
            // Cek apakah jQuery dan Select2 sudah siap
            if (typeof $ !== 'undefined' && $.fn.select2) {

                // Init Select2 untuk Filter di halaman utama
                $('.card-body .select2').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Pilih...'
                });

                // Targetkan select2 di dalam Offcanvas secara spesifik
                $('#offcanvasAddCourse .select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: true,
                        dropdownParent: $('#offcanvasAddCourse'), // <--- INI KUNCINYA
                        width: '100%', // Paksa lebar agar tidak menyempit
                        templateSelection: function(data) {
                            if (!data.id) {
                                return data.text;
                            }
                            // Gunakan data-code jika ada (untuk prodi), jika tidak gunakan text biasa
                            const code = $(data.element).data('code');
                            return code ? code : data.text;
                        }
                    });
                });

            } else {
                // Jika belum siap, coba lagi dalam 100ms
                setTimeout(initSelect2, 100);
            }
        };

        // Jalankan saat script dimuat
        initSelect2();

        // PENTING: Jalankan ulang saat Offcanvas dibuka (untuk jaga-jaga rendering error)
        const offcanvasElement = document.getElementById('offcanvasAddCourse');
        offcanvasElement.addEventListener('shown.bs.offcanvas', function() {
            initSelect2();
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
                        $('#required_tag').val(d.requiredTag).trigger('change');
                        $('#semester').val(d.semester).trigger('change');
                        $('#kurikulum_id').val(d.kurikulumId).trigger('change');
                    } else {
                        document.getElementById('required_tag').value = d.requiredTag;
                        document.getElementById('semester').value = d.semester;
                        document.getElementById('kurikulum_id').value = d.kurikulumId;
                    }
                }
            });

            // Reset form kembali ke Mode Create saat offcanvas ditutup
            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                offcanvasTitle.textContent = 'Tambah Kurikulum';
                saveBtn.textContent = 'Submit';
                form.action = defaultAction;
                form.reset();

                // Hapus method PUT agar kembali menjadi POST
                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();

                // Reset Select2
                if (window.$) {
                    $('#semester').val('').trigger('change');
                    $('#kurikulum_id').val('').trigger('change');
                    $('#required_tag').val('').trigger('change');
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

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

    <div class="card">

        <div class="card-header border-bottom">
            <div class="row">

                <div class="col-6">
                    <h4 class="card-title mb-0">Mata Kuliah</h4>
                    <small>Management Data Matkul Disini.</small>
                </div>
                <div class="col-6 text-end">
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
                        <th rowspan="2" class="text-center">Kurikulum</th>
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
                                <div>
                                    <h6 class="mb-0">{{ $matkul->name }}</h6>
                                    <small class="text-xs text-truncate">Prodi : {{ $matkul->kurikulum->prodi->jenjang }}
                                        {{ $matkul->kurikulum->prodi->code }} </small>
                                </div>
                            </td>
                            <td class="text-center border">{{ $matkul->sks_teori }}</td>
                            <td class="text-center border">{{ $matkul->sks_praktik }}</td>
                            <td class="text-center border">{{ $matkul->sks_lapangan }}</td>
                            <td class="text-center border">{{ $matkul->sksTotal }}</td>

                            <td class="text-center">
                                <span class="badge bg-label-info">Semester {{ $matkul->semester }}</span>
                            </td>
                            <td class="text-center">{{ $matkul->kurikulum->name }}</td>

                            {{-- <td><span class="badge bg-label-success">Active</span></td> --}}
                            <td>
                                <div class="d-flex align-items-center">
                                    {{--  --}}
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
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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
                        <select name="required_tag"
                            class="form-select select2 @error('requiered_tag') is-invalid @enderror">
                            <option value="">Pilih Fasilitas</option>
                            @foreach ($tags as $key => $label)
                                @if ($key != 'general')
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
                            class="form-select select2 @error('semester') is-invalid @enderror" required>
                            <option value="">Pilih Semester</option>
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
                            class="form-select select2 @error('kurikulum_id') is-invalid @enderror">
                            <option value="">Pilih Kurikulum</option>
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
                        $('#semester').val(d.semester).trigger('change');
                        $('#kurikulum_id').val(d.kurikulumId).trigger('change');
                        $('#required_tag').val(d.requiredTag).trigger('change');
                    } else {
                        document.getElementById('semester').value = d.semester;
                        document.getElementById('kurikulum_id').value = d.kurikulumId;
                        document.getElementById('required_tag').value = d.requiredTag;
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

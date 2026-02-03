@extends('layouts/contentNavbarLayout')
@section('title', 'Program Studi - PRIMA')
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

        <div class="card-header sm-pt-4 sm-pb-0 border-bottom">
            <div class="row">

                <div class="col-6">
                    <h5 class="card-title fw-bold mb-0">Program Studi</h5>
                    <small class="d-none d-md-block text-muted">Management Data Prodi Disini.</small>
                </div>
                <div class="col-6 text-end">
                    <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddProdi" id="btnCreate">
                        <span><i class="bx bx-plus me-2"></i>Kelas</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table class="table border-top" id="tableUser">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>kode</th>
                        <th>Program Studi</th>
                        <th>Jenjang</th>
                        <th>Kepala Prodi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prodis as $key => $prodi)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $prodi->code }}</td>
                            <td>{{ $prodi->name }}</td>
                            <td>
                                <p class="mb-0">{{ $prodi->jenjang }}</p>
                                <small>{{ $prodi->lama_studi }} Semester</small>
                            </td>
                            <td>
                                <div class="d-flex justify-content-start align-items-center user-name">
                                    <div class="avatar-wrapper">
                                        <div class="avatar avatar-sm me-3">
                                            <span
                                                class="avatar-initial rounded-circle bg-label-primary">{{ substr($prodi->kaprodi->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column text-body">
                                        <a href="javascript:;"
                                            class="text-truncate text-heading">{{ $prodi->kaprodi->name }}</a>
                                        <small>{{ $prodi->kaprodi->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">

                                    <a href="javascript:;" class="text-body edit-record me-2" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasAddProdi" data-id="{{ $prodi->id }}"
                                        data-code="{{ $prodi->code }}" data-name="{{ $prodi->name }}"
                                        data-jenjang="{{ $prodi->jenjang }}" data-lama-studi="{{ $prodi->lama_studi }}"
                                        data-kaprodi-id="{{ $prodi->kaprodi_id }}" {{-- TAMBAHAN: Data Primary Campus --}}
                                        data-primary-campus="{{ $prodi->primary_campus }}"
                                        data-action="{{ route('master.program-studi.update', $prodi->id) }}">
                                        <i class="bx bx-edit text-muted bx-sm"></i>
                                    </a>
                                    <form action="{{ route('master.program-studi.destroy', $prodi->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <a href="javascript:;" class="text-body delete-record" data-bs-toggle="tooltip"
                                            data-bs-offset="0,6" data-bs-placement="bottom" data-bs-html="true"
                                            title="Delete Program Studi">
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

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddProdi" aria-labelledby="offcanvasAddProdiLabel">
            <div class="offcanvas-header border-bottom">
                <h5 id="offcanvasAddProdiLabel" class="offcanvas-title">Tambah Program Studi</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body mx-0 grow-0 p-6 h-100">

                <form class="add-new-prodi pt-0" id="addNewProdiForm" action="{{ route('master.program-studi.store') }}"
                    method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="code">kode</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                            placeholder="TRPL" name="code" value="{{ old('code') }}" />
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="add-name-prodi">Nama Program Studi</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="add-name-prodi" placeholder="Teknologi Rekayasa Perangkat Lunak" name="name"
                            value="{{ old('name') }}" />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Homebase Kampus (Prioritas Jadwal)</label>
                        <select name="primary_campus" id="primary_campus"
                            class="form-select select2 @error('primary_campus') is-invalid @enderror"
                            data-placeholder="Pilih Kampus Utama" required>
                            <option value="">-- Pilih Kampus Utama --</option>
                            <option value="kampus_1">Kampus 1 (Pusat/Teknik)</option>
                            <option value="kampus_2">Kampus 2 (Kesehatan)</option>
                        </select>
                        @error('primary_campus')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenjang</label>
                        <select name="jenjang" id="jenjang" class="form-select select2"
                            data-placeholder="Pilih Jenjang">
                            <option value=""></option>
                            <option value="D1">Diploma 1</option>
                            <option value="D2">Diploma 2</option>
                            <option value="D3">Diploma 3</option>
                            <option value="D4">Diploma 4</option>
                            <option value="S1">Sarjana 1</option>
                            <option value="S2">Sarjana 2</option>
                            <option value="S3">Sarjana 3</option>
                        </select>
                        @error('jenjang_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lama Studi (Jumlah Semester)</label>
                        <input type="number" id="lamaStudi" class="form-control" name="lama_studi"
                            placeholder="Contoh: 6 untuk D3, 8 untuk S1" required>
                        <small class="text-muted">Kelas akan otomatis berhenti digenerate jika melebihi angka ini.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="kaprodi_id">Kepala Program Studi (Kaprodi)</label>
                        <select name="kaprodi_id" id="kaprodi_id" class="form-select select2"
                            data-placeholder="Pilih Kaprodi">
                            <option value=""></option>
                            @foreach ($dosens as $dosen)
                                <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                            @endforeach
                            @error('kaprodi_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary me-3" id="saveBtn">Submit</button>
                    <button type="reset" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </form>
            </div>
        </div>
    </div>


@endsection
@section('page-script')
    <script type="module">
        // Fungsi inisialisasi
        const initSelect2 = () => {
            // Cek apakah jQuery dan Select2 sudah siap
            if (typeof $ !== 'undefined' && $.fn.select2) {

                // Targetkan select2 di dalam Offcanvas secara spesifik
                $('#offcanvasAddProdi .select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: true,
                        dropdownParent: $('#offcanvasAddProdi'), // <--- INI KUNCINYA
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
        const offcanvasElement = document.getElementById('offcanvasAddProdi');
        offcanvasElement.addEventListener('shown.bs.offcanvas', function() {
            initSelect2();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addNewProdiForm');
            const offcanvasEl = document.getElementById('offcanvasAddProdi');
            const offcanvasTitle = document.getElementById('offcanvasAddProdiLabel');
            const saveBtn = document.getElementById('saveBtn');
            const defaultAction = form.action;

            // Event delegation untuk tombol edit
            document.body.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-record');
                if (editBtn) {
                    const d = editBtn.dataset;

                    offcanvasTitle.textContent = 'Edit Program Studi';
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
                    document.getElementById('add-name-prodi').value = d.name;
                    document.getElementById('lamaStudi').value = d.lamaStudi;
                    document.getElementById('primary_campus').value = d.primaryCampus;
                    if (window.$) {
                        $('#kaprodi_id').val(d.kaprodiId).trigger('change');
                        $('#jenjang').val(d.jenjang).trigger('change');
                    } else {
                        document.getElementById('kaprodi_id').value = d.kaprodiId;
                        document.getElementById('jenjang').value = d.jenjang;
                    }
                }
            });

            // Reset form kembali ke Mode Create saat offcanvas ditutup
            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                offcanvasTitle.textContent = 'Tambah Program Studi';
                saveBtn.textContent = 'Submit';
                form.action = defaultAction;
                form.reset();

                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();

                if (window.$) {
                    $('#kaprodi_id').val('').trigger('change');
                    $('#jenjang').val('').trigger('change');
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
        });

        @if ($errors->any())
            const offcanvasError = new bootstrap.Offcanvas(offcanvasEl);
            offcanvasError.show();
        @endif
    </script>
@endsection

@extends('layouts/contentNavbarLayout')
@section('title', 'Kurikulum - PRIMA')

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
                    <h5 class="card-title fw-bold mb-0">Data Kurikulum</h5>
                    <small class="d-none d-md-block text-muted">Management Data Kurikulum Disini.</small>
                </div>
                <div class="col-6 text-end">
                    <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddKurikulum" id="btnCreate">
                        <span><i class="bx bx-plus me-2"></i>Kurikulum</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table class="table border-top" id="tableUser">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kurikulum</th>
                        <th class="text-center">Program Studi</th>
                        <th class="text-center">Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kurikulums as $key => $kurikulum)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>
                                <p class="mb-0"><a href="#"
                                        class="text-body text-truncate">{{ $kurikulum->name }}</a></p>
                                <small>Tahun : {{ $kurikulum->tanggal }} </small>
                            </td>
                            <td class="text-center">{{ $kurikulum->prodi->jenjang }} {{ $kurikulum->prodi->code }}</td>
                            <td class="text-center">
                                @if ($kurikulum->is_active == 1)
                                    <span class="badge bg-label-success me-1">Active</span>
                                @else
                                    <span class="badge bg-label-secondary me-1">Offline</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if (!empty($kurikulum->file_path))
                                        <a href="{{ asset('storage/' . $kurikulum->file_path) }}" target="_blank"
                                            class="text-body view-record me-2" data-bs-toggle="tooltip" data-bs-offset="0,6"
                                            data-bs-placement="bottom" data-bs-html="true"
                                            title="<i class='icon-base bx bx-doc icon-xs' ></i> <span>Lihat File</span>">
                                            <i class="bx bx-eye text-muted bx-sm"></i>
                                        </a>
                                        <a href="{{ asset('storage/' . $kurikulum->file_path) }}"
                                            download="{{ $kurikulum->name }}.pdf" target="_blank" class="text-body me-2"
                                            data-bs-toggle="tooltip" data-bs-offset="0,6" data-bs-placement="bottom"
                                            data-bs-html="true"
                                            title="<i class='icon-base bx bx-download icon-xs' ></i> <span>Download File</span>">
                                            <i class="bx bx-download text-success bx-sm"></i>
                                        </a>
                                    @else
                                        <a href="javascript:;" class="text-body view-record me-2"
                                            onclick="Swal.fire('Info', 'File belum diunggah.', 'info')"
                                            data-bs-toggle="tooltip" data-bs-offset="0,6" data-bs-placement="bottom"
                                            data-bs-html="true"
                                            title="<i class='icon-base bx bx-doc-fail icon-xs' ></i> <span>File Tidak Tersedia</span>">
                                            <i class="bx bx-eye-slash text-muted bx-sm"></i>
                                        </a>
                                    @endif

                                    <a href="javascript:;" class="text-body edit-record me-2" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasAddKurikulum" data-id="{{ $kurikulum->id }}"
                                        data-name="{{ $kurikulum->name }}" data-prodi-id="{{ $kurikulum->prodi_id }}"
                                        data-tanggal="{{ $kurikulum->tanggal }}"
                                        data-is-active="{{ $kurikulum->is_active }}"
                                        data-action="{{ route('master.kurikulum.update', $kurikulum->id) }}">
                                        <i class="bx bx-edit text-muted bx-sm"></i>
                                    </a>
                                    <form action="{{ route('master.kurikulum.destroy', $kurikulum->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <a href="javascript:;" class="text-body  delete-record" data-bs-toggle="tooltip"
                                            data-bs-offset="0,6" data-bs-placement="bottom" data-bs-html="true"
                                            title="Delete Kurikulum">
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

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddKurikulum"
            aria-labelledby="offcanvasAddKurikulumLabel">
            <div class="offcanvas-header border-bottom">
                <h5 id="offcanvasAddKurikulumLabel" class="offcanvas-title">Tambah Kurikulum</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body mx-0 grow-0 p-6 h-100">

                <form class="add-new-kurikulum pt-0" id="addNewKurikulumForm"
                    action="{{ route('master.kurikulum.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <select name="prodi_id" id="prodi_id" class="form-select select2"
                            data-placeholder="Pilih Prodi">
                            <option value=""></option>
                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}">
                                    {{ $prodi->jenjang }} {{ $prodi->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('prodi_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="add-name-kurikulum">Nama Kurikulum</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="add-name-kurikulum" placeholder="Contoh: A, B, C" name="name"
                            value="{{ old('name') }}" />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="Tanggal" class="form-label">Tahun</label>
                        <input class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" type="date"
                            value="{{ old('tanggal', date('Y')) }}" id="Tanggal">
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Masukan File Kurikulum</label>
                        <input class="form-control @error('file_sk') is-invalid @enderror" name="file_sk" type="file"
                            id="formFile">
                        @error('file_sk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="status" value="1">
                        <label class="form-check-label" for="status">Active</label>
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
                $('#offcanvasAddKurikulum .select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: true,
                        dropdownParent: $('#offcanvasAddKurikulum'), // <--- INI KUNCINYA
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
        const offcanvasElement = document.getElementById('offcanvasAddKurikulum');
        offcanvasElement.addEventListener('shown.bs.offcanvas', function() {
            initSelect2();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addNewKurikulumForm');
            const offcanvasEl = document.getElementById('offcanvasAddKurikulum');
            const offcanvasTitle = document.getElementById('offcanvasAddKurikulumLabel');
            const saveBtn = document.getElementById('saveBtn');
            const defaultAction = form.action;
            document.body.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-record');
                if (editBtn) {
                    const d = editBtn.dataset;


                    offcanvasTitle.textContent = 'Edit Kurikulum';
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


                    document.getElementById('add-name-kurikulum').value = d.name;
                    document.getElementById('Tanggal').value = d.tanggal;
                    document.getElementById('status').checked = d.isActive == '1';


                    if (window.$) {
                        $('#prodi_id').val(d.prodiId).trigger('change');
                    } else {
                        document.getElementById('prodi_id').value = d.prodiId;
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


                if (window.$) {
                    $('#prodi_id').val('').trigger('change');
                }
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

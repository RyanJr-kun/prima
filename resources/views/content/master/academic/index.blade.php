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
                    <h5 class="card-title fw-bold mb-0">Periode Akademik</h5>
                    <small class="d-none d-md-block">Management Data Periode Akademik Aktif disini.</small>
                </div>
                <div class="col-6 text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="bx bx-plus me-1"></i> Periode Baru
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Periode</th>
                        <th>Status</th>
                        <th>Switch</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $index => $period)
                        <tr class="{{ $period->is_active ? 'table-success' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $period->name }}</strong>
                            </td>
                            <td>
                                @if ($period->is_active)
                                    <span class="badge bg-success">
                                        <i class="bx bx-check-circle me-1"></i> AKTIF SEKARANG
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if (!$period->is_active)
                                        <form action="{{ route('periode-akademik.set-active', $period->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-primary activate-period-btn"
                                                title="Aktifkan Semester Ini">
                                                <i class="bx bx-power-off"></i> Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-success" disabled>Sedang Berjalan</button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:;" type="button" class="text-body edit-record me-2"
                                        data-id="{{ $period->id }}" data-name="{{ $period->name }}"
                                        data-url="{{ route('master.periode-akademik.update', $period->id) }}">
                                        <i class="bx bx-edit text-muted bx-sm"></i>
                                    </a>

                                    <form action="{{ route('master.periode-akademik.destroy', $period->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin hapus periode ini? Data kelas terkait mungkin akan error.')">
                                        @csrf
                                        @method('DELETE')
                                        <a href="javascript:;" type="submit" class="text-body  delete-record"
                                            data-bs-toggle="tooltip" data-bs-offset="0,6" data-bs-placement="bottom"
                                            data-bs-html="true" title="Delete Periode"
                                            {{ $period->is_active ? 'disabled' : '' }}>
                                            <i class="bx bx-trash text-muted bx-sm"></i>
                                        </a>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada data periode akademik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('master.periode-akademik.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Periode Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Periode / Tahun Ajaran</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: 2025/2026 Genap"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="formEdit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Periode</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Periode</label>
                            <input type="text" name="name" id="edit_name" class="form-control"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.btn-edit');

            editButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const name = this.getAttribute('data-name');
                    const url = this.getAttribute('data-url');

                    // Isi input di modal
                    document.getElementById('edit_name').value = name;

                    // Update action URL form
                    const form = document.getElementById('formEdit');
                    form.action = url;

                    // Buka Modal (Bootstrap 5)
                    const modal = new bootstrap.Modal(document.getElementById('editModal'));
                    modal.show();
                });
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

                const activateBtn = e.target.closest('.activate-period-btn');
                if (activateBtn) {
                    e.preventDefault();
                    const form = activateBtn.closest('form');

                    Swal.fire({
                        title: 'Aktifkan Periode?',
                        text: "Periode aktif saat ini akan dinonaktifkan.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Aktifkan!',
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

            @if ($errors->any())
                // Cek apakah error berasal dari Edit (PUT) atau Create (POST)
                @if (old('_method') === 'PUT')
                    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                @else
                    const createModal = new bootstrap.Modal(document.getElementById('createModal'));
                    createModal.show();
                @endif
            @endif
        });
    </script>
@endsection

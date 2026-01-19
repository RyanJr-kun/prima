@extends('layouts/contentNavbarLayout')
@section('title', 'Managemen Pengguna - prima')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('content')

{{-- Toast Notification --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
  @if (session('success'))  
  <div id="successToast" class="bs-toast bg-primary toast fade hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <i class="icon-base bx bx-bell icon-xs me-2"></i>
      <span class="fw-medium me-auto">Notifikasi</span>
      <small >Baru Saja!</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">{{ session('success') }}</div>
  </div>
  @endif 

  @if (session('error'))
  <div id="errorToast" class="bs-toast bg-danger toast fade hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <i class="icon-base bx bx-bell icon-xs me-2"></i>
      <span class="fw-medium me-auto">Notifikasi</span>
      <small >Baru Saja!</small>
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
            <h4 class="card-title mb-0">Pengguna</h4>
            <small>Management Data Penggunamu Disini.</small>
        </div>
        <div class="col-6 text-end">
            <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser" id="btnCreate">
                <span><i class="bx bx-plus me-2"></i>Penggguna</span>
            </button>
        </div>
    </div>
  </div>

  <div class="card-datatable table-responsive">
    <table class="table border-top" id="tableUser">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Pengguna</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data as $key => $user)
        <tr>
            <td>{{ ++$key }}</td>
            <td>
                <div class="d-flex justify-content-start align-items-center user-name">
                    <div class="avatar-wrapper">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($user->name, 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <a href="#" class="text-body text-truncate"><span class="fw-medium">{{ $user->name }}</span></a>
                        <small class="text-muted">{{ $user->email }}</small>
                    </div>
                </div>
            </td>
            <td>
                 @php
                    $roleColors = [
                        'admin' => 'danger',
                        'baak' => 'secondary',
                        'dosen' => 'success',
                        'kaprodi' => 'primary',
                        'wadir1' => 'warning',
                        'wadir2' => 'warning',
                        'direktur' => 'dark',
                    ];
                @endphp
                @forelse($user->getRoleNames() as $role)
                    @php $colorClass = $roleColors[strtolower($role)] ?? 'primary'; @endphp
                    <span class="badge bg-label-{{ $colorClass }} me-1">{{ $role }}</span>
                @empty
                    <span class="text-muted small">Tanpa Role</span>
                @endforelse
            </td>
            <td>
                @if ($user->status == 1)
                  <span class="badge bg-label-success me-1">Active</span>
                @else
                  <span class="badge bg-label-secondary me-1">Offline</span>
                @endif
            </td>
            <td>
                <div class="d-flex align-items-center">
                    @if(!empty($user->signature_path))
                        <a  href="{{ asset('storage/' . $user->signature_path) }}" 
                            target="_blank" class="text-body view-record me-2" 
                            data-bs-toggle="tooltip"
                            data-bs-offset="0,6"
                            data-bs-placement="bottom"
                            data-bs-html="true"
                            title="<i class='icon-base bx bx-doc icon-xs' ></i> <span>Lihat TTD</span>">
                            <i class="bx bx-eye text-muted bx-sm"></i>
                        </a>
                        <a href="{{ asset('storage/' . $user->signature_path) }}" 
                        download="{{ $user->name }}_signature.png" 
                        target="_blank" 
                        class="text-body me-2"
                        data-bs-toggle="tooltip"
                        data-bs-offset="0,6"
                        data-bs-placement="bottom"
                        data-bs-html="true"
                        title="<i class='icon-base bx bx-download icon-xs' ></i> <span>Download TTD</span>">
                            <i class="bx bx-download text-success bx-sm"></i>
                    </a>
                    @else
                        <a  href="javascript:;"
                            class="text-body view-record me-2" 
                            onclick="Swal.fire('Info', 'File belum diunggah.', 'info')"
                            data-bs-toggle="tooltip"
                            data-bs-offset="0,6"
                            data-bs-placement="bottom"
                            data-bs-html="true"
                            title="<i class='icon-base bx bx-doc-fail icon-xs' ></i> <span>TTD Tidak Tersedia</span>">
                            <i class="bx bx-eye-slash text-muted bx-sm"></i>
                        </a>
                    @endif
                    <a href="javascript:;" class="text-body edit-record me-2"
                       data-bs-toggle="offcanvas"
                       data-bs-target="#offcanvasAddUser"
                       data-id="{{ $user->id }}"
                       data-name="{{ $user->name }}"
                       data-username="{{ $user->username }}"
                       data-email="{{ $user->email }}"
                       data-nidn="{{ $user->nidn }}"
                       data-roles='@json($user->getRoleNames())'
                       data-status="{{ $user->status }}"
                       data-action="{{ route('user.update', $user->id) }}">
                        <i class="bx bx-edit text-muted bx-sm"></i>
                    </a>

                    <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <a href="javascript:;" class="text-body delete-record"
                            data-bs-toggle="tooltip"
                            data-bs-offset="0,6"
                            data-bs-placement="bottom"
                            data-bs-html="true"
                            title="Delete User">
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

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 grow-0 p-6 h-100">

      <form class="add-new-user pt-0" id="addNewUserForm" action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div id="methodInput"></div>
        <input type="hidden" id="user_id" name="user_id">

        <div class="mb-3">
          <label class="form-label" for="add-user-fullname">Full Name</label>
          <input type="text" class="form-control @error('name') is-invalid @enderror" id="add-user-fullname" placeholder="John Doe" name="name" value="{{ old('name') }}" />
          @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label" for="add-user-username">Username</label>
          <input type="text" class="form-control @error('username') is-invalid @enderror" id="add-user-username" placeholder="johndoe" name="username" value="{{ old('username') }}" />
          @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label" for="add-user-nidn">NIDN</label>
          <input type="text" class="form-control @error('nidn') is-invalid @enderror" id="add-user-nidn" placeholder="617892733" name="nidn" value="{{ old('nidn') }}" />
          @error('nidn') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label" for="add-user-email">Email</label>
          <input type="text" class="form-control @error('email') is-invalid @enderror" id="add-user-email" placeholder="john.doe@example.com" name="email" value="{{ old('email') }}" />
          @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label" for="user-role">User Role</label>
          <select id="user-role" class="form-select select2 @error('userRole') is-invalid @enderror" name="userRole[]" multiple>
            @foreach($roles as $role)
                <option value="{{ $role }}" {{ (collect(old('userRole'))->contains($role)) ? 'selected' : '' }}>{{ $role }}</option>
            @endforeach
          </select>
          @error('userRole') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label" for="add-user-password">Password</label>
          <input type="password" class="form-control @error('password') is-invalid @enderror" id="add-user-password" name="password" placeholder="********" />
          <small class="text-muted" id="password-help" style="display:none">Biarkan kosong jika tidak ingin mengganti password.</small>
          @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="confirm-password">Confirm Password</label>
            <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="********" />
        </div>

        <div class="mb-3">
            <label class="form-label" for="signature_path">Tanda Tangan (PNG)</label>
            <input type="file" class="form-control @error('signature_path') is-invalid @enderror" id="signature_path" name="signature_path" accept="image/png" />
            @error('signature_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-check form-switch mb-3">
            <input type="hidden" name="status" value="0">
            <input class="form-check-input" type="checkbox" name="status" id="status" value="1">
            <label class="form-check-label" for="status">Active</label>
        </div>

        <button type="submit" class="btn btn-primary me-3" id="saveBtn">Submit</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
      </form>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('addNewUserForm');
        const offcanvasEl = document.getElementById('offcanvasAddUser');
        const offcanvasTitle = document.getElementById('offcanvasAddUserLabel');
        const saveBtn = document.getElementById('saveBtn');
        const defaultAction = form.action;

        document.body.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.edit-record');
            if (editBtn) {
                const d = editBtn.dataset;

                // Ubah tampilan offcanvas menjadi Mode Edit
                offcanvasTitle.textContent = 'Edit Pengguna';
                saveBtn.textContent = 'Simpan Perubahan';
                form.action = d.action; // Menggunakan route update dari data-action

                // Tambahkan input hidden method PUT untuk Laravel
                let methodInput = form.querySelector('input[name="_method"]');
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    form.appendChild(methodInput);
                }

                // Isi value form berdasarkan data attribute
                document.getElementById('user_id').value = d.id;
                document.getElementById('add-user-fullname').value = d.name;
                document.getElementById('add-user-username').value = d.username;
                document.getElementById('add-user-email').value = d.email;
                document.getElementById('add-user-nidn').value = d.nidn;
                document.getElementById('status').checked = d.status == '1';
                document.getElementById('password-help').style.display = 'block';
                
                // Set nilai Select2 (menggunakan jQuery karena class 'select2' biasanya memerlukannya)
                if (window.$) {
                    $('#user-role').val(JSON.parse(d.roles)).trigger('change');
                } else {
                    document.getElementById('user-role').value = JSON.parse(d.roles);
                }
            }
        });

        // Reset form kembali ke Mode Create saat offcanvas ditutup
        offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
            offcanvasTitle.textContent = 'Tambah Kurikulum';
            saveBtn.textContent = 'Submit';
            form.action = defaultAction;
            form.reset();
            
            // Hapus method PUT agar kembali menjadi POST untuk create
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.remove();

            // Reset Select2
            if (window.$) {
                $('#user-role').val(null).trigger('change');
                document.getElementById('password-help').style.display = 'none';
            } else {
                document.getElementById('user-role').value = null;
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
                        title:'my-0 py-0',
                        htmlContainer:'py-0 my-0',
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
            new bootstrap.Toast(successToast, { delay: 3000 }).show();
        }
        const errorToast = document.getElementById('errorToast');
        if (errorToast) {
            new bootstrap.Toast(errorToast, { delay: 3000 }).show();
        }
    });
</script>
@endsection

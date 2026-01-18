@extends('layouts/contentNavbarLayout')

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('title', 'Managemen Pengguna - prima')
@section('content')

{{-- TOAST NOTIFICATION --}}
@if (session('success'))
<div class="toast-container top-0 end-0 p-3" style="z-index: 1050;">
  <div id="successToast" class="toast align-items-center bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        {{ session('success') }}
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
@endif

<div class="card">

  <div class="card-header border-bottom">
    <div class="row">

        <div class="col-6">
            <h4 class="card-title mb-0">Managemen Pengguna</h4>
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
          <th>#</th>
          <th>User</th>
          <th>Role</th>
          <th>Status</th> <th>Actions</th>
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
                        'mahasiswa' => 'success',
                        'dosen' => 'primary',
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
            <td><span class="badge bg-label-success">Active</span></td>
            <td>
                <div class="d-flex align-items-center">
                    {{-- Perhatikan penambahan data-roles dengan @json --}}
                    <a href="javascript:;" class="text-body edit-record me-2"
                       data-bs-toggle="offcanvas"
                       data-bs-target="#offcanvasAddUser"
                       data-id="{{ $user->id }}"
                       data-name="{{ $user->name }}"
                       data-username="{{ $user->username }}"
                       data-email="{{ $user->email }}"
                       data-roles='@json($user->getRoleNames())'
                       data-action="{{ route('user.update', $user->id) }}">
                        <i class="bx bx-edit text-muted bx-sm"></i>
                    </a>

                    <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
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

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 grow-0 p-6 h-100">

      <form class="add-new-user pt-0" id="addNewUserForm" action="{{ route('user.store') }}" method="POST">
        @csrf
        <div id="methodInput"></div>
        <input type="hidden" id="user_id" name="user_id">

        <div class="mb-6">
          <label class="form-label" for="add-user-fullname">Full Name</label>
          <input type="text" class="form-control @error('name') is-invalid @enderror" id="add-user-fullname" placeholder="John Doe" name="name" value="{{ old('name') }}" />
          @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-user-username">Username</label>
          <input type="text" class="form-control @error('username') is-invalid @enderror" id="add-user-username" placeholder="johndoe" name="username" value="{{ old('username') }}" />
          @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-user-email">Email</label>
          <input type="text" class="form-control @error('email') is-invalid @enderror" id="add-user-email" placeholder="john.doe@example.com" name="email" value="{{ old('email') }}" />
          @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-6">
          <label class="form-label" for="user-role">User Role</label>
          {{-- 3. Select dengan atribut Multiple --}}
          <select id="user-role" class="form-select select2 @error('userRole') is-invalid @enderror" name="userRole[]" multiple>
            @foreach($roles as $role)
                <option value="{{ $role }}" {{ (collect(old('userRole'))->contains($role)) ? 'selected' : '' }}>{{ $role }}</option>
            @endforeach
          </select>
          @error('userRole') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-user-password">Password</label>
          <input type="password" class="form-control @error('password') is-invalid @enderror" id="add-user-password" name="password" placeholder="********" />
          <small class="text-muted" id="password-help" style="display:none">Biarkan kosong jika tidak ingin mengganti password.</small>
          @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-6">
            <label class="form-label" for="confirm-password">Confirm Password</label>
            <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="********" />
        </div>

        <button type="submit" class="btn btn-primary me-3" id="saveBtn">Submit</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (window.jQuery) {
        $('#user-role').select2({
            placeholder: "Pilih Role",
            allowClear: true,
            dropdownParent: $('#offcanvasAddUser'), // jQuery selector untuk parent
            theme: 'bootstrap-5'
        });
    }

    const btnCreate = document.getElementById('btnCreate');
    if (btnCreate) {
        btnCreate.addEventListener('click', function() {
            document.getElementById('offcanvasAddUserLabel').textContent = "Add User";
            document.getElementById('saveBtn').textContent = "Submit";
            document.getElementById('user_id').value = '';
            document.getElementById('password-help').style.display = 'none';

            let form = document.getElementById('addNewUserForm');
            form.reset();
            form.setAttribute('action', "{{ route('user.store') }}");
            document.getElementById('methodInput').innerHTML = '';

            if (window.jQuery) {
                $('#user-role').val(null).trigger('change');
            }

            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        });
    }

    document.body.addEventListener('click', function(e) {
        const target = e.target.closest('.edit-record');

        if (target) {
            let id = target.getAttribute('data-id');
            let name = target.getAttribute('data-name');
            let username = target.getAttribute('data-username');
            let email = target.getAttribute('data-email');
            let rolesData = JSON.parse(target.getAttribute('data-roles'));
            let actionUrl = target.getAttribute('data-action');

            document.getElementById('offcanvasAddUserLabel').textContent = "Edit User";
            document.getElementById('saveBtn').textContent = "Update";
            document.getElementById('password-help').style.display = 'block';

            document.getElementById('user_id').value = id;
            document.getElementById('add-user-fullname').value = name;
            document.getElementById('add-user-username').value = username;
            document.getElementById('add-user-email').value = email;

            let form = document.getElementById('addNewUserForm');
            form.setAttribute('action', actionUrl);
            document.getElementById('methodInput').innerHTML = '<input type="hidden" name="_method" value="PUT">';

            if (window.jQuery) {
                $('#user-role').val(rolesData).trigger('change');
            }

            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }
    });

    document.body.addEventListener('click', function(e) {
        const target = e.target.closest('.delete-record');

        if (target) {
            e.preventDefault();
            const form = target.closest('form');

            if (form) {
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
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        }
    });

    @if ($errors->any())
        const offcanvasEl = document.getElementById('offcanvasAddUser');
        if (offcanvasEl) {
            const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
            offcanvas.show();
        }
    @endif

    const toastEl = document.getElementById('successToast');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl, {
            delay: 5000
        });
        toast.show();
    }
});
</script>
@endsection

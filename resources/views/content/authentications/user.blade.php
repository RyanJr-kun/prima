@extends('layouts/contentNavbarLayout')

@section('title', 'User Management - SiPadu')

@section('vendor-script')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite('resources/assets/vendor/libs/bootstrap/bootstrap.js')
@endsection

@section('page-script')
@vite('resources/assets/js/app-user-list.js')
@endsection

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row g-6 mb-6">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Session</span>
            <div class="d-flex align-items-end mt-2">
              <h3 class="mb-0 me-2">21,459</h3>
              <small class="text-success">(+29%)</small>
            </div>
            <small>Total Users</small>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            <i class="bx bx-user bx-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  </div>

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Search Filter</h5>
    <div class="d-flex justify-content-between align-items-center row pt-4 gap-4 gap-md-0">
      <div class="col-md-4 user_role"></div>
      <div class="col-md-4 user_plan"></div>
      <div class="col-md-4 user_status"></div>
    </div>
  </div>

  <div class="card-datatable table-responsive">
    <div class="d-flex justify-content-between align-items-center m-3">
        <h5 class="card-title mb-0">List Users</h5>
        <button class="btn btn-secondary add-new btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser" id="btnCreate">
            <span><i class="bx bx-plus bx-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Add New User</span></span>
        </button>
    </div>

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
        <tr id="row_{{ $user->id }}">
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
                @foreach($user->getRoleNames() as $role)
                    <span class="text-truncate d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-label-warning w-px-30 h-px-30 me-2"><i class="bx bx-user bx-xs"></i></span>
                        {{ $role }}
                    </span>
                @endforeach
            </td>
            <td><span class="badge bg-label-success">Active</span></td>
            <td>
                <div class="d-flex align-items-center">
                    <a href="javascript:;" class="text-body edit-record me-2"
                       data-bs-toggle="offcanvas"
                       data-bs-target="#offcanvasAddUser"
                       data-id="{{ $user->id }}"
                       data-name="{{ $user->name }}"
                       data-username="{{ $user->username }}"
                       data-email="{{ $user->email }}"
                       data-role="{{ $user->roles->first()->name ?? '' }}">
                        <i class="bx bx-edit text-muted bx-sm"></i>
                    </a>
                    <a href="javascript:;" class="text-body delete-record" data-id="{{ $user->id }}">
                        <i class="bx bx-trash text-muted bx-sm"></i>
                    </a>
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

      <form class="add-new-user pt-0" id="addNewUserForm" onsubmit="return false">
        <input type="hidden" id="user_id" name="user_id"> <div class="mb-6">
          <label class="form-label" for="add-user-fullname">Full Name</label>
          <input type="text" class="form-control" id="add-user-fullname" placeholder="John Doe" name="name" aria-label="John Doe" />
          <div class="invalid-feedback error-text name_error"></div>
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-user-username">Username</label>
          <input type="text" class="form-control" id="add-user-username" placeholder="johndoe" name="username" aria-label="johndoe" />
          <div class="invalid-feedback error-text username_error"></div>
        </div>

        <div class="mb-6">
          <label class="form-label" for="add-user-email">Email</label>
          <input type="text" class="form-control" id="add-user-email" placeholder="john.doe@example.com" name="email" aria-label="john.doe@example.com" />
          <div class="invalid-feedback error-text email_error"></div>
        </div>

        <div class="mb-6">
          <label class="form-label" for="user-role">User Role</label>
          <select id="user-role" class="form-select" name="userRole">
            <option value="">Select Role</option>
            @foreach($roles as $role)
                <option value="{{ $role }}">{{ $role }}</option>
            @endforeach
          </select>
          <div class="invalid-feedback error-text userRole_error"></div>
        </div>

        <div class="mb-6">
            <label class="form-label" for="add-user-password">Password</label>
            <input type="password" class="form-control" id="add-user-password" name="password" placeholder="********" />
            <small class="text-muted" id="password-help" style="display:none">Biarkan kosong jika tidak ingin mengganti password.</small>
            <div class="invalid-feedback error-text password_error"></div>
        </div>

        <div class="mb-6">
            <label class="form-label" for="confirm-password">Confirm Password</label>
            <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="********" />
        </div>

        <button type="submit" class="btn btn-primary me-3 data-submit" id="saveBtn">Submit</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {

    // Setup CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 1. Reset Form saat tombol Tambah diklik
    $('#btnCreate').click(function() {
        $('#offcanvasAddUserLabel').text("Add User");
        $('#saveBtn').text("Submit");
        $('#user_id').val('');
        $('#addNewUserForm')[0].reset();
        $('.invalid-feedback').text('');
        $('input, select').removeClass('is-invalid');
        $('#password-help').hide(); // Sembunyikan pesan help password
    });

    // 2. Isi Form saat tombol Edit diklik
    $('body').on('click', '.edit-record', function() {
        var id = $(this).data('id');

        $('#offcanvasAddUserLabel').text("Edit User");
        $('#saveBtn').text("Update");

        // Isi input
        $('#user_id').val(id);
        $('#add-user-fullname').val($(this).data('name'));
        $('#add-user-username').val($(this).data('username'));
        $('#add-user-email').val($(this).data('email'));
        $('#user-role').val($(this).data('role'));

        // Bersihkan password
        $('#add-user-password').val('');
        $('#confirm-password').val('');
        $('#password-help').show(); // Tampilkan pesan help

        // Hapus error lama
        $('.invalid-feedback').text('');
        $('input, select').removeClass('is-invalid');
    });

    // 3. Simpan Data (AJAX)
    $('#addNewUserForm').submit(function(e) {
        e.preventDefault();

        var id = $('#user_id').val();
        var url = id ? "/user/" + id : "{{ route('user.store') }}";
        var type = id ? "PUT" : "POST";

        $('#saveBtn').prop('disabled', true).text('Processing...');

        $.ajax({
            url: url,
            type: type,
            data: $(this).serialize(),
            success: function(response) {
                $('#saveBtn').prop('disabled', false).text('Submit');

                // Tutup Offcanvas
                var offcanvasEl = document.getElementById('offcanvasAddUser');
                var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                offcanvas.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.success,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(response) {
                $('#saveBtn').prop('disabled', false).text('Submit');

                if(response.status === 422) {
                    var errors = response.responseJSON.errors;
                    $('input, select').removeClass('is-invalid');
                    $('.invalid-feedback').text('');

                    $.each(errors, function(key, val) {
                        // Mapping nama field controller ke ID input HTML
                        var inputSelector = '';
                        if(key == 'name') inputSelector = '#add-user-fullname';
                        else if(key == 'username') inputSelector = '#add-user-username';
                        else if(key == 'email') inputSelector = '#add-user-email';
                        else if(key == 'userRole') inputSelector = '#user-role';
                        else if(key == 'password') inputSelector = '#add-user-password';

                        if(inputSelector) {
                            $(inputSelector).addClass('is-invalid');
                            $(inputSelector).siblings('.invalid-feedback').text(val[0]);
                        }
                    });
                } else {
                    Swal.fire('Error', 'Something went wrong!', 'error');
                }
            }
        });
    });

    // 4. Delete Data
    $('body').on('click', '.delete-record', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            customClass: {
              confirmButton: 'btn btn-primary me-3',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/user/" + id,
                    type: "DELETE",
                    success: function(response) {
                        $('#row_' + id).fadeOut();
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.success,
                            customClass: { confirmButton: 'btn btn-success' }
                        });
                    }
                });
            }
        });
    });

});
</script>
@endsection

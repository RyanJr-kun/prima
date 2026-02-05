@extends('layouts/contentNavbarLayout')

@section('title', 'Pengaturan Akun - PRIMA')

@section('page-script')
    @vite(['resources/assets/js/pages-account-settings-account.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            {{-- Alert Sukses --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Alert Error --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="nav-align-top">
                <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-md-0 gap-2">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);"><i
                                class="icon-base bx bx-user icon-sm me-1_5"></i> Akun</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('notifikasi') }}"><i
                                class="icon-base bx bx-bell icon-sm me-1_5"></i> Notifikasi</a>
                    </li>
                </ul>
            </div>

            <div class="card mb-6">
                <form id="formAccountSettings" method="POST" action="{{ route('pengaturan.update') }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">
                            {{-- Logic Avatar: Cek Database / File Exists --}}
                            @php

                                $hasPhoto =
                                    $user->profile_photo_path &&
                                    Storage::disk('public')->exists($user->profile_photo_path);

                                // 2. Jika ada, pakai foto itu. Jika tidak, pakai UI Avatars (Inisial Nama)
                                $avatarUrl = $hasPhoto
                                    ? asset('storage/' . $user->profile_photo_path)
                                    : 'https://ui-avatars.com/api/?name=' .
                                        urlencode($user->name) .
                                        '&background=random&color=fff&bold=true&size=128';
                            @endphp

                            <img src="{{ $avatarUrl }}" alt="user-avatar"
                                class="d-block w-px-100 h-px-100 rounded object-fit-cover" id="uploadedAvatar" />

                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Upload foto baru</span>
                                    <i class="icon-base bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" name="upload" class="account-file-input" hidden
                                        accept="image/png, image/jpeg" />
                                </label>
                                @if ($hasPhoto)
                                    <button type="button" class="btn btn-outline-secondary account-image-reset mb-4"
                                        id="resetAvatarBtn">
                                        <i class="icon-base bx bx-reset d-block d-sm-none"></i>
                                        <span class="d-none d-sm-block">Hapus Foto</span>
                                    </button>
                                @endif

                                <div>Allowed JPG, GIF or PNG. Max size of 1MB</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-4">
                        <div class="row g-6">
                            {{-- Username --}}
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input class="form-control" type="text" id="username" name="username"
                                    value="{{ old('username', $user->username) }}" autofocus />
                            </div>

                            {{-- Nama Lengkap --}}
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input class="form-control" type="text" name="name" id="name"
                                    value="{{ old('name', $user->name) }}" />
                            </div>

                            {{-- E-mail --}}
                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input class="form-control" type="email" id="email" name="email"
                                    value="{{ old('email', $user->email) }}" />
                            </div>

                            {{-- NIDN --}}
                            <div class="col-md-6">
                                <label for="nidn" class="form-label">NIDN (Nomor Induk Dosen Nasional)</label>
                                <input type="text" class="form-control" id="nidn" name="nidn"
                                    value="{{ old('nidn', $user->nidn) }}" placeholder="Isi jika ada" />
                            </div>

                            {{-- LOGIC TTD KHUSUS ROLE --}}
                            @hasanyrole('direktur|wadir1|wadir2|wadir3|kaprodi')
                                <div class="col-md-12">
                                    <div class="divider text-start">
                                        <div class="divider-text text-primary fw-bold"> <i class="bx bx-pen"></i> Tanda Tangan
                                            Digital</div>
                                    </div>
                                    <div class="alert alert-warning mb-2">
                                        <small>Upload gambar tanda tangan (Format PNG Transparan disarankan). Akan digunakan
                                            untuk dokumen resmi.</small>
                                    </div>

                                    <div class="d-flex align-items-center gap-3">
                                        @if ($user->signature_path)
                                            <div class="border p-2 rounded bg-light">
                                                <img src="{{ asset('storage/' . $user->signature_path) }}" height="60"
                                                    alt="TTD Saat ini">
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <label for="signature" class="form-label">Upload TTD Baru</label>
                                            <input class="form-control" type="file" id="signature" name="signature"
                                                accept="image/png, image/jpeg">
                                        </div>
                                    </div>
                                </div>
                            @endhasanyrole

                            {{-- Ganti Password Section --}}
                            <div class="col-md-12 mt-4">
                                <div class="divider text-start">
                                    <div class="divider-text">Ganti Password (Opsional)</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                    placeholder="Kosongkan jika tidak ingin mengganti" />
                            </div>
                            <div class="col-md-6">
                                <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="new_password_confirmation"
                                    name="new_password_confirmation" placeholder="Ulangi password baru" />
                            </div>

                        </div>
                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary me-3">Simpan Perubahan</button>
                            <button type="reset" class="btn btn-outline-secondary">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resetBtn = document.getElementById('resetAvatarBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (confirm('Apakah Anda yakin ingin menghapus foto profil?')) {
                        // Create a temporary form to submit DELETE request
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = "{{ route('pengaturan.deleteAvatar') }}";

                        let csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = "{{ csrf_token() }}";
                        form.appendChild(csrf);

                        let method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'DELETE';
                        form.appendChild(method);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        });
    </script>
@endsection

@extends('layouts/blankLayout')

@section('title', 'Login - Sistem Persiapan Dokumen')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
    <div class="container-fluid">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="card px-sm-6 px-0">
                <div class="card-body">
                    <div class="app-brand justify-content-center">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2 mb-3">
                            <span class="app-brand-logo demo">@include('_partials.macros')</span>
                            <span class="app-brand-text demo text-heading fw-bold">prima</span>
                        </a>
                    </div>
                    <p class="mb-4">Silakan login menggunakan akun SI-AKAD Anda.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="formAuthentication" class="mb-6" action="{{ route('login.post') }}" method="POST">

                        @csrf

                        <div class="mb-6">
                            <label for="username" class="form-label">Username / NIM / NIDN</label>

                            <input type="text" class="form-control" id="username" name="username"
                                placeholder="Masukkan NIM atau NIDN" value="{{ old('username') }}" autocomplete="username"
                                autofocus required />
                        </div>

                        <div class="mb-6 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">

                                <input type="password" id="password" class="form-control" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" autocomplete="current-password" required />
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
                        </div>
                    </form>

                    <p class="text-center">
                        <span>Belum punya akun?</span>
                        <a href="javascript:void(0);">
                            <span>Hubungi BAAK / IT</span>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

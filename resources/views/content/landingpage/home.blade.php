@extends('layouts/commonMaster')
@section('title', 'PRIMA - Politeknik Indonusa Surakarta')

@section('vendor-style')
    {{-- AOS Animation CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    {{-- Font: Inter/Plus Jakarta Sans sering digunakan untuk kesan Pro --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            color: #2d3748;
        }

        /* Navbar Enhancement */
        .landing-navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.4s ease;
        }

        /* Hero Dark Theme & Grid */
        .hero-section {
            background-color: #0b0f19;
            /* Warna dasar gelap */
            background-image:
                /* Efek Grid */
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                /* Cahaya dari pojok kanan bawah */
                radial-gradient(circle at 80% 80%, rgba(105, 108, 255, 0.15) 0%, transparent 50%);
            background-size: 40px 40px, 40px 40px, 100% 100%;
            min-height: 90vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Animasi cahaya lembut yang berdenyut */
        .hero-section::after {
            content: "";
            position: absolute;
            bottom: -150px;
            right: -150px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(105, 108, 255, 0.2) 0%, transparent 70%);
            filter: blur(80px);
            z-index: 0;
        }

        /* Penyesuaian Teks agar terbaca di background gelap */
        .hero-section h1 {
            color: #ffffff !important;
        }

        .hero-section .lead {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        /* Badge yang lebih kontras */
        .stats-badge {
            background: rgba(105, 108, 255, 0.15);
            color: #8183ff;
            border: 1px solid rgba(105, 108, 255, 0.3);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
        }

        /* Tombol Glassmorphism untuk "Pelajari Fitur" */
        .btn-outline-light-custom {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }

        .btn-outline-light-custom:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Typography & Buttons */
        .display-4 {
            letter-spacing: -1.5px;
            font-weight: 800;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        /* Custom Outline Button dengan Fill Animation */
        .btn-outline-primary {
            color: #667eea;
            border-color: #667eea;
            background: transparent;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            color: #fff;
            border-color: transparent;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-outline-primary::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: var(--primary-gradient);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .btn-outline-primary:hover::before {
            width: 100%;
        }

        /* Feature Cards Modernization */
        .feature-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 24px;
            background: #fff;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08) !important;
            border-color: #667eea;
        }

        .icon-box {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            margin-bottom: 2rem;
            transition: all 0.3s;
        }

        .feature-card:hover .icon-box {
            transform: scale(1.1) rotate(5deg);
        }

        /* Navbar Link Hover Effect */
        .navbar-nav .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 5px;
            left: 50%;
            background: var(--primary-gradient);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after {
            width: 80%;
        }

        .navbar-nav .nav-link:hover {
            color: #667eea !important;
        }

        /* Stats Section Custom */
        .stats-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        footer {
            background: #0f172a !important;
        }
    </style>
@endsection

@section('layoutContent')

    <nav class="navbar navbar-expand-lg landing-navbar sticky-top px-4 py-3">
        <div class="container-xxl">
            <a class="navbar-brand fw-bolder text-primary fs-3 d-flex align-items-center" href="#">
                <div class="bg-primary text-white p-2 rounded-3 me-2 d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;">
                    <i class='bx bxs-zap fs-4'></i>
                </div>
                PRIMA
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav gap-lg-4 fw-semibold">
                    <li class="nav-item"><a class="nav-link" href="#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Fitur</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('public.jadwal') }}">Jadwal Publik</a></li>
                </ul>
            </div>
            <div class="d-none d-lg-block">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4 py-2">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 me-2 ">Masuk</a>
                @endauth
            </div>
        </div>
    </nav>

    <section id="home" class="hero-section">
        <div class="container-xxl">
            <div class="row align-items-center">
                <div class="col-lg-6 z-1" data-aos="fade-up">
                    <span class="stats-badge mb-4 d-inline-block fw-bold">
                        <i class='bx bxs-graduation me-2'></i>SISTEM AKADEMIK V2.0
                    </span>
                    <h1 class="display-4 mb-4 fw-extrabold">
                        Manajemen Akademik <br>
                        <span
                            style="background: linear-gradient(135deg, #fff 0%, #696cff 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            Jauh Lebih Modern.
                        </span>
                    </h1>
                    <p class="lead mb-5 fs-5" style="max-width: 90%;">
                        Otomatisasi penjadwalan, monitoring beban kerja dosen, dan distribusi mata kuliah dalam satu
                        platform yang elegan dan responsif.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg">
                            Mulai Sekarang <i class='bx bx-right-arrow-alt ms-2'></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 text-center mt-5 mt-lg-0" data-aos="zoom-in" data-aos-delay="200">
                    <div class="position-relative">
                        <div class="position-absolute bg-dark p-3 rounded-4 shadow-lg d-none d-md-block border border-secondary"
                            style="top: 20%; left: -5%; z-index: 2; width: 180px; backdrop-filter: blur(10px); background: rgba(20, 25, 35, 0.8) !important;"
                            data-aos="fade-right" data-aos-delay="500">
                            <div class="d-flex align-items-center text-start">
                                <div class="bg-success py-1 px-2 rounded-circle me-3">
                                    <i class='bx bx-check text-white'></i>
                                </div>
                                <div>
                                    <small class="text-white d-block">Status</small>
                                    <b class="small text-white">124 Jadwal</b>
                                </div>
                            </div>
                        </div>
                        <dotlottie-player src="https://assets10.lottiefiles.com/packages/lf20_w51pcehl.json"
                            background="transparent" speed="1"
                            style="width: 100%; height: auto; filter: drop-shadow(0 20px 50px rgba(105, 108, 255, 0.3));"
                            class="position-relative z-1" loop autoplay>
                        </dotlottie-player>

                        <div class="position-absolute top-50 start-50 translate-middle"
                            style="width: 120%; height: 120%; background: radial-gradient(circle, rgba(105, 108, 255, 0.1) 0%, transparent 60%); z-index: 0;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-5">
        <div class="container-xxl py-5">
            <div class="row mb-5 justify-content-center text-center" data-aos="fade-up">
                <div class="col-lg-7">
                    <h2 class="display-6 fw-bold mb-3">Solusi Cerdas untuk Politeknik</h2>
                    <p class="text-muted fs-5">Dirancang khusus untuk mempermudah birokrasi akademik dengan teknologi
                        otomatisasi terbaru.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card feature-card h-100 p-4 border-0">
                        <div class="card-body">
                            <div class="icon-box bg-label-primary shadow-sm">
                                <i class='bx bx-calendar-check fs-1 text-primary'></i>
                            </div>
                            <h4 class="fw-bold mb-3">Smart Scheduling</h4>
                            <p class="text-muted lh-lg">
                                Algoritma cerdas yang meminimalkan bentrok waktu dan ruang secara otomatis. Hemat waktu
                                koordinasi hingga 80%.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card feature-card h-100 p-4 border-0">
                        <div class="card-body">
                            <div class="icon-box bg-label-success shadow-sm">
                                <i class='bx bx-line-chart fs-1 text-success'></i>
                            </div>
                            <h4 class="fw-bold mb-3">Monitoring BKD</h4>
                            <p class="text-muted lh-lg">
                                Pantau beban kerja dosen secara real-time. Laporan otomatis untuk kebutuhan akreditasi dan
                                evaluasi semester.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card feature-card h-100 p-4 border-0">
                        <div class="card-body">
                            <div class="icon-box bg-label-info shadow-sm">
                                <i class='bx bx-shield-quarter fs-1 text-info'></i>
                            </div>
                            <h4 class="fw-bold mb-3">Validasi Data</h4>
                            <p class="text-muted lh-lg">
                                Sinkronisasi data kurikulum yang menjamin integritas informasi antara dosen, prodi, dan
                                administrasi pusat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="text-white py-5">
        <div class="container-xxl">
            <div class="row gy-4 align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-3">
                        <div class="bg-primary text-white p-2 rounded-3 me-2"><i class='bx bxs-zap fs-4'></i></div>
                        <span class="fw-bold fs-4">PRIMA</span>
                    </div>
                    <p class="text-white-50">Politeknik Indonusa Surakarta<br>Mewujudkan pendidikan vokasi yang modern dan
                        terukur.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="mb-3">
                        <a href="#" class="text-white me-3 fs-4"><i class='bx bxl-facebook-circle'></i></a>
                        <a href="#" class="text-white me-3 fs-4"><i class='bx bxl-instagram'></i></a>
                        <a href="#" class="text-white fs-4"><i class='bx bxl-linkedin-square'></i></a>
                    </div>
                    <p class="small text-white-50 mb-0">
                        &copy; {{ date('Y') }} Politeknik Indonusa Surakarta. Developed with ❤️
                    </p>
                </div>
            </div>
        </div>
    </footer>
@endsection

@section('page-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 120,
            easing: 'ease-out-back'
        });

        // Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.landing-navbar').style.padding = "10px 20px";
                document.querySelector('.landing-navbar').style.boxShadow = "0 10px 30px rgba(0,0,0,0.1)";
            } else {
                document.querySelector('.landing-navbar').style.padding = "20px 20px";
                document.querySelector('.landing-navbar').style.boxShadow = "none";
            }
        });
    </script>
@endsection

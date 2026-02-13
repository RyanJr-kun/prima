@extends('layouts/contentNavbarLayout')
@section('title', 'Monitoring Beban Kerja Dosen')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .hover-bg:hover {
            background-color: #f5f5f9;
        }

        .active-row {
            background-color: #e7e7ff !important;
        }
    </style>
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')

    {{-- HEADER --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Monitoring Workload Dosen</h5>
                    <small class="text-muted">Periode: {{ $activePeriod->name ?? '-' }}</small>
                </div>
                {{-- Filter Global bisa ditaruh disini --}}
            </div>
        </div>
    </div>

    <div class="row">
        {{-- CARD 2: DAFTAR DOSEN / NAVIGASI (KANAN ATAS) --}}
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Dosen</h5>

                    {{-- Pencarian Cepat --}}
                    <form action="{{ route('monitoring.bkd') }}" method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control form-control-sm me-2"
                            placeholder="Cari Nama/NIDN..." value="{{ request('q') }}">
                        <button type="submit" class="btn btn-sm btn-primary"><i class='bx bx-search'></i></button>
                    </form>
                </div>
                <div class="table-responsive text-nowrap" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead class="position-sticky top-0 bg-white" style="z-index: 2;">
                            <tr>
                                <th width="3%">No</th>
                                <th>Nama Dosen</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $key => $user)
                                <tr class="cursor-pointer hover-bg dosen-row"
                                    onclick="loadDosenDetail(this, {{ $user->id }}, '{{ $user->name }}')">
                                    <td>{{ ++$key }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($user->name, 0, 2) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-semibold d-block text-heading">{{ $user->name }}</span>
                                                <small class="text-muted">NIDN : {{ $user->nidn ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-icon btn-label-secondary">
                                            <i class='bx bx-chevron-right'></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="card-footer py-2">
                    {{ $data->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        {{-- CARD 1: CHART & SUMMARY PRODI (KIRI ATAS) --}}
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Analisis Beban Kerja</h5>
                    <small class="text-muted" id="selectedDosenName">Pilih dosen di tabel sebelah kanan</small>
                </div>
                <div class="card-body">
                    {{-- Area Chart --}}
                    <div id="chartContainer" style="min-height: 200px;"
                        class="d-flex align-items-center justify-content-center">
                    </div>

                    {{-- Area Total --}}
                    <div class="text-center mt-3 mb-4 d-none" id="totalSksContainer">
                        <h2 class="fw-bold text-primary mb-0" id="grandTotalSks">0</h2>
                        <small class="text-uppercase fw-semibold text-muted">Total SKS Ajar</small>
                    </div>

                    {{-- Area List Prodi --}}
                    <div class="mt-4">
                        <ul class="list-group list-group-flush" id="prodiListContainer">
                            {{-- Item prodi akan di-inject JS disini --}}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 3: DETAIL MATA KULIAH (BAWAH) --}}
    <div class="row">
        <div class="col-12">
            <div class="card" id="detailMatkulCard" style="display: none;">
                <div class="card-header border-bottom">
                    <h5 class="mb-0 fw-bold">Rincian Mata Kuliah Diampu</h5>
                    <small></small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mata Kuliah</th>
                                <th>Kode</th>
                                <th>Kelas</th>
                                <th>Prodi</th>
                                <th class="text-center">Jenis</th>
                                <th class="text-center">SKS Beban</th>
                            </tr>
                        </thead>
                        <tbody id="tableDetailBody">
                            {{-- Row diinject JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page-script')
    <script>
        let bkdChart = null;

        function loadDosenDetail(row, userId, userName) {
            // 1. UI Feedback (Active Row)
            document.querySelectorAll('.dosen-row').forEach(r => r.classList.remove('active-row'));
            row.classList.add('active-row');

            // 2. Update Header Kiri
            document.getElementById('selectedDosenName').innerText = userName;
            if (bkdChart) {
                bkdChart.destroy();
                bkdChart = null;
            }
            document.getElementById('grandTotalSks').innerText = '0';

            // Show loading
            document.getElementById('chartContainer').innerHTML =
                '<div class="spinner-border text-primary" role="status"></div>';
            document.getElementById('prodiListContainer').innerHTML =
                '<li class="list-group-item text-center">Memuat data...</li>';
            document.getElementById('detailMatkulCard').style.display = 'none';

            // 3. Fetch Data API
            fetch("{{ url('/api/dosen-stats') }}/" + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderChart(data.chart_data);
                        renderProdiList(data.prodi_data);
                        renderDetailTable(data.detail_table);

                        // Update Total SKS Besar
                        document.getElementById('grandTotalSks').innerText = data.total_sks;
                        document.getElementById('totalSksContainer').classList.remove('d-none');

                        // Show Bottom Card
                        document.getElementById('detailMatkulCard').style.display = 'block';
                    } else {
                        showEmptyState();
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('chartContainer').innerHTML =
                        '<span class="text-danger">Gagal memuat data.</span>';
                });
        }

        function renderChart(seriesData) {
            document.getElementById('chartContainer').innerHTML = ''; // Clear loading

            const options = {
                series: seriesData, // [Teori, Praktik, Lapangan]
                chart: {
                    width: 320,
                    type: 'donut',
                },
                labels: ['Teori', 'Praktik', 'Lapangan'],
                colors: ['#696cff', '#71dd37', '#03c3ec'], // Primary, Success, Info
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toFixed(2) +
                                            ' SKS';
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: false
                }
            };

            // Hapus chart lama jika ada
            if (bkdChart) {
                bkdChart.destroy();
            }

            bkdChart = new ApexCharts(document.querySelector("#chartContainer"), options);
            bkdChart.render();
        }

        function renderProdiList(prodiData) {
            let html = '';
            for (const [prodi, sks] of Object.entries(prodiData)) {
                html += `
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                  <div class="d-flex align-items-center">
                      <span class="badge badge-center rounded-pill bg-label-primary w-px-30 h-px-30 me-2">
                          <i class='bx bx-building'></i>
                      </span>
                      <span class="text-heading fw-semibold">${prodi}</span>
                  </div>
                  <span class="badge bg-primary">${parseFloat(sks).toFixed(2)} SKS</span>
              </li>
          `;
            }
            document.getElementById('prodiListContainer').innerHTML = html;
        }

        function renderDetailTable(matkulData) {
            let html = '';
            matkulData.forEach(m => {
                // Logika Warna Badge berdasarkan Shift
                let badgeClass = 'bg-label-primary'; // Default Pagi (Biru)
                let shiftIcon = '<i class="bx bx-sun me-1"></i>';

                if (m.shift === 'malam') {
                    badgeClass = 'bg-label-dark'; // Malam (Hitam/Dark)
                    shiftIcon = '<i class="bx bx-moon me-1"></i>';
                }

                // Format Tampilan Kelas: Badge warna + Nama Kelas + Icon Shift
                let kelasHtml = `
                    <span class="badge ${badgeClass} d-flex align-items-center justify-content-center w-px-120">
                        ${shiftIcon} ${m.kelas}
                    </span>
                `;

                html += `
                    <tr>
                        <td><span class="fw-semibold text-heading">${m.matkul}</span></td>
                        <td><small>${m.kode}</small></td>
                        <td>${kelasHtml}</td>
                        <td>${m.prodi}</td>
                        <td class="text-center">${m.jenis}</td>
                        <td class="text-center fw-bold text-primary">${m.sks_total}</td>
                    </tr>
                `;
            });
            document.getElementById('tableDetailBody').innerHTML = html;
        }

        function showEmptyState() {
            if (bkdChart) {
                bkdChart.destroy();
                bkdChart = null;
            }
            document.getElementById('chartContainer').innerHTML =
                '<div class="text-center p-4"><img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" width="100"><p class="mt-2 text-muted">Belum ada data mengajar.</p></div>';
            document.getElementById('prodiListContainer').innerHTML = '';
            document.getElementById('totalSksContainer').classList.add('d-none');
            document.getElementById('detailMatkulCard').style.display = 'none';
        }
    </script>
@endsection

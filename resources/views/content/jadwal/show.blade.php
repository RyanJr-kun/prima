@extends('layouts/contentNavbarLayout')
@section('title', 'Jadwal Matriks')

@section('page-style')
    <style>
        /* CSS Vertical Text untuk Kolom Hari */
        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-align: center;
            font-weight: bold;
            padding: 5px;
            white-space: nowrap;
            height: 100%;
            max-height: 200px;
            /* Batas tinggi */
        }

        .table-matrix td,
        .table-matrix th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 4px !important;
            font-size: 10px;
            /* Huruf kecil agar muat */
        }

        .cell-filled {
            background-color: #e8f0fe !important;
            /* Warna biru muda untuk sel terisi */
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 5mm;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            body {
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
@endsection

@section('content')

    {{-- FILTER (Sama seperti sebelumnya) --}}
    <div class="card card-filter mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('jadwal-perkuliahan.show') }}" class="row g-3">

                <div class="col-md-2">
                    <label class="form-label">Kampus</label>
                    <select name="campus" class="form-select select2" onchange="this.form.submit()">
                        <option value="kampus_1" {{ $campus == 'kampus_1' ? 'selected' : '' }}>Kampus 1</option>
                        <option value="kampus_2" {{ $campus == 'kampus_2' ? 'selected' : '' }}>Kampus 2</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Shift</label>
                    <select name="shift" class="form-select select2" onchange="this.form.submit()">
                        <option value="pagi" {{ $shift == 'pagi' ? 'selected' : '' }}>Kelas Pagi</option>
                        <option value="malam" {{ $shift == 'malam' ? 'selected' : '' }}>Kelas Malam</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Filter Prodi (Opsional)</label>
                    <select name="prodi_id" class="form-select select2" onchange="this.form.submit()">
                        <option value="">-- Semua Prodi --</option>
                        @foreach ($prodis as $prodi)
                            <option value="{{ $prodi->id }}" {{ $prodiId == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->jenjang }} {{ $prodi->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-select select2" onchange="this.form.submit()">
                        <option value="">-- Semua --</option>
                        @foreach (range(1, 8) as $sem)
                            <option value="{{ $sem }}" {{ $semester == $sem ? 'selected' : '' }}>Semester
                                {{ $sem }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- <div class="col-md-3 d-flex align-items-end">
                    <button type="button" onclick="window.print()" class="btn btn-primary w-100">
                        <i class='bx bx-printer me-1'></i> Cetak / Simpan PDF
                    </button>
                </div> --}}
            </form>
        </div>
    </div>

    <div class="print-area">

        {{-- LOOPING PER HALAMAN (Per 6 Ruangan) --}}
        @foreach ($roomChunks as $chunkIndex => $rooms)
            <div class="matrix-section {{ !$loop->last ? 'page-break' : '' }}">

                {{-- HEADER DOKUMEN --}}
                <div class="text-center mb-3">
                    <h4 class="fw-bold mb-0">JADWAL KULIAH {{ strtoupper($shift) }} (Hal {{ $loop->iteration }})</h4>
                    <small>{{ $campus }} - Semester Ganjil 2025/2026</small>
                </div>

                <table class="table table-bordered table-matrix w-100">
                    <thead>
                        <tr class="bg-light">
                            <th style="width: 30px;">HARI</th>
                            <th style="width: 80px;">JAM</th>
                            {{-- Header Nama Ruangan --}}
                            @foreach ($rooms as $room)
                                <th>{{ $room->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($days as $day)
                            @php
                                $daysIndo = [
                                    'Monday' => 'SENIN',
                                    'Tuesday' => 'SELASA',
                                    'Wednesday' => 'RABU',
                                    'Thursday' => 'KAMIS',
                                    'Friday' => 'JUMAT',
                                    'Saturday' => 'SABTU',
                                ];

                                // Khusus SABTU shift malam, jika user ingin ikut jam pagi,
                                // Anda bisa ganti $masterSlots di sini secara dinamis.
                                // Untuk sekarang kita ikut default $masterSlots dari controller.
                                $currentSlots = $masterSlots;
                            @endphp

                            {{-- Loop Slot Waktu --}}
                            @foreach ($currentSlots as $slotIndex => $slotTime)
                                <tr>
                                    {{-- KOLOM HARI (Rowspan) --}}
                                    @if ($slotIndex === 0)
                                        <td rowspan="{{ count($currentSlots) }}" class="bg-light fw-bold">
                                            <div class="vertical-text">{{ $daysIndo[$day] }}</div>
                                        </td>
                                    @endif

                                    {{-- KOLOM JAM --}}
                                    <td class="bg-light fw-bold">
                                        {{ $slotTime[0] }} - {{ $slotTime[1] }}
                                    </td>

                                    {{-- KOLOM ISI (Per Ruangan) --}}
                                    @foreach ($rooms as $room)
                                        @php
                                            // Cek apakah ada jadwal di koordinat ini
                                            $sched = $scheduleMatrix[$day][$slotIndex][$room->id] ?? null;
                                        @endphp

                                        @if ($sched)
                                            <td class="cell-filled">
                                                <strong>{{ $sched->course->name }}</strong><br>
                                                <span class="text-muted">{{ $sched->studyClass->name }}</span><br>
                                                <small>{{ $sched->lecturer->name }}</small>
                                            </td>
                                        @else
                                            <td></td> {{-- Sel Kosong --}}
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach

                            {{-- Pemisah Antar Hari (Optional) --}}
                            <tr style="height: 5px; background: #000;">
                                <td colspan="{{ count($rooms) + 2 }}" class="p-0 bg-dark"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

    </div>
@endsection

@section('page-script')
    <script type="module">
        // Fungsi inisialisasi Select2
        const initSelect2 = () => {
            // Cek apakah jQuery dan Select2 sudah siap
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: $this.find('option[value=""]').length >
                            0, // Hanya allowClear jika ada value=""
                        width: '100%',
                        minimumResultsForSearch: 10 // Sembunyikan search box jika opsi < 10
                    });
                });
            } else {
                // Jika belum siap, coba lagi dalam 100ms
                setTimeout(initSelect2, 100);
            }
        };

        // Jalankan saat script dimuat
        initSelect2();
    </script>
@endsection

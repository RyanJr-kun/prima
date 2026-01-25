<!DOCTYPE html>
<html>

<head>
    <title>Cetak Distribusi Mata Kuliah</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
        }

        /* KOP SURAT */
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: auto;
        }

        .kampus-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .sk-text {
            font-size: 11px;
            margin: 2px 0;
        }

        .alamat {
            font-size: 10px;
            margin: 2px 0;
        }

        /* JUDUL DOKUMEN */
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
            text-transform: capitalize;
        }

        .doc-title h3 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .doc-title p {
            margin: 2px 0;
            font-size: 13px;
            font-weight: bold;
        }

        /* TABEL DATA */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 4px;
        }

        .data-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        /* PAGE BREAK */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    {{-- 1. KOP SURAT (Header) --}}
    <table class="header-table">
        <tr>
            <td width="10%" class="text-center">
                {{-- Pastikan file logo ada di public/assets/img/logo.png --}}
                {{-- Gunakan public_path agar terbaca oleh DomPDF --}}
                <img src="{{ public_path('assets/img/logo.png') }}" class="logo">
            </td>
            <td width="90%" class="text-center">
                <h1 class="kampus-name">POLITEKNIK INDONUSA SURAKARTA</h1>
                <p class="sk-text">SK. Mendiknas RI No. 166/D/O/2002</p>
                <p class="alamat">
                    Kampus 1: Jl. KH. Samanhudi No. 31, Sondakan, Laweyan, Surakarta <br>
                    Kampus 2: Jl. Palaraya No. 5, Cemani, Grogol, Sukoharjo <br>
                    Email: info@poltekindonusa.ac.id | Website: www.poltekindonusa.ac.id
                </p>
            </td>
        </tr>
    </table>

    {{-- 2. JUDUL DOKUMEN --}}
    <div class="doc-title">
        <h3>{{ $doc->type_label }}</h3> {{-- Pakai Accessor yg kita buat tadi --}}
        <p>
            Semester {{ $semesterLabel }} - {{ $doc->prodi->jenjang }} {{ $doc->prodi->name }}
        </p>
        <p>Tahun Akademik {{ $tahunAkademik }}</p>
    </div>

    {{-- 3. ISI DATA (Looping per Semester agar rapi) --}}
    @foreach ($dataIsi as $semester => $items)
        <h4 style="margin-bottom: 5px;">Semester {{ $semester }}</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="10%">Kode</th>
                    <th width="30%">Mata Kuliah</th>
                    <th width="5%">SKS</th>
                    <th width="10%">Kelas</th>
                    <th width="20%">Dosen Pengampu</th>
                    <th width="20%">Dosen Team</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $item->course->code }}</td>
                        <td>{{ $item->course->name }}</td>
                        <td class="text-center">{{ $item->course->sksTotal }}</td>
                        <td class="text-center">{{ $item->studyClass->name }}</td>
                        <td>{{ $item->user->name ?? '-' }}</td>
                        <td>{{ $item->pddiktiUser->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
    @endforeach

    {{-- 4. TANDA TANGAN (Opsional tapi penting untuk dokumen resmi) --}}
    <table width="100%" style="margin-top: 30px;">
        <tr>
            <td width="70%"></td>
            <td width="30%" class="text-center">
                Surakarta, {{ date('d F Y') }} <br>
                Direktur,
                <br><br><br><br>
                <strong><u>Ir. Suci Purwandari, M.M.</u></strong> <br>
                NIDN. 0605050505
            </td>
        </tr>
    </table>

</body>

</html>

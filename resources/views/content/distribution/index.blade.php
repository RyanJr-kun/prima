@extends('layouts/contentNavbarLayout')
@section('title', 'Distribusi Mata Kuliah - PRIMA')

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

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('distributions.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Periode Akademik</label>
                        <select name="period_id" class="form-select select2">
                            @foreach ($periods as $p)
                                <option value="{{ $p->id }}"
                                    {{ request('period_id', $activePeriod->id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Program Studi</label>
                        <select name="prodi_id" class="form-select select2">
                            <option value="">Semua Program Studi</option>
                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}"
                                    {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->jenjang }} - {{ $prodi->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select select2">
                            <option value="">Semua Semester</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>
                                    Semester {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" title="Filter Data"><i
                                class='bx bx-filter-alt'></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="fw-bold mb-0">Distribusi Perkuliahan ({{ $activePeriod->name }})</h5>
                    <small>Management Distribusi Mata Kuliah</small>
                </div>
                <div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bx bx-spreadsheet me-1"></i> Import Excel
                    </button>
                    <a href="{{ route('distributions.create') }}" class="btn btn-primary">
                        + Tambah Distribusi
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- LOOPING GROUPING PER KELAS --}}
    @forelse($distributions as $classId => $items)
        @php
            $kelasInfo = $items->first()->studyClass;
        @endphp

        <div class="card mb-4">
            <div class="card-header bg-label-primary">
                <h5 class="mb-0 text-primary">
                    <i class='bx bx-building'></i> {{ $kelasInfo->full_name }}
                    <span class="badge bg-white text-primary ms-2">{{ $items->count() }} Matkul</span>
                </h5>
                <small>PA: {{ $kelasInfo->academicAdvisor->name ?? '-' }} | Mhs: {{ $kelasInfo->total_students }}</small>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Kode</th>
                            <th rowspan="2">Mata Kuliah</th>

                            <th colspan="4" class="border py-1 text-center">SKS</th>
                            <th rowspan="2">Dosen Pengampu</th>
                            <th rowspan="2">Dosen PDDIKTI</th>
                            <th rowspan="2">Aksi</th>
                        </tr>
                        <tr>
                            <th class="text-center py-1 border">T</th>
                            <th class="text-center py-1 border">P</th>
                            <th class="text-center py-1 border">PL</th>
                            <th class="text-center py-1 border">JML</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $key => $dist)
                            <tr>
                                <td>{{ ++$key }}</td>
                                {{-- <td>{{ $dist->course->semester }}</td> --}}
                                <td>{{ $dist->course->code ?? '-' }}</td>
                                <td class="fw-bold">{{ $dist->course->name ?? '-' }}</td>
                                <td class="text-center border">{{ $dist->course->sks_teori ?? '-' }}</td>
                                <td class="text-center border">{{ $dist->course->sks_praktik ?? '-' }}</td>
                                <td class="text-center border">{{ $dist->course->sks_lapangan ?? '-' }}</td>
                                <td class="text-center border">{{ $dist->course->sksTotal ?? '-' }}</td>
                                <td>{{ $dist->user->name ?? '-' }}</td>
                                <td>{{ $dist->teamTeaching->name ?? '-' }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:;" class="text-body edit-record me-2"
                                            data-id="{{ $dist->id }}"
                                            data-matkul="{{ $dist->course->name }} - {{ $dist->course->code }}"
                                            data-kelas="{{ $dist->studyClass->full_name }}">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('distributions.destroy', $dist->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <a href="javascript:;" class="text-body  delete-record"
                                                data-bs-toggle="tooltip" data-bs-offset="0,6" data-bs-placement="bottom"
                                                data-bs-html="true" title="Delete Distribusi">
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
        </div>
    @empty
        <div class="alert alert-warning">Belum ada data distribusi mata kuliah. Silakan input data.</div>
    @endforelse

    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('course-distributions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small>Download format excel: <a href="{{ route('course-distributions.template') }}"
                                    class="fw-bold">Klik Disini</a></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Target Kelas</label>
                            <select name="study_class_id" class="form-select select2-import" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($study_classes as $kls)
                                    <option value="{{ $kls->id }}">{{ $kls->prodi->jenjang }}
                                        {{ $kls->prodi->code }} - {{ $kls->name }} ({{ ucfirst($kls->shift) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Excel</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="editCanvas" aria-labelledby="editCanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="editCanvasLabel" class="offcanvas-title fw-bold">Revisi Distribusi</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="formEdit" method="POST">
                @csrf
                @method('PUT')

                <div class="alert alert-secondary mb-3">
                    <small class="d-block text-muted">Mata Kuliah:</small>
                    <strong id="viewMatkul">Loading...</strong>
                    <hr class="my-1">
                    <small class="d-block text-muted">Kelas:</small>
                    <strong id="viewKelas">Loading...</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dosen Pengampu Utama</label>
                    <select name="user_id" id="edit_user_id" class="form-select select2-edit">
                        <option value="">-- Pilih Dosen --</option>
                        {{-- Kita inject semua dosen dari controller ke sini --}}
                        {{-- Pastikan variable $allDosens dikirim dari controller index() --}}
                        @foreach (\App\Models\User::role('dosen')->get() as $dosen)
                            <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dosen Team / PDDIKTI</label>
                    <select name="pddikti_user_id" id="edit_pddikti_user_id" class="form-select select2-edit">
                        <option value="">-- Kosong --</option>
                        @foreach (\App\Models\User::role('dosen')->get() as $dosen)
                            <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Referensi</label>
                    <textarea name="referensi" id="edit_referensi" rows="3" class="form-control"
                        placeholder="Contoh: Buku Algoritma 2024"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Luaran Mata Kuliah</label>
                    <textarea name="luaran" id="edit_luaran" rows="3" class="form-control"
                        placeholder="Contoh: Mahasiswa mampu membuat program..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                <button type="button" class="btn btn-outline-secondary w-100 mt-2"
                    data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>

@endsection

@section('page-script')
    <script type="module">
        // 1. Fungsi Inisialisasi Select2 yang Robust (Menunggu jQuery & Plugin siap)
        const initSelect2 = () => {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                // Select2 untuk Edit Canvas
                $('.select2-edit').each(function() {
                    $(this).select2({
                        dropdownParent: $('#editCanvas'),
                        width: '100%',
                        placeholder: '-- Pilih --',
                        allowClear: true
                    });
                });

                // Select2 untuk Filter (Halaman Utama)
                $('.select2').select2({
                    width: '100%'
                });

                // Select2 untuk Import Modal
                $('.select2-import').select2({
                    dropdownParent: $('#importModal'),
                    width: '100%'
                });
            } else {
                // Coba lagi dalam 100ms jika belum siap
                setTimeout(initSelect2, 100);
            }
        };

        // Jalankan inisialisasi
        initSelect2();

        // 2. Event Handler untuk Tombol Edit
        document.addEventListener('DOMContentLoaded', function() {
            // Gunakan Event Delegation untuk menangani klik pada .edit-record
            $('body').on('click', '.edit-record', function(e) {
                e.preventDefault();

                let id = $(this).data('id');
                let matkulName = $(this).data('matkul');
                let kelasName = $(this).data('kelas');

                // A. Tampilkan Info Visual
                $('#viewMatkul').text(matkulName);
                $('#viewKelas').text(kelasName);

                // B. Set URL Form Action
                let updateUrl = "{{ route('distributions.update', ':id') }}";
                updateUrl = updateUrl.replace(':id', id);
                $('#formEdit').attr('action', updateUrl);

                // C. Ambil Data via AJAX
                $.ajax({
                    url: '/distributions/' + id + '/edit',
                    type: 'GET',
                    success: function(data) {
                        // Isi Input Text/Textarea
                        $('#edit_referensi').val(data.referensi);
                        $('#edit_luaran').val(data.luaran);

                        // Update Select2 (Penting: trigger change agar UI berubah)
                        $('#edit_user_id').val(data.user_id).trigger('change');
                        $('#edit_pddikti_user_id').val(data.pddikti_user_id).trigger('change');

                        // D. Buka Offcanvas setelah data siap
                        var myOffcanvas = new bootstrap.Offcanvas(document.getElementById(
                            'editCanvas'));
                        myOffcanvas.show();
                    },
                    error: function() {
                        alert('Gagal mengambil data!');
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
@endsection

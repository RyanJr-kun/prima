@extends('layouts/contentNavbarLayout')
@section('title', 'Kelas Perkuliahan - PRIMA')

@section('content')

    {{-- 1. TOAST NOTIFICATION --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        @if (session('success'))
            <div id="successToast" class="bs-toast bg-primary toast fade hide" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="toast-header">
                    <i class="icon-base bx bx-bell icon-xs me-2"></i>
                    <span class="fw-medium me-auto">Berhasil</span>
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
                    <span class="fw-medium me-auto">Error</span>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">{{ session('error') }}</div>
            </div>
        @endif
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('master.kelas.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Cari Nama Kelas</label>
                        <input type="text" name="q" class="form-control" placeholder="Contoh: A, B"
                            value="{{ request('q') }}">
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-6 col-md-2">
                        <label class="form-label">Shift</label>
                        <select name="shift" class="form-select select2">
                            <option value="">Semua</option>
                            <option value="pagi" {{ request('shift') == 'pagi' ? 'selected' : '' }}>Pagi</option>
                            <option value="malam" {{ request('shift') == 'malam' ? 'selected' : '' }}>Malam</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Angkatan</label>
                        <select name="angkatan" class="form-select select2">
                            <option value="">Semua</option>
                            @foreach ($angkatans as $angkatan)
                                <option value="{{ $angkatan }}"
                                    {{ request('angkatan') == $angkatan ? 'selected' : '' }}>{{ $angkatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2 text-end d-flex align-items-end">
                        <a href="{{ route('master.kelas.index') }}" class="btn btn-secondary me-3">Reset</a>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="row">
                <div class="col-6">
                    <h5 class="card-title fw-bold mb-0">Kelas Perkuliahan</h5>
                    <small class="d-none d-md-block text-muted">Data kelas aktif berdasarkan Periode Akademik.</small>
                </div>
                <div class="col-6 text-end">

                    <button type="button" class="btn btn-success my-1" data-bs-toggle="modal"
                        data-bs-target="#importModal">
                        <i class="bx bx-spreadsheet me-1"></i> Import
                    </button>
                    <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddKelas" id="btnCreate">
                        <span><i class="bx bx-plus me-2"></i> Kelas</span>
                    </button>
                    {{-- @if ($classes->isEmpty())
                        <div class="alert alert-warning d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bx bx-info-circle me-1"></i>
                                Data kelas untuk periode <strong>{{ $activePeriod->name }}</strong> belum ada.
                            </span>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#copyClassModal">
                                <i class="bx bx-copy me-1"></i> Salin dari Periode Lalu
                            </button>
                        </div>
                    @endif --}}

                </div>
            </div>
        </div>

        {{-- 3. TABLE DATA --}}
        <div class="card-datatable table-responsive">
            <table class="table border-top" id="tableClass">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th class="text-center">Semester</th>
                        <th class="text-center">Periode</th>
                        <th class="text-center">Mhs</th>
                        <th>Wali Dosen</th>
                        <th class="text-center">Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classes as $key => $kelas)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    {{-- Menggunakan Accessor Full Name (TRPL 22A) --}}
                                    <span class="fw-bold">{{ $kelas->full_name }}</span>
                                    <small class="text-muted">Angkatan {{ $kelas->angkatan }}</small>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-label-info">Semester {{ $kelas->semester }}</span>
                            </td>
                            <td class="text-center">{{ $kelas->period->name ?? '-' }}</td>
                            <td class="text-center">{{ $kelas->total_students }}</td>
                            <td>
                                @if ($kelas->academicAdvisor)
                                    <div class="d-flex justify-content-start align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ substr($kelas->academicAdvisor->name, 0, 2) }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span
                                                class="text-truncate fw-medium">{{ $kelas->academicAdvisor->name }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($kelas->is_active == 1)
                                    <span class="badge bg-label-success me-1">Active</span>
                                @else
                                    <span class="badge bg-label-secondary me-1">Offline</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:;" class="text-body edit-record me-2" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasAddKelas" data-id="{{ $kelas->id }}"
                                        data-name="{{ $kelas->name }}" data-angkatan="{{ $kelas->angkatan }}"
                                        data-semester="{{ $kelas->semester }}" data-shift="{{ $kelas->shift }}"
                                        data-total-students="{{ $kelas->total_students }}"
                                        data-prodi-id="{{ $kelas->prodi_id }}"
                                        data-kurikulum-id="{{ $kelas->kurikulum_id }}"
                                        data-advisor-id="{{ $kelas->academic_advisor_id }}"
                                        data-academic-period-id="{{ $kelas->academic_period_id }}"
                                        data-is-active="{{ $kelas->is_active }}"
                                        data-action="{{ route('master.kelas.update', $kelas->id) }}">
                                        <i class="bx bx-edit text-muted bx-sm"></i>
                                    </a>

                                    {{-- TOMBOL DELETE --}}
                                    <form action="{{ route('master.kelas.destroy', $kelas->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <a href="javascript:;" class="text-body  delete-record" data-bs-toggle="tooltip"
                                            data-bs-offset="0,6" data-bs-placement="bottom" data-bs-html="true"
                                            title="Delete kelas">
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
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddKelas" aria-labelledby="offcanvasAddKelasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddKelasLabel" class="offcanvas-title">Tambah Kelas Baru</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body mx-0 grow-0 p-6 h-100">
            <form class="add-new-Kelas pt-0" id="addNewKelasForm" action="{{ route('master.kelas.store') }}"
                method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Program Studi</label>
                    <select name="prodi_id" id="prodi_id" class="form-select select2" required
                        data-placeholder="Pilih Prodi">
                        <option value=""></option>
                        @foreach ($prodis as $prodi)
                            <option value="{{ $prodi->id }}">
                                {{ $prodi->jenjang }} - {{ $prodi->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('prodi_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="add-name-class">Nama Kelas</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="add-name-class" placeholder="Contoh: A, B" name="name" value="{{ old('name') }}"
                            required />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="add-angkatan">Angkatan</label>
                        <input type="number" class="form-control @error('angkatan') is-invalid @enderror"
                            id="add-angkatan" placeholder="2024" name="angkatan" value="{{ old('angkatan') }}"
                            required />
                        @error('angkatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        Shift Waktu Kuliah
                        <i class="bx bx-help-circle text-muted ms-1" data-bs-toggle="popover" data-bs-placement="top"
                            data-bs-trigger="hover" title="Catatan Bu Dewi"
                            data-bs-content="- Karyawan baru (2024+) rata-rata ikut jadwal <b>Pagi</b> (gabung). <br> - Pilih <b>Malam</b> HANYA jika kelas tersebut adalah Reguler 2 atau Karyawan yang diminta dosen terpisah."></i>
                    </label>
                    <select name="shift" class="form-select select2" id="shift" required
                        data-placeholder="Pilih Shift">
                        <option value=""></option>
                        <option value="pagi">Pagi (08.00 - 16.00) - Reguler 1</option>
                        <option value="malam">Malam (17.00 - 20.00) - Reguler 2 & Karyawan</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Semester Saat Ini</label>
                        <select name="semester" id="semester" class="form-select select2" required
                            data-placeholder="Pilih Semester">
                            <option value=""></option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                        @error('semester')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="add-total-students">Kapasitas (Mhs)</label>
                        <input type="number" class="form-control @error('total_students') is-invalid @enderror"
                            id="add-total-students" placeholder="30" name="total_students"
                            value="{{ old('total_students') }}" required />
                        @error('total_students')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kurikulum</label>
                    <select name="kurikulum_id" id="kurikulum_id" class="form-select select2" required
                        data-placeholder="Pilih Kurikulum">
                        <option value=""></option>
                        @if (isset($kurikulums))
                            @foreach ($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->id }}">{{ $kurikulum->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('kurikulum_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Pembimbing Akademik</label>
                    <select name="academic_advisor_id" id="academic_advisor_id" class="form-select select2" required
                        data-placeholder="Pilih Pembimbing Akademik">
                        <option value=""></option>
                        @if (isset($dosens))
                            @foreach ($dosens as $advisor)
                                <option value="{{ $advisor->id }}">{{ $advisor->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('academic_advisor_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="status" value="1">
                    <label class="form-check-label" for="status">Active</label>
                </div>

                <button type="submit" class="btn btn-primary me-3" id="saveBtn">Simpan Data</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('kelas-perkuliahan.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    {{-- INPUT HIDDEN PERIODE AKADEMIK (WAJIB) --}}
                    @if (isset($activePeriod))
                        <input type="hidden" name="academic_period_id" value="{{ $activePeriod->id }}">
                    @endif

                    <div class="modal-header">
                        <h5 class="modal-title">Import Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small>Download format excel: <a href="{{ route('kelas-perkuliahan.template') }}"
                                    class="fw-bold">Klik Disini</a></small>
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
    <div class="modal fade" id="copyClassModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('study-classes.copy') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Salin Master Data Kelas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="target_period_id" value="{{ $activePeriod->id }}">

                        <div class="mb-3">
                            <label class="form-label">Salin dari Periode:</label>
                            <select name="source_period_id" class="form-select select2"
                                data-bs-placeholder="pilih periode" required>
                                @foreach ($periods as $p)
                                    {{-- Jangan tampilkan periode aktif saat ini --}}
                                    @if ($p->id != $activePeriod->id)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="alert alert-info small">
                            <i class="bx bx-bulb"></i> Tips: Sistem akan menduplikasi nama kelas, prodi, dan atribut
                            lainnya. Mahasiswa tidak ikut disalin.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Mulai Salin</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('page-script')
    <script type="module">
        // Fungsi inisialisasi
        const initSelect2 = () => {
            // Cek apakah jQuery dan Select2 sudah siap
            if (typeof $ !== 'undefined' && $.fn.select2) {

                // Init Select2 untuk Filter di halaman utama
                $('.card-body .select2').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Pilih...'
                });

                // Targetkan select2 di dalam Offcanvas secara spesifik
                $('#offcanvasAddKelas .select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: true,
                        dropdownParent: $('#offcanvasAddKelas'), // <--- INI KUNCINYA
                        width: '100%', // Paksa lebar agar tidak menyempit
                        templateSelection: function(data) {
                            if (!data.id) {
                                return data.text;
                            }
                            // Gunakan data-code jika ada (untuk prodi), jika tidak gunakan text biasa
                            const code = $(data.element).data('code');
                            return code ? code : data.text;
                        }
                    });
                });

            } else {
                // Jika belum siap, coba lagi dalam 100ms
                setTimeout(initSelect2, 100);
            }
        };

        // Jalankan saat script dimuat
        initSelect2();

        // PENTING: Jalankan ulang saat Offcanvas dibuka (untuk jaga-jaga rendering error)
        const offcanvasElement = document.getElementById('offcanvasAddKelas');
        offcanvasElement.addEventListener('shown.bs.offcanvas', function() {
            initSelect2();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addNewKelasForm');
            const offcanvasEl = document.getElementById('offcanvasAddKelas');
            const offcanvasTitle = document.getElementById('offcanvasAddKelasLabel');
            const saveBtn = document.getElementById('saveBtn');
            const defaultAction = form.action;

            // Inisialisasi Popover Bootstrap
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    html: true,
                    sanitize: false
                });
            });

            document.body.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-record');
                if (editBtn) {
                    const d = editBtn.dataset;
                    offcanvasTitle.textContent = 'Edit Kelas';
                    saveBtn.textContent = 'Simpan Perubahan';
                    form.action = d.action;

                    let methodInput = form.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);
                    }


                    document.getElementById('add-name-class').value = d.name;
                    document.getElementById('add-angkatan').value = d.angkatan;
                    document.getElementById('add-total-students').value = d.totalStudents;
                    document.getElementById('status').checked = d.isActive == '1';


                    if (window.$) {
                        $('#prodi_id').val(d.prodiId).trigger('change');
                        $('#semester').val(d.semester).trigger('change');
                        $('#kurikulum_id').val(d.kurikulumId).trigger('change');
                        $('#academic_advisor_id').val(d.advisorId).trigger('change');
                        $('#shift').val(d.shift).trigger('change');
                    } else {
                        document.getElementById('prodi_id').value = d.prodiId;
                        document.getElementById('semester').value = d.semester;
                        document.getElementById('kurikulum_id').value = d.kurikulumId;
                        document.getElementById('academic_advisor_id').value = d.advisorId;
                        document.getElementById('shift').value = d.shift;
                    }
                }
            });

            // Reset form 
            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                offcanvasTitle.textContent = 'Tambah Kelas Baru';
                saveBtn.textContent = 'Simpan Data';
                form.action = defaultAction;
                form.reset();


                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();

                if (window.$) {
                    $('#prodi_id').val('').trigger('change');
                    $('#semester').val('').trigger('change');
                    $('#kurikulum_id').val('').trigger('change');
                    $('#academic_advisor_id').val('').trigger('change');
                    $('#shift').val('').trigger('change');

                }
            });

            // Handler untuk tombol delete dengan SweetAlert 
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

            // Buka Offcanvas otomatis jika ada error validasi
            @if ($errors->any())
                const offcanvasError = new bootstrap.Offcanvas(offcanvasEl);
                offcanvasError.show();
            @endif
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Ambil elemen DOM
            const selectProdi = document.getElementById('prodi_id');
            const selectKurikulum = document.getElementById('kurikulum_id');

            // Pastikan elemen ada sebelum menjalankan script (untuk menghindari error di halaman lain)
            if (selectProdi && selectKurikulum) {

                // 2. Event Listener 'change'
                selectProdi.addEventListener('change', function() {
                    const prodiId = this.value;

                    // Reset dropdown & Tampilkan Loading
                    selectKurikulum.innerHTML = '<option value="">Loading...</option>';

                    if (prodiId) {

                        fetch('/ajax/get-curriculums-by-prodi/' + prodiId)
                            .then(response => {
                                // Cek jika response sukses (Status 200-299)
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json(); // Parsing JSON
                            })
                            .then(data => {
                                // Kosongkan dropdown lagi
                                selectKurikulum.innerHTML = '';

                                if (data.length === 0) {
                                    const option = document.createElement('option');
                                    option.value = "";
                                    option.textContent = "Tidak ada kurikulum aktif di prodi ini";
                                    selectKurikulum.appendChild(option);
                                } else {
                                    // Tambah opsi default
                                    const defaultOption = document.createElement('option');
                                    defaultOption.value = "";
                                    defaultOption.textContent = "Pilih Kurikulum";
                                    selectKurikulum.appendChild(defaultOption);

                                    // Looping data (pengganti $.each)
                                    data.forEach(curr => {
                                        const option = document.createElement('option');
                                        option.value = curr.id;
                                        const year = curr.tanggal ? new Date(curr.tanggal)
                                            .getFullYear() : '';
                                        option.textContent = `${curr.name} (${year})`;
                                        selectKurikulum.appendChild(option);
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                selectKurikulum.innerHTML =
                                    '<option value="">Gagal memuat data</option>';
                            });
                    } else {
                        // Jika Prodi tidak dipilih (kembali ke default)
                        selectKurikulum.innerHTML =
                            '<option value="">-- Pilih Prodi Terlebih Dahulu --</option>';
                    }
                });
            }
        });
    </script>
@endsection

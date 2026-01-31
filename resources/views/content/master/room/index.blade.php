@extends('layouts/contentNavbarLayout')
@section('title', 'Ruangan - PRIMA')

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
            <form action="{{ route('master.ruangan.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cari Ruangan / Kode</label>
                        <input type="text" name="q" class="form-control" placeholder="Nama atau Kode Ruangan"
                            value="{{ request('q') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Lokasi Kampus</label>
                        <select name="location" class="form-select select2">
                            <option value="">Semua Kampus</option>
                            <option value="kampus_1" {{ request('location') == 'kampus_1' ? 'selected' : '' }}>Kampus 1
                            </option>
                            <option value="kampus_2" {{ request('location') == 'kampus_2' ? 'selected' : '' }}>Kampus 2
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipe Ruangan</label>
                        <select name="type" class="form-select select2">
                            <option value="">Semua Tipe</option>
                            <option value="teori" {{ request('type') == 'teori' ? 'selected' : '' }}>Teori</option>
                            <option value="laboratorium" {{ request('type') == 'laboratorium' ? 'selected' : '' }}>
                                Laboratorium</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>
                            Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="row">
                <div class="col-6">
                    <h5 class="card-title fw-bold mb-0">Data Ruangan</h5>
                    <small class="d-none d-md-block text-muted">Managemen Data Ruangan.</small>
                </div>
                <div class="col-6 text-end">
                    <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddRoom" id="btnCreate">
                        <span><i class="bx bx-plus me-2"></i>Ruangan</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table border-top" id="tableClass">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Ruangan</th>
                        <th>Kapasitas</th>
                        <th>Tipe</th>
                        {{-- <th>Kepemilikan (Prodi)</th> --}}
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rooms as $key => $room)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td><strong>{{ $room->code }}</strong></td>
                            <td>{{ $room->name }}</td>
                            <td>{{ $room->capacity }} Kursi</td>
                            <td>
                                @if ($room->type == 'laboratorium')
                                    <span class="badge bg-label-primary">Laboratorium</span>
                                @else
                                    <span class="badge bg-label-warning">Teori Kelas</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:;" class="text-body view-record me-2" data-bs-toggle="popover"
                                        data-bs-trigger="hover focus" data-bs-placement="left" data-bs-html="true"
                                        data-bs-custom-class="popover-primary"
                                        title="<span class='fw-medium'><i class='bx bx-info-circle me-1'></i> Rangkuman Ruang</span>"
                                        data-bs-content="
                                            <div class='d-flex flex-column gap-2 p-1' style='min-width: 200px;'>
                                                <div class='d-flex justify-content-between align-items-center border-bottom pb-1'>
                                                    <span class='text-muted small'>Nama</span>
                                                    <span class='text-muted small fw-bold'>{{ $room->name }}</span>
                                                </div>
                                                <div class='d-flex justify-content-between align-items-center border-bottom pb-1'>
                                                    <span class='text-muted small'>Kapasitas</span>
                                                    <span class='text-muted small fw-bold'>{{ $room->capacity }} Kursi</span>
                                                </div>
                                                <div class='align-items-center border-bottom'>
                                                    <span class='text-muted d-block small'>Fasilitas</span>
                                                    @foreach ($room->facility_tags ?? [] as $tag)
<span class='badge d-block bg-label-info'>{{ $tags[$tag] ?? $tag }}</span>
@endforeach
                                                </div>
                                                <div class='d-flex justify-content-between align-items-center border-bottom pb-1'>
                                                    <span class='text-muted small me-9'>Lokasi</span>
                                                    <span class='text-muted small'>{{ $room->location }}</span>
                                                </div>
                                                <div>  
                                                    <span class='text-muted small d-block mb-1'>Akses Prodi:</span>
                                                    <div class='d-flex flex-wrap gap-1'>
                                                        @forelse($room->prodis as $p)
<span class='badge bg-label-primary' title='{{ $p->name }}'>{{ $p->code }}</span>
                                                        @empty
                                                            <span class='badge bg-label-secondary'>Umum</span>
@endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        ">
                                        <i class="bx bx-eye text-muted bx-sm"></i>
                                    </a>
                                    <a href="javascript:;" class="text-body edit-record me-2" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasAddRoom" data-id="{{ $room->id }}"
                                        data-name="{{ $room->name }}" data-code="{{ $room->code }}"
                                        data-capacity="{{ $room->capacity }}" data-type="{{ $room->type }}"
                                        data-location="{{ $room->location }}" data-building="{{ $room->building }}"
                                        data-floor="{{ $room->floor }}"
                                        data-prodi-ids="{{ json_encode($room->prodis->pluck('id')) }}"
                                        data-facility-tags="{{ json_encode($room->facility_tags ?? []) }}"
                                        data-action="{{ route('master.ruangan.update', $room->id) }}">
                                        <i class="bx bx-edit text-muted bx-sm"></i>
                                    </a>
                                    <form action="{{ route('master.ruangan.destroy', $room->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <a href="javascript:;" class="text-body  delete-record" data-bs-toggle="tooltip"
                                            data-bs-offset="0,6" data-bs-placement="bottom" data-bs-html="true"
                                            title="Delete Ruangan">
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

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRoom"
            aria-labelledby="offcanvasAddRoomLabel">
            <div class="offcanvas-header border-bottom">
                <h5 id="offcanvasAddRoomLabel" class="offcanvas-title">Tambah Ruangan Baru</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>

            <div class="offcanvas-body mx-0 grow-0 p-6 h-100">
                <form class="add-new-room pt-0" id="addNewRoomForm" action="{{ route('master.ruangan.store') }}"
                    method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <select name="prodi_ids[]" id="prodis" class="form-select select2" multiple
                            data-placeholder="Pilih Program Studi">
                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}" data-code="{{ $prodi->code }}"
                                    {{ isset($room) && $room->prodis->contains($prodi->id) ? 'selected' : '' }}>
                                    {{ $prodi->name }} ({{ $prodi->code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Bisa pilih lebih dari satu prodi. Contoh: Lab Komputer untuk TRPL dan MI.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="code">kode</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                            placeholder="Lab. Kitchen" name="code" value="{{ old('code') }}" />
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="add-name-room">Nama Ruangan</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="add-name-room"
                            placeholder="Laboratorium Kitchen" name="name" value="{{ old('name') }}" />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lokasi Kampus</label>
                            <select name="location" id="location" class="form-select select2" required>
                                <option value="kampus_1">Kampus 1</option>
                                <option value="kampus_2">Kampus 2</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gedung</label>
                            <select name="building" id="building" class="form-select select2" data-tags="true">
                                @foreach (['Gedung A', 'Gedung B', 'Gedung C', 'Gedung D', 'Gedung Utama'] as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lantai</label>
                            <input type="number" name="floor" id="floor" class="form-control" value="1"
                                min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kapasitas</label>
                            <input type="number" name="capacity" id="capacity" class="form-control" value="30"
                                required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Ruangan</label>
                        <select name="type" id="type"
                            class="form-select select2 @error('type') is-invalid @enderror" required
                            data-placeholder="Pilih Tipe Ruangan">
                            <option value="">Pilih </option>
                            <option value="teori">Teori</option>
                            <option value="laboratorium">Laboratorium</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- <div class="mb-3" id="divTags">
                        <label class="form-label">Spesifikasi Fasilitas Utama</label>
                        <select name="facility_tag" id="facility_tag" class="form-select select2"
                            data-placeholder="Pilih Fasilitas">
                            <option value=""></option>
                            @foreach ($tags as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Pilih 'Umum' jika tidak ada alat khusus.</div>
                    </div> --}}
                    <div class="mb-3" id="divTags">
                        <label class="form-label">Fasilitas Utama (Bisa pilih banyak)</label>
                        <select name="facility_tags[]" id="facility_tags" class="form-select select2"
                            multiple="multiple" data-placeholder="Pilih Fasilitas...">
                            @foreach ($tags as $key => $label)
                                {{-- Sembunyikan 'general' agar user fokus ke alat khusus --}}
                                @if ($key !== 'general')
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">Untuk Kelas Teori standar, kosongkan saja (Otomatis set 'General').</div>
                    </div>
                    <button type="submit" class="btn btn-primary me-3" id="saveBtn">Simpan Data</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Batal</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-script')


    <script type="module">
        // Fungsi inisialisasi
        const initSelect2 = () => {
            // Cek apakah jQuery dan Select2 sudah siap
            if (typeof $ !== 'undefined' && $.fn.select2) {

                // Targetkan select2 di dalam Offcanvas secara spesifik
                $('#offcanvasAddRoom .select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: true,
                        dropdownParent: $('#offcanvasAddRoom'), // <--- INI KUNCINYA
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
        const offcanvasElement = document.getElementById('offcanvasAddRoom');
        offcanvasElement.addEventListener('shown.bs.offcanvas', function() {
            initSelect2();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addNewRoomForm');
            const offcanvasEl = document.getElementById('offcanvasAddRoom');
            const offcanvasTitle = document.getElementById('offcanvasAddRoomLabel');
            const saveBtn = document.getElementById('saveBtn');
            const defaultAction = form.action;

            // Inisialisasi Popover Bootstrap
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    html: true,
                    sanitize: false // Penting agar HTML di dalam content tidak di-strip
                });
            });

            const typeSelect = document.getElementById('type');
            const tagsSelect = document.getElementById('facility_tags');

            function toggleTags() {
                const isTeori = typeSelect.value === 'teori';

                // Jika tipe Teori, otomatis set fasilitas ke 'general'
                if (isTeori) {
                    if (window.$) {
                        $(tagsSelect).val('general').trigger('change');
                    } else {
                        tagsSelect.value = 'general';
                    }
                }

                tagsSelect.disabled = isTeori;
                if (window.$) $(tagsSelect).prop('disabled', isTeori);
            }

            if (typeSelect && tagsSelect) {
                window.$ ? $(typeSelect).on('change', toggleTags) : typeSelect.addEventListener('change',
                    toggleTags);
            }

            // Event delegation untuk tombol edit
            document.body.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-record');
                if (editBtn) {
                    const d = editBtn.dataset;

                    offcanvasTitle.textContent = 'Edit Ruangan';
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

                    document.getElementById('code').value = d.code;
                    document.getElementById('add-name-room').value = d.name;
                    document.getElementById('capacity').value = d.capacity;
                    document.getElementById('floor').value = d.floor;

                    if (window.$) {
                        $('#location').val(d.location).trigger('change');
                        $('#building').val(d.building).trigger('change');
                        $('#type').val(d.type).trigger('change');

                        // 1. PARSING PRODI IDS (JSON)
                        try {
                            const prodiIds = JSON.parse(d.prodiIds);
                            $('#prodis').val(prodiIds).trigger('change');
                        } catch (e) {
                            console.error('Error parse prodis', e);
                        }

                        // 2. PARSING FACILITY TAGS (JSON)
                        try {
                            const tags = JSON.parse(d.facilityTags);
                            $('#facility_tags').val(tags).trigger('change');
                        } catch (e) {
                            console.error('Error parse tags', e);
                            $('#facility_tags').val([]).trigger('change');
                        }
                    }
                }
            });

            // Reset form kembali ke Mode Create saat offcanvas ditutup
            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                offcanvasTitle.textContent = 'Tambah Program Studi';
                saveBtn.textContent = 'Submit';
                form.action = defaultAction;
                form.reset();

                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();

                if (window.$) {
                    $('.select2').val([]).trigger('change'); // Reset semua select2
                    $('#location').val('kampus_1').trigger('change'); // Default value
                    $('#floor').val('1');

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
@endsection

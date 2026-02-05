@extends('layouts/contentNavbarLayout')
@section('title', 'Jadwal Mengajar Saya')

@section('content')

    @if (!$activePeriod)
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class='bx bx-error-circle me-2'></i> Tidak ada periode akademik yang aktif saat ini.
        </div>
    @else
        {{-- Filter Section --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-4">
                        <div>
                            <h4 class="fw-bold mb-1 ms-2"> Jadwal Mengajar
                            </h4>
                        </div>
                        <div>
                            <span class="badge bg-label-primary fs-6 rounded-pill">
                                <i class='bx bx-calendar me-1'></i> Periode: {{ $activePeriod->name ?? 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <form action="{{ route('dashboard.jadwal-saya') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-bold small text-uppercase">Cari Matkul / Kelas</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input type="text" name="q" class="form-control"
                                        placeholder="Contoh: Pemrograman Web..." value="{{ request('q') }}">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold small text-uppercase">Filter Prodi</label>
                                <select name="prodi_id" class="form-select select2">
                                    <option value="">-- Semua Prodi --</option>
                                    @foreach ($prodis as $prodi)
                                        <option value="{{ $prodi->id }}"
                                            {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                            {{ $prodi->name }} ({{ $prodi->jenjang }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class='bx bx-filter-alt me-1'></i>
                                    Filter</button>
                            </div>
                            @if (request()->has('q') || request()->has('prodi_id'))
                                <div class="col-md-2">
                                    <a href="{{ route('dashboard.jadwal-saya') }}"
                                        class="btn btn-label-secondary w-100">Reset</a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @php
                $daysIndo = [
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                ];
            @endphp

            @forelse($daysIndo as $engDay => $indoDay)
                @if (isset($groupedSchedules[$engDay]) && $groupedSchedules[$engDay]->count() > 0)
                    <div class="col-md-6 col-xl-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm"
                            style="background: linear-gradient(to bottom right, #ffffff, #f8f9fa);">
                            <div
                                class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary"><i
                                                class='bx bx-calendar'></i></span>
                                    </div>
                                    <h5 class="mb-0 fw-bold text-primary">{{ $indoDay }}</h5>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $groupedSchedules[$engDay]->count() }}
                                    Sesi</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    @foreach ($groupedSchedules[$engDay] as $schedule)
                                        @php
                                            $slots = \App\Models\TimeSlots::whereIn('id', $schedule->time_slot_ids)
                                                ->orderBy('start_time')
                                                ->get();
                                            $start = $slots->first()->start_time ?? '-';
                                            $end = $slots->last()->end_time ?? '-';
                                            $startTime = substr($start, 0, 5);
                                            $endTime = substr($end, 0, 5);
                                        @endphp
                                        <div class="list-group-item p-3 border-bottom-dashed">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="badge bg-label-info d-flex align-items-center">
                                                    <i class='bx bx-time me-1'></i> {{ $startTime }} -
                                                    {{ $endTime }}
                                                </span>
                                                <span class="badge bg-label-secondary" title="Ruangan">
                                                    <i class='bx bx-map-pin me-1'></i> {{ $schedule->room->name }}
                                                </span>
                                            </div>

                                            <h6 class="mb-1 fw-bold text-dark">{{ $schedule->course->name }}</h6>
                                            <div class="d-flex justify-content-between align-items-end">
                                                <div>
                                                    <small class="text-muted d-block mb-1">
                                                        <i class='bx bx-group me-1'></i> Kelas: <span
                                                            class="fw-semibold">{{ $schedule->studyClass->full_name }}</span>
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class='bx bx-buildings me-1'></i>
                                                        {{ $schedule->studyClass->prodi->name ?? '-' }}
                                                    </small>
                                                    <small class="text-muted d-block mt-1">
                                                        <i class='bx bx-location me-1'></i> Tempat :
                                                        {{ $schedule->room->location ?? '-' }} -
                                                        {{ $schedule->room->building ?? '-' }} - Lantai
                                                        {{ $schedule->room->floor ?? '-' }}
                                                    </small>

                                                    {{-- PIC Info if exists --}}
                                                    @if ($schedule->studyClass->pic_name)
                                                        @php
                                                            $waNum = preg_replace(
                                                                '/[^0-9]/',
                                                                '',
                                                                $schedule->studyClass->pic_contact ?? '',
                                                            );
                                                            if (substr($waNum, 0, 1) === '0') {
                                                                $waNum = '62' . substr($waNum, 1);
                                                            }
                                                        @endphp
                                                        <small class="text-success d-block mt-1">
                                                            <i class='bx bx-user-check me-1'></i> PIC:
                                                            @if (!empty($waNum))
                                                                <a href="https://wa.me/{{ $waNum }}" target="_blank"
                                                                    class="text-success text-decoration-underline fw-bold"
                                                                    data-bs-toggle="tooltip" data-bs-offset="0,6"
                                                                    data-bs-placement="bottom" data-bs-html="true"
                                                                    title="Chat via Whatsapp">
                                                                    {{ $schedule->studyClass->pic_name }} <i
                                                                        class='bx bxl-whatsapp'></i>
                                                                </a>
                                                            @else
                                                                {{ $schedule->studyClass->pic_name }}
                                                            @endif
                                                        </small>
                                                    @endif
                                                </div>

                                                {{-- Action Button for PIC --}}
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary btn-icon rounded-pill"
                                                    data-bs-toggle="modal" data-bs-target="#modalPic"
                                                    data-class-id="{{ $schedule->studyClass->id }}"
                                                    data-class-name="{{ $schedule->studyClass->full_name }}"
                                                    data-pic-name="{{ $schedule->studyClass->pic_name ?? '' }}"
                                                    data-pic-contact="{{ $schedule->studyClass->pic_contact ?? '' }}"
                                                    title="Atur PIC Mahasiswa">
                                                    <i class='bx bx-user-voice'></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-12">
                    <div class="card shadow-none bg-transparent border border-dashed">
                        <div class="card-body text-center p-5">
                            <div class="mb-3">
                                <span class="badge bg-label-secondary p-3 rounded-circle">
                                    <i class='bx bx-calendar-x fs-1'></i>
                                </span>
                            </div>
                            <h4 class="mb-1">Jadwal Kosong</h4>
                            <p class="text-muted">Tidak ada jadwal mengajar yang ditemukan untuk filter ini.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    {{-- Modal PIC --}}
    <div class="modal fade" id="modalPic" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPicTitle">Atur PIC Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('dashboard.jadwal-saya.pic') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="study_class_id" id="picClassId">
                        <div class="alert alert-info py-2 mb-3">
                            <small><i class='bx bx-info-circle me-1'></i> Mengatur PIC untuk Kelas: <strong
                                    id="picClassName"></strong></small>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="picName" class="form-label">Nama Mahasiswa (PIC)</label>
                                <input type="text" id="picName" name="pic_name" class="form-control"
                                    placeholder="Masukkan Nama Lengkap" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-0">
                                <label for="picContact" class="form-label">Kontak / No. WA</label>
                                <input type="text" id="picContact" name="pic_contact" class="form-control"
                                    placeholder="0812..." required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan PIC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('page-script')
    <script type="module">
        const initSelect2 = () => {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').each(function() {
                    const $this = $(this);
                    $this.select2({
                        placeholder: $this.data('placeholder') || "Pilih...",
                        allowClear: $this.find('option[value=""]').length >
                            0,
                        width: '100%',
                        minimumResultsForSearch: 10
                    });
                });
            } else {
                setTimeout(initSelect2, 100);
            }
        };
        initSelect2();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-target="#modalPic"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            var modalPic = document.getElementById('modalPic');
            modalPic.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var classId = button.getAttribute('data-class-id');
                var className = button.getAttribute('data-class-name');
                var picName = button.getAttribute('data-pic-name');
                var picContact = button.getAttribute('data-pic-contact');

                var inputId = modalPic.querySelector('#picClassId');
                var labelName = modalPic.querySelector('#picClassName');
                var inputName = modalPic.querySelector('#picName');
                var inputContact = modalPic.querySelector('#picContact');

                inputId.value = classId;
                labelName.textContent = className;
                inputName.value = picName;
                inputContact.value = picContact;
            });
        });
    </script>
@endsection

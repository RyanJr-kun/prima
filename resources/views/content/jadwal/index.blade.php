@extends('layouts/contentNavbarLayout')
@section('title', 'Jadwal Perkuliahan - PRIMA')

@section('page-style')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js'></script>
@endsection

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

        <div id="jsToast" class="bs-toast toast fade hide" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="icon-base bx bx-bell icon-xs me-2"></i>
                <span class="fw-medium me-auto" id="jsToastTitle">Notifikasi</span>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="jsToastBody"></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('jadwal-perkuliahan.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Kampus</label>
                                <select name="campus" class="form-select select2" onchange="this.form.submit()">
                                    <option value="kampus_1" {{ $campus == 'kampus_1' ? 'selected' : '' }}>Kampus 1</option>
                                    <option value="kampus_2" {{ $campus == 'kampus_2' ? 'selected' : '' }}>Kampus 2</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Shift</label>
                                <select name="shift" class="form-select select2" onchange="this.form.submit()">
                                    <option value="pagi" {{ $shift == 'pagi' ? 'selected' : '' }}>Pagi</option>
                                    <option value="malam" {{ $shift == 'malam' ? 'selected' : '' }}>Malam</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Filter Prodi</label>
                                <select name="prodi_id" class="form-select select2" onchange="this.form.submit()">
                                    <option value="">-- Semua Prodi --</option>
                                    @foreach ($prodis as $prodi)
                                        <option value="{{ $prodi->id }}" {{ $prodiId == $prodi->id ? 'selected' : '' }}>
                                            {{ $prodi->jenjang }} {{ $prodi->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="d-flex align-items-center gap-2">
                        @if ($campus && $shift)
                            @if ($document)

                                <span class="badge bg-label-{{ $document->status_color }}">
                                    {{ strtoupper($document->status_text) }}
                                </span>

                                @if (in_array($document->status, ['draft', 'rejected']))
                                    <form id="resubmitScheduleForm" action="{{ route('jadwal-perkuliahan.submit') }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="campus" value="{{ $campus }}">
                                        <input type="hidden" name="shift" value="{{ $shift }}">
                                        <button type="button" id="btnResubmitSchedule" class="btn btn-sm btn-primary ms-2">
                                            <i class='bx bx-send'></i> Ajukan Validasi
                                        </button>
                                    </form>
                                @endif

                                @if ($document->status == 'rejected' && $document->feedback_message)
                                    <small class="text-danger fw-bold ms-2">
                                        <i class='bx bx-error-circle'></i> Revisi: {{ $document->feedback_message }}
                                    </small>
                                @endif
                            @else
                                <span class="badge bg-label-secondary">DRAFT</span>
                                <form id="submitScheduleForm" action="{{ route('jadwal-perkuliahan.submit') }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="campus" value="{{ $campus }}">
                                    <input type="hidden" name="shift" value="{{ $shift }}">

                                    <button type="button" class="btn btn-primary" id="btnSubmitSchedule">
                                        <i class='bx bx-send'></i> Ajukan Validasi
                                    </button>
                                </form>
                            @endif
                        @else
                            <span class="text-muted small fst-italic">Pilih Kampus dan Shift terlebih dahulu.</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body m-0 p-4">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-0 fw-bold">Buat Jadwal Otomatis</h5>
                    <small>Kelola dan sesuaikan jadwal disini.</small>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-center">
                        @if ($campus && $shift && (!$document || in_array($document->status, ['draft', 'rejected'])))
                            <form action="{{ route('jadwal-perkuliahan.auto-generate') }}" method="POST"
                                id="autoGenerateForm">
                                @csrf
                                <input type="hidden" name="campus" value="{{ $campus }}">
                                <input type="hidden" name="shift" value="{{ $shift }}">
                                {{-- <input type="hidden" name="prodi_id" value="{{ $prodiId }}"> --}}
                                <button type="button" class="btn btn-outline-warning" id="btnAuto">
                                    <i class='bx bx-bot me-1'></i> Generate
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white p-3">
                    <h6 class="mb-0 text-white"><i class='bx bx-list-ul'></i> Belum Terjadwal</h6>
                </div>
                <div class="card-body p-2" style="overflow-y: auto; max-height: 80vh;" id="external-events">
                    <p class="text-muted small mb-2">Drag matkul ke kalender:</p>

                    {{-- UBAH VARIABEL LOOPING --}}
                    @forelse($unscheduledDistributions as $dist)
                        @php
                            $lecturers = $dist->teachingLecturers;
                            $primaryLecturerId = $lecturers->first()->id ?? null;
                            $dosenNames = $lecturers->pluck('name')->join(', ');
                            $sks = $dist->course->sks_total;

                            if ($sks > 1) {
                                $effectiveSks = $sks - 1;
                            } else {
                                $effectiveSks = 1; // Minimal 1 sesi tatap muka
                            }
                            $durationMinutes = $effectiveSks * 50;

                            $hours = floor($durationMinutes / 60);
                            $minutes = $durationMinutes % 60;
                            $durationString = sprintf('%02d:%02d', $hours, $minutes);
                        @endphp

                        <div class="fc-event card mb-2 shadow-sm cursor-move"
                            style="border-left: 4px solid {{ $dist->needs_lab ? '#ff3e1d' : '#696cff' }}; cursor: grab;"
                            data-distribution-id="{{ $dist->id }}" data-course-id="{{ $dist->course_id }}"
                            data-class-id="{{ $dist->study_class_id }}" data-lecturer-id="{{ $primaryLecturerId }}"
                            data-duration="{{ $durationString }}" data-title="{{ $dist->course->name }}"
                            data-class-name="{{ $dist->studyClass->name }}" data-dosen="{{ $dosenNames }}">

                            <div class="card-body p-2">
                                <h6 class="mb-1 text-primary">{{ $dist->course->code }} - {{ $dist->course->name }}</h6>
                                {{-- TAMPILAN TAGS BARU (BADGES) --}}
                                <div class="text-start">
                                    @if (empty($dist->formatted_tags))
                                        {{-- Jika tidak ada tags, anggap General --}}
                                        <span class="badge bg-label-secondary" style="font-size: 0.7rem;">Umum</span>
                                    @else
                                        @foreach ($dist->formatted_tags as $tag)
                                            <span class="badge bg-label-{{ $tag['color'] }} bg-glow mb-1"
                                                style="font-size: 0.7rem;">
                                                {{ $tag['label'] }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    <i class='bx bx-group'></i> {{ $dist->studyClass->name }}<br>
                                    <i class='bx bx-time'></i> {{ $sks }} SKS ({{ $durationMinutes }} mnt)<br>
                                    <i class='bx {{ $lecturers->count() > 1 ? 'bx-group' : 'bx-user' }}'></i>
                                    <span class="text-truncate d-inline-block"
                                        style="max-width: 150px; vertical-align: bottom;">
                                        {{ $dosenNames }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-3 text-muted">
                            <i class='bx bx-check-circle fs-1'></i><br>
                            Semua mata kuliah telah didistribusikan!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h5 class="mb-0">Plotting Jadwal</h5>
                    <span class="badge bg-label-info" id="loadingIndicator" style="display:none;">Menyimpan...</span>
                </div>
                <div class="card-body p-2">
                    <div id='calendar'></div>
                </div>
            </div>
        </div>
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
    <script>
        // Helper Toast
        function showJsToast(type, message) {
            const toastEl = document.getElementById('jsToast');
            const toastBody = document.getElementById('jsToastBody');
            const toastTitle = document.getElementById('jsToastTitle');

            if (!toastEl) return; // Safety check

            toastEl.classList.remove('bg-primary', 'bg-danger');
            if (type === 'success') {
                toastEl.classList.add('bg-primary');
                toastTitle.textContent = 'Berhasil';
            } else {
                toastEl.classList.add('bg-danger');
                toastTitle.textContent = 'Gagal';
            }
            toastBody.textContent = message;
            new bootstrap.Toast(toastEl).show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var containerEl = document.getElementById('external-events');

            // 1. Inisialisasi Draggable (Sidebar)
            if (containerEl) {
                new FullCalendar.Draggable(containerEl, {
                    itemSelector: '.fc-event',
                    eventData: function(eventEl) {
                        // Safe Parsing JSON
                        var lecturersRaw = eventEl.getAttribute('data-lecturers');
                        var lecturers = [];
                        try {
                            lecturers = JSON.parse(lecturersRaw) || [];
                        } catch (e) {
                            console.error("Gagal parse lecturers JSON", e);
                        }

                        return {
                            title: eventEl.getAttribute('data-title'),
                            duration: eventEl.getAttribute('data-duration'),
                            extendedProps: {
                                distributionId: eventEl.getAttribute('data-distribution-id'),
                                courseId: eventEl.getAttribute('data-course-id'),
                                classId: eventEl.getAttribute('data-class-id'),
                                lecturers: lecturers, // Array Dosen
                                lecturerId: eventEl.getAttribute(
                                    'data-lecturer-id'), // Primary ID (Backup)
                                dosenName: eventEl.getAttribute('data-dosen-text'),
                                className: eventEl.getAttribute('data-class-name')
                            }
                        };
                    }
                });
            }

            // 2. Inisialisasi Kalender
            var calendar = new FullCalendar.Calendar(calendarEl, {
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                initialView: 'resourceTimelineDay',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'resourceTimelineDay,resourceTimeGridDay'
                },

                // Pastikan Resources di-encode dengan benar
                // Jika resources kosong, berikan array kosong agar tidak error
                resources: {!! !empty($resources) ? json_encode($resources) : '[]' !!},

                // Gunakan Route Laravel untuk events agar URL absolut & aman
                events: "{{ route('jadwal-perkuliahan.get-events') }}",
                titleFormat: {
                    weekday: 'long' // Ini yang memunculkan "Senin", "Selasa", dst.
                },

                locale: 'id',
                slotMinTime: '08:00:00',
                slotMaxTime: '21:00:00',
                slotLabelInterval: '00:50:00',
                slotDuration: '00:10:00', // Granularitas 10 menit
                editable: true,
                droppable: true,
                eventDurationEditable: true,

                // --- EVENT: TERIMA DROP DARI SIDEBAR ---
                eventReceive: function(info) {
                    var props = info.event.extendedProps;
                    var lecturers = props.lecturers || [];

                    // Jika dosen > 1, Tampilkan Modal Pilih Dosen
                    if (lecturers.length > 1) {
                        let optionsHtml = '';
                        lecturers.forEach(l => {
                            optionsHtml += `
                                <div class="form-check text-start">
                                    <input class="form-check-input" type="radio" name="selected_lecturer" value="${l.id}" id="lec_${l.id}" checked>
                                    <label class="form-check-label" for="lec_${l.id}">${l.name}</label>
                                </div>`;
                        });

                        Swal.fire({
                            title: 'Pilih Pengajar Sesi Ini',
                            html: `<div class="p-2">${optionsHtml}</div>`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Simpan',
                            preConfirm: () => {
                                const selected = document.querySelector(
                                    'input[name="selected_lecturer"]:checked');
                                return selected ? selected.value : null;
                            }
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                saveEventToDB(info, result.value);
                            } else {
                                info.revert(); // Batal Drop
                            }
                        });
                    } else {
                        // Jika cuma 1 dosen (atau 0), langsung simpan pakai ID default
                        var defaultId = props.lecturerId || (lecturers[0] ? lecturers[0].id : null);
                        saveEventToDB(info, defaultId);
                    }
                },

                // --- EVENT: PINDAH JADWAL (DRAG DI KALENDER) ---
                eventDrop: function(info) {
                    updateEventInDB(info);
                },

                // --- EVENT: RESIZE DURASI ---
                eventResize: function(info) {
                    resizeEventInDB(info);
                },

                // --- EVENT: KLIK EVENT (DETAIL/HAPUS) ---
                eventClick: function(info) {
                    showEventDetail(info);
                }
            });

            calendar.render();

            // ==========================================
            // FUNGSI AJAX HELPER (DIPISAH AGAR RAPI)
            // ==========================================

            function saveEventToDB(info, userId) {
                var props = info.event.extendedProps;
                var resourceId = info.event.getResources()[0].id;

                showLoading(true);

                fetch("{{ route('jadwal-perkuliahan.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            course_distribution_id: props.distributionId,
                            course_id: props.courseId,
                            study_class_id: props.classId,
                            room_id: resourceId,
                            start_time: info.event.startStr,
                            user_id: userId // Single ID sesuai request Anda
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        showLoading(false);
                        if (data.success) {
                            info.event.setProp('id', data.schedule_id); // Update ID Event Real dari DB

                            // Hapus dari sidebar jika sukses
                            if (info.draggedEl && info.draggedEl.parentNode) {
                                info.draggedEl.parentNode.removeChild(info.draggedEl);
                            }
                            showJsToast('success', 'Jadwal tersimpan!');
                        } else {
                            info.revert();
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        showLoading(false);
                        info.revert();
                        console.error(err);
                        showJsToast('error', 'Terjadi kesalahan server.');
                    });
            }

            function updateEventInDB(info) {
                var scheduleId = info.event.id;
                // GENERATE URL UPDATE YANG BENAR (SAFE URL)
                var url = "{{ route('jadwal-perkuliahan.update', ':id') }}".replace(':id', scheduleId);

                showLoading(true);

                fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            room_id: info.event.getResources()[0].id,
                            start_time: info.event.startStr
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        showLoading(false);
                        if (data.success) {
                            showJsToast('success', 'Jadwal dipindahkan!');
                        } else {
                            info.revert();
                            showJsToast('error', data.message);
                        }
                    })
                    .catch(err => {
                        showLoading(false);
                        info.revert();
                        showJsToast('error', 'Gagal update jadwal.');
                    });
            }

            function resizeEventInDB(info) {
                var scheduleId = info.event.id;
                // GENERATE URL RESIZE (Pastikan route ini ada di web.php)
                // Route::patch('/jadwal-perkuliahan/{id}/resize', ...)
                var url = "/jadwal-perkuliahan/" + scheduleId + "/resize";

                showLoading(true);

                fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            start_time: info.event.startStr,
                            end_time: info.event.endStr
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        showLoading(false);
                        if (data.success) {
                            showJsToast('success', 'Durasi diupdate!');
                        } else {
                            info.revert();
                            showJsToast('error', data.message);
                        }
                    })
                    .catch(err => {
                        showLoading(false);
                        info.revert();
                        showJsToast('error', 'Gagal resize.');
                    });
            }

            function showEventDetail(info) {
                var eventObj = info.event;
                var props = eventObj.extendedProps;
                var scheduleId = eventObj.id;
                var content = `
                    <div class="text-start fs-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="fw-bold" width="30%">Matkul</td><td>: ${props.courseName}</td></tr>
                            <tr><td class="fw-bold">Kelas</td><td>: ${props.fullClassName}</td></tr>
                            <tr><td class="fw-bold">Dosen</td><td>: ${props.dosenName}</td></tr>
                            <tr><td class="fw-bold">Waktu</td><td>: ${props.jam_mulai} - ${props.jam_selesai}</td></tr>
                            <tr><td class="fw-bold">Lokasi</td><td>: ${props.location}</td></tr>
                        </table>
                    </div>
                `;

                Swal.fire({
                    title: 'Detail Jadwal',
                    html: content,
                    icon: 'info',
                    showDenyButton: true, // Tombol Hapus
                    showCancelButton: true, // Tombol Ganti Dosen
                    confirmButtonText: 'Tutup',
                    denyButtonText: 'Hapus Jadwal',
                    cancelButtonText: 'Ganti Pengajar', // <--- UBAH TEXT INI
                    denyButtonColor: '#ff3e1d',
                    cancelButtonColor: '#ff9f43', // Warna Kuning/Orange
                    customClass: {
                        title: 'my-0 py-0',
                        htmlContainer: 'py-0 my-0 fs-7',
                        confirmButton: 'btn btn-sm btn-primary me-3',
                        cancelButton: 'btn btn-sm btn-secondary',
                        denyButton: 'btn btn-sm btn-danger'
                    },
                }).then((result) => {
                    if (result.isDenied) {
                        // Hapus Jadwal
                        deleteSchedule(scheduleId);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // Ganti Pengajar Diklik
                        handleChangeLecturer(scheduleId, props);
                    }
                });
            }

            function deleteSchedule(id) {
                Swal.fire({
                    title: 'Hapus Jadwal?',
                    text: 'Jadwal akan dikembalikan ke antrean.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus'
                }).then((res) => {
                    if (res.isConfirmed) {
                        var url = "{{ route('jadwal-perkuliahan.destroy', ':id') }}".replace(':id', id);

                        showLoading(true);
                        fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.json())
                            .then(d => {
                                if (d.success) {
                                    showJsToast('success', 'Terhapus!');
                                    setTimeout(() => location.reload(),
                                        5000); // Reload untuk refresh sidebar
                                } else {
                                    showJsToast('error', d.message);
                                }
                            });
                    }
                });
            }

            function showLoading(show) {
                const el = document.getElementById('loadingIndicator');
                if (el) el.style.display = show ? 'block' : 'none';
            }

            function handleChangeLecturer(scheduleId, props) {
                var team = props.teamTeaching;

                // Cek jika data team teaching kosong atau cuma 1
                if (!team || team.length <= 1) {
                    Swal.fire('Info', 'Mata kuliah ini hanya memiliki satu pengajar.', 'info');
                    return;
                }

                // 1. GENERATE HTML CUSTOM (User Card Style)
                let optionsHtml = '<div class="d-flex flex-column gap-3 text-start">';

                team.forEach(t => {
                    // Cek apakah ini dosen yang sedang aktif (untuk default checked)
                    const isChecked = t.id == props.lecturerId ? 'checked' : '';

                    // Style awal (Active vs Inactive)
                    const wrapperClass = t.id == props.lecturerId ?
                        'border-primary bg-label-primary shadow-sm' :
                        'border-light bg-white';

                    optionsHtml += `
            <label class="cursor-pointer card-selection-wrapper position-relative w-100">
                <input type="radio" class="btn-check" name="swal_lecturer_id" id="lec_${t.id}" value="${t.id}" ${isChecked}>
                
                <div class="card-selection-content p-3 border rounded-3 d-flex align-items-center transition-all ${wrapperClass}">
                    <div class="avatar avatar-md me-3">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            <i class='bx bx-user fs-4'></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <span class="d-block fw-semibold text-heading mb-0">${t.name}</span>
                        <small class="text-muted d-block mt-1">
                            <i class='bx bx-id-card me-1'></i> NIDN: ${t.nidn}
                        </small>
                    </div>
                    <div class="selection-indicator">
                        ${isChecked ? "<i class='bx bxs-check-circle text-primary fs-4'></i>" : "<i class='bx bx-circle text-muted fs-4'></i>"}
                    </div>
                </div>
            </label>
        `;
                });
                optionsHtml += '</div>';

                // 2. TAMPILKAN SWEETALERT
                Swal.fire({
                    title: 'Pilih Dosen Pengajar',
                    html: optionsHtml, // Render HTML Custom di sini
                    showCancelButton: true,
                    confirmButtonText: 'Simpan Perubahan',
                    cancelButtonText: 'Batal',

                    customClass: {
                        title: 'fs-4 fw-bold mb-4',
                        htmlContainer: 'overflow-hidden m-0 p-0 text-start',
                        popup: 'p-4 rounded-4 w-md-500px',
                        confirmButton: 'btn btn-sm btn-primary px-4',
                        cancelButton: 'btn btn-sm btn-secondary px-4'
                    },

                    // --- LOGIKA VALIDASI & AMBIL DATA (PENGGANTI inputValidator) ---
                    preConfirm: () => {
                        const selected = document.querySelector(
                            'input[name="swal_lecturer_id"]:checked');
                        if (!selected) {
                            Swal.showValidationMessage('Silakan pilih salah satu dosen!');
                            return false;
                        }
                        return selected.value; // Return ID Dosen
                    },

                    // --- LOGIKA UI (HIGHLIGHT CARD SAAT DIKLIK) ---
                    didOpen: () => {
                        const container = Swal.getHtmlContainer();
                        const inputs = container.querySelectorAll('input[type="radio"]');

                        inputs.forEach(input => {
                            input.addEventListener('change', function() {
                                // Reset semua card ke tampilan default (putih)
                                container.querySelectorAll('.card-selection-content')
                                    .forEach(el => {
                                        el.classList.remove('border-primary',
                                            'bg-label-primary', 'shadow-sm');
                                        el.classList.add('border-light',
                                            'bg-white');
                                        // Reset icon kanan jadi lingkaran kosong
                                        el.querySelector('.selection-indicator')
                                            .innerHTML =
                                            "<i class='bx bx-circle text-muted fs-4'></i>";
                                    });

                                // Highlight card yang dipilih (biru)
                                const selectedWrapper = this
                                    .nextElementSibling; // div.card-selection-content
                                selectedWrapper.classList.remove('border-light',
                                    'bg-white');
                                selectedWrapper.classList.add('border-primary',
                                    'bg-label-primary', 'shadow-sm');
                                // Ubah icon kanan jadi checkmark
                                selectedWrapper.querySelector('.selection-indicator')
                                    .innerHTML =
                                    "<i class='bx bxs-check-circle text-primary fs-4'></i>";
                            });
                        });
                    }

                }).then((res) => {
                    // 3. PROSES SIMPAN (AJAX)
                    if (res.isConfirmed) {
                        var newUserId = res.value;
                        var url = "{{ route('jadwal-perkuliahan.update', ':id') }}".replace(':id',
                            scheduleId);

                        document.getElementById('loadingIndicator').style.display = 'block';

                        fetch(url, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content
                                },
                                body: JSON.stringify({
                                    user_id: newUserId
                                })
                            })
                            .then(r => r.json())
                            .then(d => {
                                document.getElementById('loadingIndicator').style.display = 'none';
                                if (d.success) {
                                    showJsToast('success', 'Pengajar Berhasil Diganti!');
                                    setTimeout(() => location.reload(),
                                        5000);
                                } else {
                                    Swal.fire('Gagal', d.message, 'error');
                                }
                            })
                            .catch(e => {
                                document.getElementById('loadingIndicator').style.display = 'none';
                                console.error(e);
                                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                            });
                    }
                });
            }
        });
    </script>
    <script>
        // Fungsi Helper untuk menampilkan Toast dari JS
        function showJsToast(type, message) {
            const toastEl = document.getElementById('jsToast');
            const toastTitle = document.getElementById('jsToastTitle');
            const toastBody = document.getElementById('jsToastBody');

            // Reset kelas warna
            toastEl.classList.remove('bg-primary', 'bg-danger');

            if (type === 'success') {
                toastEl.classList.add('bg-primary');
                toastTitle.textContent = 'Berhasil';
            } else {
                toastEl.classList.add('bg-danger');
                toastTitle.textContent = 'Error';
            }

            toastBody.textContent = message;
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const autoGenerateForm = document.getElementById('autoGenerateForm');
            const autoGenerateButton = document.getElementById('btnAuto');

            autoGenerateButton.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default form submission

                Swal.fire({
                    title: 'Konfirmasi Auto Generate',
                    text: 'Fitur ini akan mengisi jadwal secara otomatis untuk matkul yang BELUM terjadwal',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    customClass: {
                        title: 'fs-4 fw-bold mb-4',
                        htmlContainer: 'overflow-hidden m-0 p-0',
                        popup: 'p-4 rounded-4 w-md-500px',
                        confirmButton: 'btn btn-sm btn-primary px-4',
                        cancelButton: 'btn btn-sm btn-secondary px-4'
                    },
                    preConfirm: () => {
                        return new Promise((resolve) => {
                            autoGenerateForm.submit();
                            resolve();
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });
            });

            // Fungsi Helper untuk Konfirmasi Submit
            const setupSubmitConfirmation = (btnId, formId, title, text) => {
                const btn = document.getElementById(btnId);
                const form = document.getElementById(formId);

                if (btn && form) {
                    btn.addEventListener('click', function() {
                        Swal.fire({
                            title: title,
                            text: text,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Ajukan',
                            cancelButtonText: 'Batal',
                            customClass: {
                                title: 'my-0 py-0',
                                htmlContainer: 'py-0 my-0 fs-7',
                                confirmButton: 'btn btn-sm btn-primary me-3',
                                cancelButton: 'btn btn-sm btn-secondary'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) form.submit();
                        });
                    });
                }
            };

            // Terapkan konfirmasi pada tombol Ajukan (Baru) & Ajukan Validasi (Revisi)
            setupSubmitConfirmation('btnSubmitSchedule', 'submitScheduleForm', 'Ajukan Jadwal?',
                'Pastikan jadwal sudah final dan siap diajukan.');
            setupSubmitConfirmation('btnResubmitSchedule', 'resubmitScheduleForm', 'Ajukan Validasi?',
                'Jadwal revisi akan dikirim untuk divalidasi ulang.');

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

@extends('layouts/contentNavbarLayout')
@section('title', 'Jadwal Perkuliahan - PRIMA')

@section('page-style')
    {{-- 1. Load CSS FullCalendar Scheduler --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js'></script>
    <style>
        /* Mengatur tinggi kalender agar pas di layar */
        #calendar {
            max-width: 100%;
            margin: 0 auto;
            height: 80vh;
            background: white;
            padding: 10px;
            border-radius: 8px;
        }


        /* Styling Event di dalam kalender */
        .fc-event {
            cursor: pointer;
            font-size: 0.85rem;
        }

        /* Warna latar header jam/ruangan */
        .fc-timeline-slot-cushion,
        .fc-resource-area-header {
            background-color: #f5f5f9;
            color: #566a7f;
        }
    </style>
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
        <div class="card-body p-3 d-flex justify-content-between align-items-center">
            <form method="GET" action="{{ route('jadwal-perkuliahan.index') }}" class="d-flex gap-2 w-100">
                <div class="grow">
                    <label class="form-label small mb-1">Kampus</label>
                    <select name="campus" class="form-select form-select-sm select2" onchange="this.form.submit()">
                        <option value="kampus_1" {{ $campus == 'kampus_1' ? 'selected' : '' }}>Kampus 1</option>
                        <option value="kampus_2" {{ $campus == 'kampus_2' ? 'selected' : '' }}>Kampus 2</option>
                    </select>
                </div>
                <div class="grow">
                    <label class="form-label small mb-1">Shift</label>
                    <select name="shift" class="form-select form-select-sm select2" onchange="this.form.submit()">
                        <option value="pagi" {{ $shift == 'pagi' ? 'selected' : '' }}>Pagi</option>
                        <option value="malam" {{ $shift == 'malam' ? 'selected' : '' }}>Malam</option>
                    </select>
                </div>
                <div class="grow">
                    <label class="form-label small mb-1">Filter Prodi</label>
                    <select name="prodi_id" class="form-select form-select-sm select2" onchange="this.form.submit()">
                        <option value="">-- Semua Prodi --</option>
                        @foreach ($prodis as $prodi)
                            <option value="{{ $prodi->id }}" {{ $prodiId == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->jenjang }} {{ $prodi->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            {{-- STATUS & TOMBOL ACTION --}}
            <div class="d-flex align-items-center gap-2">
                {{-- Cek Kampus & Shift (Logic Baru) --}}
                @if ($campus && $shift)

                    @if ($document)
                        {{-- Badge Status Dokumen --}}
                        <span class="badge bg-label-{{ $document->status_color }}">
                            {{ strtoupper($document->status_text) }}
                        </span>

                        {{-- Tombol Ajukan (Hanya muncul jika Draft/Rejected) --}}
                        @if (in_array($document->status, ['draft', 'rejected']))
                            <form action="{{ route('jadwal-perkuliahan.submit') }}" method="POST"
                                onsubmit="return confirm('Yakin jadwal {{ $campus }} shift {{ $shift }} sudah final dan siap diajukan?')">
                                @csrf
                                {{-- UPDATE: Kirim Kampus & Shift, bukan Prodi ID --}}
                                <input type="hidden" name="campus" value="{{ $campus }}">
                                <input type="hidden" name="shift" value="{{ $shift }}">

                                <button type="submit" class="btn btn-sm btn-primary ms-2">
                                    <i class='bx bx-send'></i> Ajukan Validasi
                                </button>
                            </form>
                        @endif

                        {{-- Pesan Revisi --}}
                        @if ($document->status == 'rejected' && $document->feedback_message)
                            <small class="text-danger fw-bold ms-2">
                                <i class='bx bx-error-circle'></i> Revisi: {{ $document->feedback_message }}
                            </small>
                        @endif
                    @else
                        {{-- KONDISI DRAFT (Belum ada dokumen sama sekali) --}}
                        <span class="badge bg-label-secondary">DRAFT</span>

                        <form id="submitScheduleForm" action="{{ route('jadwal-perkuliahan.submit') }}" method="POST">
                            @csrf
                            <input type="hidden" name="campus" value="{{ $campus }}">
                            <input type="hidden" name="shift" value="{{ $shift }}">

                            <button type="button" class="btn btn-primary" id="btnSubmitSchedule">
                                <i class='bx bx-send'></i> Ajukan
                            </button>
                        </form>
                    @endif
                @else
                    <span class="text-muted small fst-italic">Pilih Kampus dan Shift terlebih dahulu.</span>
                @endif
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
                                    <i class='bx bx-bot me-1'></i> Auto Generate
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
                            $sks = $dist->course->sks_total ?? 2;
                            $durationMinutes = $sks * 50;
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
                    text: 'PERINGATAN: Fitur ini akan mengisi jadwal secara otomatis untuk matkul yang BELUM terjadwal. Sistem akan mencari slot kosong berdasarkan SKS dan kapasitas ruangan. Lanjutkan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve) => {
                            autoGenerateForm.submit();
                            resolve();
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var containerEl = document.getElementById('external-events');

            new FullCalendar.Draggable(containerEl, {
                itemSelector: '.fc-event',
                eventData: function(eventEl) {
                    // Ambil data dari atribut HTML
                    return {
                        title: eventEl.querySelector('h6').innerText,
                        duration: eventEl.getAttribute('data-duration'), // Durasi otomatis sesuai SKS!
                        extendedProps: {
                            distributionId: eventEl.getAttribute('data-distribution-id'),
                            courseId: eventEl.getAttribute('data-course-id'),
                            classId: eventEl.getAttribute('data-class-id'),
                            lecturerId: eventEl.getAttribute('data-lecturer-id'),
                            dosenName: eventEl.getAttribute('data-dosen'),
                            className: eventEl.getAttribute('data-class-name')
                        }
                    };
                }
            });

            // var isReadOnly = @json($isReadOnly ?? true);
            // var canEdit = hasProdi && !isReadOnly;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                initialView: 'resourceTimelineDay',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'resourceTimelineDay,resourceTimeGridDay'
                },
                resources: {!! json_encode($resources) !!},
                events: "{{ route('jadwal-perkuliahan.get-events') }}",

                views: {
                    resourceTimeGridDay: {
                        dayMinWidth: 250
                    }
                },

                locale: 'id',

                titleFormat: {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    weekday: 'long'
                },

                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                    meridiem: false
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },


                slotMinTime: '08:00:00',
                slotMaxTime: '21:00:00',
                slotLabelInterval: '00:50:00',
                slotDuration: '00:10:00',

                editable: true,
                droppable: true,
                eventDurationEditable: true,

                eventReceive: function(info) {
                    var eventData = info.event;
                    var resourceId = info.event.getResources()[0].id;
                    var startTime = info.event.startStr;
                    var props = eventData.extendedProps;

                    document.getElementById('loadingIndicator').style.display = 'block';
                    fetch("{{ route('jadwal-perkuliahan.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                course_distribution_id: props.distributionId,
                                course_id: props.courseId,
                                study_class_id: props.classId,
                                user_id: props.lecturerId,
                                room_id: resourceId,
                                start_time: startTime
                            })
                        })
                        .then(response => response.json())

                        .then(data => {
                            document.getElementById('loadingIndicator').style.display = 'none';
                            if (data.success) {
                                info.draggedEl.parentNode.removeChild(info.draggedEl);
                                info.event.setProp('id', data.schedule_id);
                                showJsToast('success', 'Jadwal Berhasil Disimpan!');
                            } else {
                                info.revert();
                                showJsToast('error', 'Gagal: ' + data.message);
                            }
                        })
                        .catch(error => {
                            info.revert();
                            document.getElementById('loadingIndicator').style.display = 'none';
                            showJsToast('error', 'Terjadi kesalahan sistem.');
                            console.error(error);
                        });
                },

                eventDrop: function(info) {
                    //showJsToast('success', 'Jadwal dipindah ke ' + info.event.startStr);
                    var eventData = info.event;
                    var resourceId = info.event.getResources()[0].id;
                    var startTime = info.event.startStr;
                    var scheduleId = info.event.id; // ID Jadwal yang ada di database

                    document.getElementById('loadingIndicator').style.display = 'block';
                    fetch(`/jadwal-perkuliahan/${scheduleId}`, { // Gunakan template literal
                            method: 'PUT', // Atau PATCH, tergantung kebutuhan API Anda
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                room_id: resourceId,
                                start_time: startTime
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('loadingIndicator').style.display = 'none';
                            if (data.success) {
                                showJsToast('success', 'Jadwal Berhasil Dipindahkan!');
                            } else {
                                info.revert();
                                showJsToast('error', 'Gagal: ' + data.message);
                            }
                        })
                        .catch(error => {
                            info.revert();
                            document.getElementById('loadingIndicator').style.display = 'none';
                            showJsToast('error', 'Terjadi kesalahan sistem.');
                        });
                },

                eventResize: function(info) {
                    var scheduleId = info.event.id;
                    var newStart = info.event.startStr;
                    var newEnd = info.event.endStr;

                    // Tampilkan Loading
                    document.getElementById('loadingIndicator').style.display = 'block';

                    // Panggil API Resize
                    fetch(`/jadwal-perkuliahan/${scheduleId}/resize`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                start_time: newStart,
                                end_time: newEnd
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('loadingIndicator').style.display = 'none';
                            if (data.success) {
                                showJsToast('success', 'Durasi Berhasil Diupdate!');
                            } else {
                                info.revert(); // Balikkan ukuran jika gagal
                                showJsToast('error', data.message);
                            }
                        })
                        .catch(error => {
                            info.revert();
                            document.getElementById('loadingIndicator').style.display = 'none';
                            console.error(error);
                            showJsToast('error', 'Terjadi kesalahan saat resize.');
                        });
                },

                eventClick: function(info) {
                    var eventObj = info.event;
                    var props = eventObj.extendedProps;

                    // Tampilkan SweetAlert dengan Detail Lengkap
                    Swal.fire({
                        title: 'Detail Perkuliahan',
                        html: `
                        <div class="text-start">
                            <table class="table table-sm table-borderless fs-6">
                                <tr>
                                    <td class="fw-bold" style="width: 35%;">Mata Kuliah</td>
                                    <td>: ${props.courseName} <br><small class="text-muted">(${props.courseCode})</small></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Kelas</td>
                                    <td>: <span class="badge bg-label-primary">${props.fullClassName}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Dosen</td>
                                    <td>: ${props.dosenName}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Waktu</td>
                                    <td>: ${props.jam_mulai} - ${props.jam_selesai} WIB</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Durasi</td>
                                    <td>: ${props.durasi} (${props.sks} SKS)</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Lokasi</td>
                                    <td>: ${props.location}</td>
                                </tr>
                            </table>
                        </div>
                    `,
                        icon: 'info',
                        showCancelButton: true,
                        showDenyButton: true, // Tombol Hapus kita pindah ke sini
                        confirmButtonText: 'Tutup',
                        denyButtonText: 'Hapus Jadwal', // Tombol Merah
                        cancelButtonText: 'Edit (Geser)', // Opsional
                        customClass: {
                            title: 'my-0 py-0',
                            htmlContainer: 'py-0 my-0',
                            popup: 'text-start',
                            confirmButton: 'btn btn-primary me-2 btn-sm',
                            denyButton: 'btn btn-danger btn-sm',
                            cancelButton: 'btn btn-secondary d-none btn-sm' // Sembunyikan tombol cancel jika tidak perlu
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        // Jika tombol "Hapus Jadwal" diklik
                        if (result.isDenied) {

                            // Konfirmasi Ulang (Double Check)
                            Swal.fire({
                                title: 'Yakin hapus jadwal ini?',
                                text: "Matkul akan dikembalikan ke daftar antrean.",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Hapus',
                                cancelButtonText: 'Batal',
                                customClass: {
                                    title: 'my-0 py-0',
                                    htmlContainer: 'py-0 my-0',
                                    confirmButton: 'btn btn-primary btn-sm me-3',
                                    cancelButton: 'btn btn-secondary btn-sm'
                                },
                                buttonsStyling: false
                            }).then((resDelete) => {
                                if (resDelete.isConfirmed) {
                                    deleteSchedule(eventObj
                                        .id); // Panggil fungsi delete yang sudah ada
                                }
                            });
                        }
                    });
                },

            });

            calendar.render();

        });

        function deleteSchedule(id) {
            // Tampilkan Loading (Optional)
            document.getElementById('loadingIndicator').style.display = 'inline-block';

            // URL Route: /jadwal-perkuliahan/{id}
            // Kita harus buat URL manual karena di JS tidak bisa pakai route() parameter dinamis dengan mudah
            let url = "{{ route('jadwal-perkuliahan.destroy', ':id') }}";
            url = url.replace(':id', id);

            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Berhasil
                        showJsToast('success', data.message);

                        // PENTING: Reload halaman agar kartu matkul KEMBALI ke Sidebar
                        // Kita beri jeda sedikit agar user sempat baca toast
                        setTimeout(() => {
                            location.reload();
                        }, 1000);

                    } else {
                        showJsToast('error', data.message);
                        document.getElementById('loadingIndicator').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error(error);
                    showJsToast('error', 'Terjadi kesalahan sistem.');
                    document.getElementById('loadingIndicator').style.display = 'none';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const btnSubmitCalendar = document.getElementById('btnSubmitSchedule');
            if (btnSubmitCalendar) {
                btnSubmitCalendar.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data tidak bisa diubah setelah diajukan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ajukan!',
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
                            document.getElementById('submitScheduleForm').submit();
                        }
                    });
                });
            }
        });
    </script>
@endsection

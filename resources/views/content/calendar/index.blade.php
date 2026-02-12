@extends('layouts/contentNavbarLayout')
@section('title', 'Kalender Akademik - PRIMA')
@section('page-style')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js'></script>
@endsection

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

    <div class="row">
        <div class="col-12">
            {{-- CARD STATUS & ACTION --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                        {{-- Left: Title & Info --}}
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-label-primary rounded d-flex align-items-center justify-content-center me-3">
                                <i class="bx bx-calendar-event bx-sm"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold text-primary">Kalender Akademik</h5>
                                <small class="text-muted">Periode: <span
                                        class="fw-semibold text-heading">{{ $activePeriod->name }}</span></small>
                            </div>
                        </div>

                        {{-- Right: Status & Actions --}}
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                            {{-- 1. BADGE STATUS --}}
                            @if ($approvalDoc)
                                <div class="text-md-end order-2 order-sm-1">
                                    <span class="badge bg-{{ $approvalDoc->status_color }} mb-1">
                                        {{ strtoupper($approvalDoc->status_pendek) }}
                                    </span>
                                    <div class="small text-muted d-flex align-items-center justify-content-md-end gap-1">
                                        <i class='bx bx-time-five'></i> {{ $approvalDoc->updated_at->diffForHumans() }}
                                    </div>
                                </div>
                            @else
                                <div class="order-2 order-sm-1">
                                    <span class="badge bg-label-secondary">DRAFT (Belum Diajukan)</span>
                                </div>
                            @endif

                            {{-- 2. TOMBOL AJUKAN --}}
                            @php
                                $canEdit = !$approvalDoc || in_array($approvalDoc->status, ['draft', 'rejected']);
                            @endphp

                            @if ($canEdit)
                                <div class="d-flex gap-2 order-1 order-sm-2">
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#addEventModal">
                                        <i class="bx bx-plus me-1"></i> <span class="d-none d-sm-inline">Baru</span><span
                                            class="d-inline d-sm-none">Item</span>
                                    </button>

                                    <form id="submitCalendarForm" action="{{ route('kalender-akademik.submit') }}"
                                        method="POST">
                                        @csrf
                                        <button type="button" class="btn btn-primary shadow-sm" id="btnSubmitCalendar">
                                            <i class='bx bx-send me-1'></i> Ajukan
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Feedback Message jika Rejected --}}
                    @if ($approvalDoc && $approvalDoc->status == 'rejected')
                        <div class="alert alert-danger mt-4 mb-0 d-flex align-items-start gap-3" role="alert">
                            <div class="flex-shrink-0">
                                <i class="bx bx-x-circle bx-sm mt-1"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1">Perlu Revisi</h6>
                                <p class="mb-0 text-break">{{ $approvalDoc->feedback_message }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card mb-4">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th>Nama Kegiatan</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    @if ($canEdit)
                                        <th style="width: 10%">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($events as $index => $event)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="fw-medium">{{ $event->name }}</span></td>
                                        <td>
                                            <span class="text-muted small">{{ $event->start_date->format('d M Y') }}</span>
                                            <span class="text-muted small"> s/d
                                                {{ $event->end_date->format('d M Y') }}</span>
                                        </td>
                                        <td>{{ $event->description ?? '-' }}</td>
                                        @if ($canEdit)
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:;" class="text-body edit-record me-2"
                                                        data-bs-toggle="modal" data-bs-target="#addEventModal"
                                                        onclick="editEvent(this)" data-id="{{ $event->id }}"
                                                        data-name="{{ $event->name }}"
                                                        data-start-date="{{ $event->start_date->format('Y-m-d') }}"
                                                        data-end-date="{{ $event->end_date ? $event->end_date->format('Y-m-d') : '' }}"
                                                        data-description="{{ $event->description }}"
                                                        data-target-semesters="{{ json_encode($event->target_semesters) }}"
                                                        data-url="{{ route('kalender-akademik.update', $event->id) }}">

                                                        <i class="bx bx-edit text-muted bx-sm"></i>
                                                    </a>
                                                    <form action="{{ route('kalender-akademik.destroy', $event->id) }}"
                                                        method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <a href="javascript:;" class="text-body  delete-record"
                                                            data-bs-toggle="tooltip" data-bs-offset="0,6"
                                                            data-bs-placement="bottom" data-bs-html="true"
                                                            title="Delete Agenda">
                                                            <i class="bx bx-trash text-danger bx-sm"></i>
                                                        </a>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Belum ada agenda kegiatan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bx bx-calendar-alt me-2"></i>
                            Kalender Akademik
                        </h5>
                        <small class="text-muted">Tampilan kalender untuk periode
                            <b>{{ $activePeriod->name }}</b></small>
                    </div>
                    <h5 class="mb-0">Kalender Akademik: {{ $activePeriod->name }}</h5>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="addEventModal" tabindex="-1">
            <div class="modal-dialog">
                <form id="formEvent" action="{{ route('kalender-akademik.store') }}" method="POST">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Agenda Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @csrf
                            <div id="methodInputContainer"></div>
                            <div class="mb-3">
                                <label class="form-label">Nama Kegiatan</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                    placeholder="Contoh: UAS Susulan">
                            </div>

                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label">Mulai</label>
                                    <input type="date" id="start-date" class="form-control" name="start_date"
                                        required>
                                </div>
                                <div class="col">
                                    <label class="form-label">Selesai (Opsional)</label>
                                    <input type="date" id="end-date" class="form-control" name="end_date">
                                </div>
                            </div>

                            @foreach ($availableSemesters as $semester)
                                <input type="hidden" name="target_semesters[]" value="{{ $semester }}">
                            @endforeach

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                            <input type="hidden" name="id" id="eventId">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


    @endsection

    @section('page-script')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // --- 1. Helper Function Modal Edit ---
                const formEvent = document.getElementById('formEvent');
                const methodContainer = document.getElementById('methodInputContainer');
                const modalTitle = document.querySelector('#addEventModal .modal-title');

                function editEvent(button) {
                    modalTitle.innerText = "Edit Agenda";
                    formEvent.action = button.dataset.url;
                    methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';
                    formEvent.elements['name'].value = button.dataset.name;
                    formEvent.elements['start_date'].value = button.dataset.startDate;
                    formEvent.elements['end_date'].value = button.dataset.endDate;
                    formEvent.elements['description'].value = button.dataset.description;
                }

                function resetModal() {
                    modalTitle.innerText = "Agenda Baru";
                    formEvent.action = "{{ route('kalender-akademik.store') }}";
                    methodContainer.innerHTML = '';
                    formEvent.reset();
                }

                document.getElementById('addEventModal').addEventListener('hidden.bs.modal', function() {
                    resetModal();
                });

            });

            // --- 2. Inisialisasi FullCalendar (Safe Mode) ---
            document.addEventListener('DOMContentLoaded', function() { // Move to top

                // Fungsi rekursif untuk menunggu library dimuat
                const initCalendar = () => {
                    // Cek apakah object 'fullcalendar' sudah ada di window global?
                    // (Ini berasal dari file fullcalendar.js yang kita buat di folder libs)
                    if (window.FullCalendar) {
                        // Set Locale Moment ke Indonesia
                        moment.locale('id');

                        const calendarEl = document.getElementById('calendar');

                        // Style lebih halus dengan warna pastel

                        const calendar = new FullCalendar.Calendar(calendarEl, {

                            initialView: 'dayGridMonth',
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,listMonth'
                            },
                            // Locale Indonesia
                            locale: 'id',
                            buttonText: {
                                today: 'Hari Ini',
                                month: 'Bulan',
                                week: 'Minggu',
                                day: 'Hari',
                                list: 'Agenda'
                            },

                            // Load Events dari Controller
                            events: "{{ route('kalender-akademik.events') }}",


                            eventColor: '#4299e1', // Warna default event
                            eventTextColor: '#fff', // Warna text event
                            editable: false,
                            selectable: true,
                            eventClick: function(info) {
                                const start = moment(info.event.start);
                                const end = info.event.end ? moment(info.event.end) : null;
                                const dateStr = start.format('D MMMM YYYY');
                                const dayStr = start.format('dddd');
                                const desc = info.event.extendedProps.description ||
                                    '<span class="text-muted fst-italic">Tidak ada deskripsi.</span>';

                                Swal.fire({
                                    title: '', // Kosongkan title default
                                    html: `
                                        <div class="text-start">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="avatar avatar-md me-3">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        <i class='bx bx-calendar-event bx-sm'></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0 fw-bold text-heading">${info.event.title}</h5>
                                                    <small class="text-muted">Detail Agenda Kegiatan</small>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label text-muted text-uppercase small fw-bold mb-2">
                                                    <i class="bx bx-time-five me-1"></i> Waktu Pelaksanaan
                                                </label>
                                                <div class="p-3 bg-lighter rounded border border-dashed d-flex align-items-center">
                                                    <i class="bx bx-calendar me-3 text-primary bx-md"></i>
                                                    <div>
                                                        <h6 class="mb-0 text-heading fw-semibold">${dateStr}</h6>
                                                        <small class="text-muted">Sampai ${end ? '  ' + end.format('D MMMM YYYY') : ''}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label text-muted text-uppercase small fw-bold mb-2">
                                                    <i class="bx bx-align-left me-1"></i> Deskripsi
                                                </label>
                                                <div class="text-body bg-label-light p-3 rounded">
                                                    ${desc}
                                                </div>
                                            </div>
                                        </div>
                                    `,
                                    confirmButtonText: 'Tutup',
                                    showCloseButton: true,
                                    customClass: {
                                        popup: 'swal-custom',
                                        content: 'p-0',
                                        confirmButton: 'btn btn-secondary btn-sm',
                                    },
                                    buttonsStyling: false
                                });
                            }
                        });

                        calendar.render();
                    } else {
                        // Jika belum load, coba lagi 100ms kemudian
                        setTimeout(initCalendar, 100);
                    }
                };

                // Jalankan fungsi
                initCalendar();
            });
            // Tambahkan style kustom
            const style = document.createElement('style');
            style.innerHTML = `
            .fc-header-toolbar {
                margin-bottom: 1em;
            }
            .fc-button {
                background-color: #667eea;
                color: white;
                border: none;
                padding: 0.5em 1em;
                border-radius: 0.25em;
            }
            `;
            document.head.appendChild(style);
            // --- 3. Handler Submit & Delete (SweetAlert) ---

            document.addEventListener('DOMContentLoaded', function() {
                // Submit Form
                const btnSubmitCalendar = document.getElementById('btnSubmitCalendar');
                if (btnSubmitCalendar) {
                    btnSubmitCalendar.addEventListener('click', function() {
                        Swal.fire({
                            title: 'Ajukan ke Wadir?',
                            text: "Data tidak bisa diubah setelah diajukan!",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Ajukan!',
                            cancelButtonText: 'Batal',
                            customClass: {
                                title: 'my-0 py-0',
                                htmlContainer: 'py-0 my-0',
                                confirmButton: 'btn btn-primary btn-sm me-3',
                                cancelButton: 'btn btn-secondary btn-sm'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('submitCalendarForm').submit();
                            }
                        });
                    });
                }
            });

            // Delete Handler
            document.body.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.delete-record');
                if (deleteBtn) {
                    e.preventDefault();
                    const form = deleteBtn.closest('form');

                    Swal.fire({
                        title: 'Hapus Agenda?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus!',
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
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>
    @endsection

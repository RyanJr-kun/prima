@extends('layouts/contentNavbarLayout')
@section('title', 'Kalender Akademik - PRIMA')


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
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Kalender Akademik : <span
                                class="badge bg-label-primary">{{ $activePeriod->name }}</span></h5>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        {{-- 1. BADGE STATUS --}}
                        @if ($approvalDoc)
                            <div class="text-end">
                                <span class="badge bg-{{ $approvalDoc->status_color }} mb-1">
                                    {{ strtoupper($approvalDoc->status_text) }}
                                </span>
                                <div class="small text-muted">
                                    Last update: {{ $approvalDoc->updated_at->diffForHumans() }}
                                </div>
                            </div>
                        @else
                            <span class="badge bg-secondary">DRAFT (Belum Diajukan)</span>
                        @endif

                        {{-- 2. TOMBOL AJUKAN (Hanya muncul jika Draft atau Rejected) --}}
                        @php
                            $canEdit = !$approvalDoc || in_array($approvalDoc->status, ['draft', 'rejected']);
                        @endphp

                        @if ($canEdit)
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                                <i class="bx bx-plus"></i> Item Baru
                            </button>

                            <form id="submitCalendarForm" action="{{ route('kalender-akademik.submit') }}" method="POST">
                                @csrf
                                <button type="button" class="btn btn-success" id="btnSubmitCalendar">
                                    <i class='bx bx-send'></i> Ajukan ke Wadir
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Feedback Message jika Rejected --}}
                @if ($approvalDoc && $approvalDoc->status == 'rejected')
                    <div class="alert alert-danger m-3 mb-0">
                        <strong>Catatan Revisi:</strong> {{ $approvalDoc->feedback_message }}
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
                                        <span class="text-muted small"> s/d {{ $event->end_date->format('d M Y') }}</span>
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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Kalender Akademik: {{ $activePeriod->name }}</h5>
        </div>
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    <div class="modal fade" id="addEventModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="formEvent" action="{{ route('kalender-akademik.store') }}" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Agenda Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <input type="date" id="start-date" class="form-control" name="start_date" required>
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

        // --- 2. Inisialisasi FullCalendar (Safe Mode) ---
        document.addEventListener('DOMContentLoaded', function() {

            // Fungsi rekursif untuk menunggu library dimuat
            const initCalendar = () => {
                // Cek apakah object 'fullcalendar' sudah ada di window global?
                // (Ini berasal dari file fullcalendar.js yang kita buat di folder libs)
                if (window.fullcalendar) {
                    const calendarEl = document.getElementById('calendar');

                    // Destructure plugin dari window.fullcalendar
                    const {
                        Calendar,
                        plugins
                    } = window.fullcalendar;

                    const calendar = new Calendar(calendarEl, {
                        // Pastikan plugins didaftarkan disini!
                        plugins: plugins,

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

                        editable: false,
                        selectable: true,
                        eventClick: function(info) {
                            Swal.fire({
                                title: info.event.title,
                                text: info.event.extendedProps.description ||
                                    'Tidak ada deskripsi',
                                icon: 'info',
                                confirmButtonText: 'Tutup',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
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
                            confirmButton: 'btn btn-success me-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('submitCalendarForm').submit();
                        }
                    });
                });
            }

            // Toast Notifikasi
            @if (session('success'))
                new bootstrap.Toast(document.getElementById('successToast')).show();
            @endif
            @if (session('error'))
                new bootstrap.Toast(document.getElementById('errorToast')).show();
            @endif
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
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-secondary'
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
@endsection

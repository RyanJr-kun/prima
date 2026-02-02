@extends('layouts/contentNavbarLayout')
@section('title', 'Dokumen - PRIMA')

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

        <div id="jsToast" class="bs-toast toast fade hide" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="icon-base bx bx-bell icon-xs me-2"></i>
                <span class="fw-medium me-auto" id="jsToastTitle">Notifikasi</span>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="jsToastBody"></div>
        </div>
    </div>

    <div class="card mb-4 ">
        @php
            $tabs = [
                'distribusi_matkul' => ['icon' => 'bx-file', 'label' => 'Distribusi Matkul'],
                'beban_kerja_dosen' => ['icon' => 'bx-user-check', 'label' => 'Laporan BKD'],
                'jadwal_perkuliahan' => ['icon' => 'bx-calendar', 'label' => 'Jadwal Kuliah'],
                'kalender_akademik' => ['icon' => 'bx-calendar-event', 'label' => 'Kalender Akademik'],
            ];
        @endphp

        <div class="card-body py-3 d-none d-md-block">
            {{-- versi dekstop --}}
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold my-0">Arsip Dokumen</h5>
                    <div class="text-muted">Periode: <strong>{{ $activePeriod->name }}</strong></div>
                </div>

                <form action="{{ route('documents.index') }}" method="GET" class="d-flex gap-2">
                    <select name="period_id" class="form-select select2" onchange="this.form.submit()">
                        @foreach ($periods as $p)
                            <option value="{{ $p->id }}" {{ $activePeriod->id == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        <ul class="nav nav-pills ms-4 mb-3 d-none d-md-flex" role="tablist">
            @foreach ($tabs as $key => $tab)
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }}" role="tab"
                        data-bs-toggle="tab" data-bs-target="#nav-{{ $key }}">
                        <i class="bx {{ $tab['icon'] }} me-1"></i> {{ $tab['label'] }}
                        @if (isset($groupedDocs[$key]))
                            <span class="badge rounded-pill bg-danger ms-1">{{ $groupedDocs[$key]->count() }}</span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>
        {{-- versi mobile --}}
        <div class="d-block d-md-none p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Arsip Dokumen</h5>
                <span class="badge bg-label-primary">{{ $activePeriod->name }}</span>
            </div>

            <form action="{{ route('documents.index') }}" method="GET" class="mb-3">
                <select name="period_id" class="form-select select2" onchange="this.form.submit()">
                    @foreach ($periods as $p)
                        <option value="{{ $p->id }}" {{ $activePeriod->id == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="overflow-auto">
                <ul class="nav nav-pills flex-nowrap pb-1" role="tablist">
                    @foreach ($tabs as $key => $tab)
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }} text-nowrap"
                                role="tab" data-bs-toggle="tab" data-bs-target="#nav-{{ $key }}">
                                <i class="bx {{ $tab['icon'] }} me-1"></i> {{ $tab['label'] }}
                                @if (isset($groupedDocs[$key]))
                                    <span
                                        class="badge rounded-pill bg-danger ms-1">{{ $groupedDocs[$key]->count() }}</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- JENIS DOKUMEN --}}
    <div class="nav-align-top mb-4">
        <div class="tab-content shadow-sm p-4 rounded-bottom bg-white">
            @foreach ($tabs as $key => $tab)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="nav-{{ $key }}"
                    role="tabpanel">

                    @if (isset($groupedDocs[$key]) && $groupedDocs[$key]->count() > 0)
                        <div class="row g-4">
                            @foreach ($groupedDocs[$key] as $doc)
                                <div class="col-md-6 col-lg-4">

                                    {{-- LOGIKA PEMILIHAN PARTIAL --}}
                                    @if ($key == 'kalender_akademik')
                                        {{-- wadir 1 -> direktur --}}
                                        @include('content.dokumen.partials.card-kalender', ['doc' => $doc])
                                    @elseif($key == 'distribusi_matkul')
                                        {{-- kaprodi -> wadir1 -> wadir2 -> direktur --}}
                                        @include('content.dokumen.partials.card-distribusi', [
                                            'doc' => $doc,
                                        ])
                                    @elseif ($key == 'beban_kerja_dosen')
                                        @include('content.dokumen.partials.card-bkd', [
                                            'doc' => $doc,
                                        ])
                                    @elseif ($key == 'jadwal_perkuliahan')
                                        @include('content.dokumen.partials.card-jadwal', [
                                            'doc' => $doc,
                                        ])
                                    @else
                                        @include('content.dokumen.partials.card-distribusi', [
                                            'doc' => $doc,
                                        ])
                                    @endif

                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="{{ asset('assets/img/illustrations/empty-box.png') }}" alt="Kosong" width="150"
                                class="mb-3 opacity-50">
                            <h6 class="text-muted">Tidak ada dokumen {{ $tab['label'] }} pada periode ini.</h6>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    </div>

    {{-- MODAL REJECT / REVISI --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="rejectForm" action="" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger fw-bold">Kembalikan Dokumen (Revisi)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bx bx-error-circle me-1"></i>
                            Tindakan ini akan mengembalikan status dokumen menjadi <strong>Rejected</strong>.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan / Alasan Revisi <span class="text-danger">*</span></label>
                            <textarea name="feedback_message" class="form-control" rows="4" required
                                placeholder="Contoh: SKS Dosen A melebihi batas, tolong diperbaiki."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Kirim Revisi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('page-script')

    <script>
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
            // Handle Tombol Reject agar URL form dinamis sesuai ID Dokumen
            var rejectModal = document.getElementById('rejectModal');
            rejectModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var docId = button.getAttribute('data-id');

                // Set Action URL: /documents/{id}/reject
                var form = document.getElementById('rejectForm');
                var urlTemplate = "{{ route('documents.reject', ':id') }}";
                form.action = urlTemplate.replace(':id', docId);
            });

            // Inisialisasi Select2
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').select2({
                    width: '100%',
                    minimumResultsForSearch: 5
                });
            }

            // Handler Approve Dokumen
            document.body.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-approve-doc');
                if (btn) {
                    e.preventDefault();
                    const form = btn.closest('form');
                    Swal.fire({
                        title: 'Setujui Dokumen?',
                        text: "Apakah Anda yakin menyetujui dokumen ini?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Setuju!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            title: 'my-0 py-0',
                            htmlContainer: 'py-0 my-0',
                            confirmButton: 'btn btn-success me-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                }
            });

            // Handler Ajukan Ulang
            document.body.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-resubmit-doc');
                if (btn) {
                    e.preventDefault();
                    const form = btn.closest('form');
                    Swal.fire({
                        title: 'Ajukan Ulang?',
                        text: "Dokumen akan diajukan kembali untuk diperiksa.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ajukan!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            title: 'my-0 py-0',
                            htmlContainer: 'py-0 my-0',
                            confirmButton: 'btn btn-warning me-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                }
            });

            const successToast = document.getElementById('successToast');
            if (successToast) {
                new bootstrap.Toast(successToast, {
                    delay: 5000
                }).show();
            }
            const errorToast = document.getElementById('errorToast');
            if (errorToast) {
                new bootstrap.Toast(errorToast, {
                    delay: 5000
                }).show();
            }
        });
    </script>
@endsection

<div class="card border border-{{ $doc->status_color }} shadow-sm hover-shadow">
    <div class="card-body">
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="fw-bold mb-0">Jadwal Perkuliahan</h6>
                <span class="badge bg-label-{{ $doc->status_color }}">{{ $doc->status_pendek }}</span>
            </div>
            {{-- Body Info --}}
            <div class="row g-1 small">
                {{-- Periode --}}
                <div class="col-4 text-muted">Periode</div>
                <div class="col-8 fw-medium">: {{ $doc->academicPeriod->name }}</div>

                {{-- [BARU] Kampus --}}
                <div class="col-4 text-muted">Kampus</div>
                <div class="col-8 fw-medium text-primary">
                    : {{ ucwords(str_replace('_', ' ', $doc->campus)) }}
                </div>

                {{-- [BARU] Shift --}}
                <div class="col-4 text-muted">Shift</div>
                <div class="col-8 fw-medium text-primary">
                    : {{ ucfirst($doc->shift) }}
                </div>

                {{-- Update Time --}}
                <div class="col-4 text-muted">Update</div>
                <div class="col-8 text-muted">: {{ $doc->updated_at->diffForHumans() }}</div>

                {{-- User Pengaju --}}
                <div class="col-4 text-muted">Oleh</div>
                <div class="col-8 text-muted">: {{ $doc->lastActionUser->name ?? '-' }}</div>
            </div>
        </div>

        {{-- Info Posisi Dokumen --}}
        <div class="mb-3 p-2 bg-label-primary rounded small">
            <i class="bx bx-map-pin me-1 text-primary"></i> Posisi:
            <strong>
                @switch($doc->status)
                    @case('approved_kaprodi')
                        Wadir 1
                    @break

                    @case('approved_wadir2')
                        Direktur
                    @break

                    @case('approved_direktur')
                        Selesai
                    @break

                    @case('rejected')
                        Dikembalikan
                    @break

                    @default
                        Draft
                @endswitch
            </strong>
        </div>

        {{-- Tampilkan Pesan Revisi Jika Ada --}}
        @if ($doc->feedback_message && $doc->status == 'rejected')
            <div class="alert alert-danger p-2 small mb-3">
                <strong>Catatan:</strong> {{ Str::limit($doc->feedback_message, 50) }}
            </div>
        @endif

        <hr class="my-3">

        {{-- LOGIC TOMBOL --}}
        <div class="d-flex justify-content-between align-items-center">

            <a href="{{ route('jadwal-perkuliahan.show') }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-calendar me-1"></i> Lihat Detail
            </a>

            @php
                $user = auth()->user();
                $canAction = false;

                if ($user->hasRole('wadir1') && $doc->status == 'submitted') {
                    $canAction = true;
                }

                if ($user->hasRole('direktur') && $doc->status == 'approved_wadir1') {
                    $canAction = true;
                }
            @endphp

            @if ($canAction)
                <div class="d-flex">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-danger me-2" data-bs-toggle="modal"
                        data-bs-target="#rejectModal" data-id="{{ $doc->id }}">
                        <i class="bx bx-x"></i> Revisi
                    </button>

                    <form action="{{ route('documents.approve', $doc->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="is_short_flow" value="1">
                        <button type="button" class="btn btn-sm btn-success btn-approve-doc">
                            <i class="bx bx-check"></i> ACC
                        </button>
                    </form>
                </div>
            @elseif($doc->status == 'approved_direktur')
                @php
                    $urlPrint = '#';
                    // Arahkan ke halaman index masing-masing fitur dengan filter yang sesuai
                    switch ($doc->type) {
                        case 'jadwal_perkuliahan':
                            $urlPrint = route('jadwal-perkuliahan.print', $doc->id);
                            break;
                    }
                @endphp

                <div class="d-flex justify-content-end">
                    <a href="{{ $urlPrint }}" target="_blank" class="btn me-3 btn-outline-danger btn-sm">
                        <i class="bx bxs-file-pdf me-1"></i> PDF
                    </a>
                    <span class="text-success mt-1"><i class="bx bx-check-double"></i> Terbit</span>
                </div>
            @endif

            {{-- RESUBMIT (Admin Akademik) --}}
            @if (($doc->status == 'rejected' || $doc->status == 'draft') && $user->hasRole('admin_akademik'))
                <form action="{{ route('documents.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="document_id" value="{{ $doc->id }}">
                    <button type="button" class="btn btn-sm btn-warning btn-resubmit-doc">
                        <i class="bx bx-send"></i> Ajukan
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="card border border-{{ $doc->status_color }} shadow-sm hover-shadow">
    <div class="card-body">
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="fw-bold mb-0">{{ \Illuminate\Support\Str::limit($doc->prodi->name ?? '-', 22) }}</h6>
                <span class=" text-end badge bg-label-{{ $doc->status_color }}">{{ $doc->status_text }}</span>
            </div>
            <div class="row g-1">
                <div class="col-4 col-md-3"><small class="text-muted">Dokumen</small></div>
                <div class="col-8 col-md-9">
                    <small class="fw-medium">
                        <span class="d-none d-md-inline me-1">:</span>
                        {{ $doc->type_label }} {{ $doc->prodi->jenjang ?? '' }} {{ $doc->prodi->code ?? '' }}
                    </small>
                </div>

                <div class="col-4 col-md-3"><small class="text-muted">Periode</small></div>
                <div class="col-8 col-md-9">
                    <small class="fw-medium">
                        <span class="d-none d-md-inline me-1">:</span>
                        {{ $doc->academicPeriod->name }}
                    </small>
                </div>

                <div class="col-4 col-md-3"><small class="text-muted">Update</small></div>
                <div class="col-8 col-md-9">
                    <small class="text-muted">
                        <span class="d-none d-md-inline me-1">:</span>
                        {{ $doc->updated_at->diffForHumans() }}
                    </small>
                </div>
            </div>
        </div>

        {{-- Info Posisi Dokumen --}}
        <div class="mb-3 p-2 bg-label-primary rounded small">
            <i class="bx bx-map-pin me-1 text-primary"></i> Posisi:
            <strong>
                @switch($doc->status)
                    @case('submitted')
                        Kaprodi
                    @break

                    @case('approved_kaprodi')
                        Wadir 1
                    @break

                    @case('approved_wadir1')
                        Wadir 2
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

            {{-- 1. TOMBOL LIHAT FILE --}}
            @php
                $urlDetail = '#';
                // Arahkan ke halaman index masing-masing fitur dengan filter yang sesuai
                switch ($doc->type) {
                    case 'distribusi_matkul':
                        $urlDetail = route('distribusi-mata-kuliah.show-doc', $doc->id);
                        break;
                    // case 'beban_kerja_dosen':
                    //     $urlDetail = route('beban-kerja-dosen.show-doc', $doc->id);
                    //     break;
                    // Tambahkan case lain nanti (Jadwal, BKD)
                }
            @endphp

            <a href="{{ $urlDetail }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-show me-1"></i> Lihat Data
            </a>

            {{-- 2. TOMBOL ACTION (APPROVE/REJECT) --}}
            @php
                $user = auth()->user();
                $canAction = false;

                if ($user->hasRole('kaprodi') && $doc->status == 'submitted') {
                    $canAction = true;
                }
                if ($user->hasRole('wadir1') && $doc->status == 'approved_kaprodi') {
                    $canAction = true;
                }
                if ($user->hasRole('wadir2') && $doc->status == 'approved_wadir1') {
                    $canAction = true;
                }
                if ($user->hasRole('direktur') && $doc->status == 'approved_wadir2') {
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
                        <button type="button" class="btn btn-sm btn-success btn-approve-doc">
                            <i class="bx bx-check me-1"></i> Setuju
                        </button>
                    </form>
                </div>
            @elseif($doc->status == 'approved_direktur')
                @php
                    $urlPrint = '#';
                    // Arahkan ke halaman index masing-masing fitur dengan filter yang sesuai
                    switch ($doc->type) {
                        case 'distribusi_matkul':
                            $urlPrint = route('distribusi-mata-kuliah.print', $doc->id);
                            break;
                        // case 'beban_kerja_dosen':
                        //     $urlPrint = route('beban-kerja-dosen.print', $doc->id);
                        //     break;
                    }
                @endphp
                <div class="d-flex justify-content-end">
                    <a href="{{ $urlPrint }}" class="btn me-3 btn-outline-danger btn-sm">
                        <i class="bx bxs-file-pdf me-1"></i> PDF
                    </a>
                    <span class="text-success mt-1"><i class="bx bx-check-double"></i> Terbit</span>
                </div>
            @endif

            {{-- 3. TOMBOL SUBMIT ULANG (Khusus Admin/Kaprodi jika Rejected) --}}
            @if (
                ($doc->status == 'rejected' || $doc->status == 'draft') &&
                    ($user->hasRole('admin_prodi') || $user->hasRole('kaprodi')))
                <form action="{{ route('documents.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="document_id" value="{{ $doc->id }}">
                    <button type="button" class="btn btn-sm btn-warning btn-resubmit-doc">
                        <i class="bx bx-send me-1"></i> Ajukan Ulang
                    </button>
                </form>
            @endif

        </div>
    </div>
</div>

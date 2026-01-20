@extends('layouts/contentNavbarLayout')

@section('title', 'Master Data Kelas')

@section('content')

{{-- 1. TOAST NOTIFICATION --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
  @if (session('success'))  
  <div id="successToast" class="bs-toast bg-primary toast fade hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <i class="icon-base bx bx-bell icon-xs me-2"></i>
      <span class="fw-medium me-auto">Berhasil</span>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">{{ session('success') }}</div>
  </div>
  @endif 

  @if (session('error'))
  <div id="errorToast" class="bs-toast bg-danger toast fade hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <i class="icon-base bx bx-bell icon-xs me-2"></i>
      <span class="fw-medium me-auto">Error</span>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">{{ session('error') }}</div>
  </div>
  @endif
</div>

<div class="card">
  {{-- 2. CARD HEADER --}}
  <div class="card-header border-bottom">
    <div class="row">
        <div class="col-6">
            <h4 class="card-title mb-0">Kelas Perkuliahan</h4>
            <small class="text-muted">Data kelas aktif berdasarkan Periode Akademik.</small>
        </div>
        <div class="col-6 text-end">
            {{-- Tombol Tambah --}}
            <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddKelas" id="btnCreate">
                <span><i class="bx bx-plus me-2"></i>Tambah Kelas</span>
            </button>
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
                @if($kelas->academicAdvisor)
                <div class="d-flex justify-content-start align-items-center">
                    <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            {{ substr($kelas->academicAdvisor->name, 0, 2) }}
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="text-truncate fw-medium">{{ $kelas->academicAdvisor->name }}</span>
                    </div>
                </div>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
    
            <td>
                <div class="d-flex align-items-center">
                    <a href="javascript:;" class="text-body edit-record me-2"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasAddKelas"
                       data-id="{{ $kelas->id }}"
                       data-name="{{ $kelas->name }}" 
                       data-angkatan="{{ $kelas->angkatan }}"
                       data-semester="{{ $kelas->semester }}"
                       data-total-students="{{ $kelas->total_students }}"
                       data-prodi-id="{{ $kelas->prodi_id }}"
                       data-kurikulum-id="{{ $kelas->kurikulum_id }}"
                       data-advisor-id="{{ $kelas->academic_advisor_id }}"
                       data-academic-period-id="{{ $kelas->academic_period_id }}"
                       data-action="{{ route('master.kelas.update', $kelas->id) }}">
                        <i class="bx bx-edit text-muted bx-sm"></i>
                    </a>

                    {{-- TOMBOL DELETE --}}
                    <form action="{{ route('master.kelas.destroy', $kelas->id) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <a href="javascript:;" class="text-body  delete-record"
                            data-bs-toggle="tooltip"
                            data-bs-offset="0,6"
                            data-bs-placement="bottom"
                            data-bs-html="true"
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

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddKelas" aria-labelledby="offcanvasAddKelasLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddKelasLabel" class="offcanvas-title">Tambah Kelas Baru</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body mx-0 grow-0 p-6 h-100">
      <form class="add-new-Kelas pt-0" id="addNewKelasForm" action="{{ route('master.kelas.store') }}" method="POST">
        @csrf
        
        {{-- Prodi --}}
        <div class="mb-3">
            <label class="form-label">Program Studi</label>
            <select name="prodi_id" id="prodi_id" class="form-select select2" required>
                <option value="">Pilih Prodi</option>
                @foreach($prodis as $prodi)
                    <option value="{{ $prodi->id }}">
                        {{ $prodi->jenjang }} - {{ $prodi->name }}
                    </option>
                @endforeach
            </select>
            @error('prodi_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        {{-- Suffix Nama & Angkatan --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="add-name-class">Nama Kelas (Suffix)</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="add-name-class" placeholder="Contoh: A, B, Pagi" name="name" value="{{ old('name') }}" required />
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="add-angkatan">Angkatan</label>
                <input type="number" class="form-control @error('angkatan') is-invalid @enderror" 
                       id="add-angkatan" placeholder="2024" name="angkatan" value="{{ old('angkatan') }}" required />
                @error('angkatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Semester & Jumlah --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Semester Saat Ini</label>
                <select name="semester" id="semester" class="form-select select2" required>
                    <option value="">Pilih...</option>
                    @for ($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}">Semester {{ $i }}</option>
                    @endfor
                </select>
                @error('semester') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
    
            <div class="col-md-6 mb-3">
                <label class="form-label" for="add-total-students">Kapasitas (Mhs)</label>
                <input type="number" class="form-control @error('total_students') is-invalid @enderror" 
                       id="add-total-students" placeholder="30" name="total_students" value="{{ old('total_students') }}" required />
                @error('total_students') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Kurikulum --}}
        <div class="mb-3">
            <label class="form-label">Kurikulum</label>
            <select name="kurikulum_id" id="kurikulum_id" class="form-select select2" required>
                <option value="">Pilih Kurikulum</option>
                @if(isset($kurikulums))
                    @foreach($kurikulums as $kurikulum)
                        <option value="{{ $kurikulum->id }}">{{ $kurikulum->name }}</option>
                    @endforeach
                @endif
            </select>
            @error('kurikulum_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Dosen Wali (PA)</label>
            <select name="academic_advisor_id" id="academic_advisor_id" class="form-select select2" required>
                <option value="">Pilih Dosen</option>
                @if(isset($dosens))
                    @foreach($dosens as $advisor)
                        <option value="{{ $advisor->id }}">{{ $advisor->name }}</option>
                    @endforeach
                @endif
            </select>
            @error('academic_advisor_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        
        
        {{-- <div class="mb-3">
             <label class="form-label">Periode Akademik</label>
             <select name="academic_period_id" id="academic_period_id" class="form-select select2">
                 @foreach($activePeriods as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                 @endforeach
             </select>
        </div>  --}}

        <button type="submit" class="btn btn-primary me-3" id="saveBtn">Simpan Data</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Batal</button>
      </form>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('addNewKelasForm');
        const offcanvasEl = document.getElementById('offcanvasAddKelas');
        const offcanvasTitle = document.getElementById('offcanvasAddKelasLabel');
        const saveBtn = document.getElementById('saveBtn');
        const defaultAction = form.action;

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
                

                if (window.$) {
                    $('#prodi_id').val(d.prodiId).trigger('change');
                    $('#semester').val(d.semester).trigger('change');
                    $('#kurikulum_id').val(d.kurikulumId).trigger('change');
                    $('#academic_advisor_id').val(d.advisorId).trigger('change');
                } else {
                    document.getElementById('prodi_id').value = d.prodiId;
                    document.getElementById('semester').value = d.semester;
                    document.getElementById('kurikulum_id').value = d.kurikulumId;
                    document.getElementById('academic_advisor_id').value = d.advisorId;
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
                        title:'my-0 py-0',
                        htmlContainer:'py-0 my-0',
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
            new bootstrap.Toast(successToast, { delay: 3000 }).show();
        }
        const errorToast = document.getElementById('errorToast');
        if (errorToast) {
            new bootstrap.Toast(errorToast, { delay: 3000 }).show();
        }
    });
</script>
@endsection
@extends('layouts/contentNavbarLayout')
@section('title', 'Kelas - PRIMA')
@section('content')

<div class="card">

  <div class="card-header border-bottom">
    <div class="row">

        <div class="col-6">
            <h4 class="card-title mb-0">Data Kelas</h4>
        </div>
        <div class="col-6 text-end">
            <button class="btn btn-primary add-new" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddClass" id="btnCreate">
                <span><i class="bx bx-plus me-2"></i>Kelas</span>
            </button>
        </div>
    </div>
  </div>

  <div class="card-datatable table-responsive">
    <table class="table border-top" id="tableUser">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Kelas</th>
          <th class="text-center">Semester</th>
          <th class="text-center">Periode</th>
          <th class="text-center">Jumlah Murid</th>
          <th>Pembiming Akademik</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($classes as $key => $kelas)
        <tr >
            <td>{{ ++$key }}</td>
            <td>
                <div>
                    <h6 class="mb-0">{{ $kelas->full_name }}</h6>
                    <small class="text-xs text-truncate">Angkatan: {{ $kelas->angkatan }}</small>
                </div>
            </td>

            <td class="text-center">Semester {{ $kelas->semester }}</td>
            <td class="text-center">{{ $kelas->period->name }}</td>
            <td class="text-center">{{ $kelas->total_students }}</td>
            <td>{{ $kelas->academicAdvisor->name }}</td>

            {{-- <td><span class="badge bg-label-success">Active</span></td> --}}
            <td>
                <div class="d-flex align-items-center">
                    {{-- data-bs-toggle="offcanvas"
                       data-bs-target="#offcanvasAddClass"
                       data-id="{{ $user->id }}"
                       data-name="{{ $user->name }}"
                       data-angkatan="{{ $user->angkatan }}"
                       data-semester="{{ $user->semester }}"
                       data-roles='@json($user->getRoleNames())'
                       data-action="{{ route('user.update', $user->id) }}" --}}
                    <a href="javascript:;" class="text-body edit-record me-2">
                        <i class="bx bx-edit text-muted bx-sm"></i>
                    </a>
                    <form action="{{ route('master.kelas.destroy', $kelas->id) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <a href="javascript:;" class="text-body delete-record">
                            <i class="bx bx-trash text-muted bx-sm"></i>
                        </a>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddClass" aria-labelledby="offcanvasAddClassLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddClassLabel" class="offcanvas-title">Tambah Kelas</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 grow-0 p-6 h-100">

      <form class="add-new-user pt-0" id="addNewUserForm" action="{{ route('master.kelas.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Program Studi</label>
            <select name="prodi_id" class="form-select select2">
                @foreach($prodis as $prodi)
                    <option value="{{ $prodi->id }}">
                        {{ $prodi->jenjang }} {{ $prodi->name }} ({{ $prodi->code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="add-name-class">Nama Kelas</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="add-name-class" placeholder="Contoh: A, B, C" name="name" value="{{ old('name') }}" />
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
          <label class="form-label" for="add-angkatan">Angkatan</label>
          <input type="text" class="form-control @error('angkatan') is-invalid @enderror" id="add-angkatan" placeholder="2025" name="angkatan" value="{{ old('angkatan') }}" />
          @error('angkatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Semester</label>
            <select name="semester" class="form-select select2" required>
                <option value="">Pilih Semester</option>
                @for ($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}">Semester {{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label" for="add-total-students">Jumlah Murid</label>
            <input type="number" class="form-control @error('total_students') is-invalid @enderror" id="add-total-students" placeholder="30" name="total_students" value="{{ old('total_students') }}" />
            @error('total_students') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Kurikulum</label>
            <select name="kurikulum_id" class="form-select select2">
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
            <label class="form-label">Pembimbing Akademik</label>
            <select name="academic_advisor_id" class="form-select select2">
                <option value="">Pilih Pembimbing</option>
                @if(isset($dosens))
                    @foreach($dosens as $advisor)
                        <option value="{{ $advisor->id }}">{{ $advisor->name }}</option>
                    @endforeach
                @endif
            </select>
            @error('academic_advisor_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary me-3" id="saveBtn">Submit</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
      </form>
    </div>
  </div>
</div>

@endsection

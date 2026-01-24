@extends('layouts/contentNavbarLayout')
@section('title', 'Distribusi Mata Kuliah - PRIMA')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Input Distribusi Mata Kuliah</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('distribusi-mata-kuliah.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <select name="study_class_id" id="selectKelas" class="form-select select2" required>
                        <option value="">Pilih Kelas</option>
                        @foreach ($classes as $kelas)
                            <option value="{{ $kelas->id }}">
                                {{ $kelas->full_name }} ({{ $kelas->kurikulum->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mata Kuliah</label>
                    <select name="course_id" id="selectMatkul" class="form-select select2" required>
                        <option value=""> Pilih Kelas Terlebih Dahulu </option>
                    </select>
                    <small class="text-muted">Mata kuliah otomatis muncul sesuai kurikulum kelas yang dipilih.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dosen Pengampu</label>
                    <select name="user_id" class="form-select select2" required>
                        <option value="">Pilih Dosen</option>
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dosen PDDIKTI</label>
                    <select name="user_id" class="form-select select2" required>
                        <option value="">Pilih Dosen</option>
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                        @endforeach
                    </select>
                </div>
                <hr>
                <h6>Data Tambahan (Opsional sesuai Excel)</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Referensi</label>
                        <input type="text" name="referensi" class="form-control" placeholder="Buku/Modul Ajar">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Luaran</label>
                        <input type="text" name="luaran" class="form-control" placeholder="Jurnal/HKI/Produk">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Distribusi</button>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectKelas = document.getElementById('selectKelas');
            const selectMatkul = document.getElementById('selectMatkul');

            selectKelas.addEventListener('change', function() {
                const classId = this.value;

                selectMatkul.innerHTML = '<option value="">Loading...</option>';
                // Trigger change untuk Select2 jika library tersebut aktif
                if (window.$) $(selectMatkul).trigger('change');

                if (classId) {
                    fetch('/ajax/get-courses-by-class/' + classId)
                        .then(response => response.json())
                        .then(data => {
                            selectMatkul.innerHTML = '<option value="">Pilih Mata Kuliah</option>';

                            data.forEach(course => {
                                const option = document.createElement('option');
                                option.value = course.id;
                                option.textContent =
                                    `[Smt ${course.semester}] ${course.name} (${course.code})`;
                                selectMatkul.appendChild(option);
                            });

                            // Update tampilan Select2 setelah data masuk
                            if (window.$) $(selectMatkul).trigger('change');
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    selectMatkul.innerHTML = '<option value="">Pilih Kelas Terlebih Dahulu</option>';
                    if (window.$) $(selectMatkul).trigger('change');
                }
            });
        });
    </script>
@endsection

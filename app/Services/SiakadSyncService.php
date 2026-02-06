<?php

namespace App\Services;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Course;
use App\Models\Kurikulum;
use App\Models\StudyClass;
use App\Models\AcademicPeriod;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SiakadSyncService
{
    // Konfigurasi URL bisa ditaruh di sini atau di .env
    protected $baseUrl = 'https://api.siakad-kampus.ac.id/v1';
    protected $apiKey = 'SECRET-KEY-ANDA';

    /**
     * Main Function untuk Sinkronisasi
     */
    public function syncClasses()
    {
        // 1. Cek Periode Aktif
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) {
            return ['status' => false, 'message' => 'Tidak ada periode akademik aktif.'];
        }

        try {
            // 2. Tembak API Siakad (Contoh)
            // Sesuaikan endpoint dengan dokumentasi API kampus Anda sebenarnya
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/kelas', [
                'tahun_akademik' => $activePeriod->code // misal '20251'
            ]);

            /** @var Response $response */
            if ($response->failed()) {
                return ['status' => false, 'message' => 'Gagal koneksi ke API Siakad: ' . $response->status()];
            }

            $dataSiakad = $response->json()['data'] ?? []; // Sesuaikan key JSON

            $count = 0;

            foreach ($dataSiakad as $row) {
                // A. Cari Prodi Lokal
                $prodi = Prodi::where('code', $row['kode_prodi'])->first();
                if (!$prodi) continue; // Skip jika prodi tidak dikenali

                // B. Cari Kurikulum (Logic: ambil kurikulum aktif prodi tsb)
                $kurikulum = Kurikulum::where('prodi_id', $prodi->id)
                    ->where('is_active', true)
                    ->first();

                if (!$kurikulum) continue;

                // C. Mapping Shift (Pagi/Malam)
                $shift = $this->mapShift($row['jenis_kelas'] ?? 'REGULER');

                // D. Simpan Data (Update or Create)
                StudyClass::updateOrCreate(
                    [
                        // Kunci unik untuk mencari data agar tidak duplikat
                        'name' => $row['nama_kelas'],
                        'academic_period_id' => $activePeriod->id,
                        'prodi_id' => $prodi->id,
                    ],
                    [
                        // Data yang akan diupdate/insert
                        'shift' => $shift,
                        'semester' => $row['semester'],
                        'angkatan' => $row['angkatan'],
                        'total_students' => $row['jumlah_mhs'] ?? 0,
                        'kurikulum_id' => $kurikulum->id,
                        'is_active' => true,
                    ]
                );
                $count++;
            }

            return ['status' => true, 'message' => "Sukses sinkronisasi $count data kelas."];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Error System: ' . $e->getMessage()];
        }
    }

    private function mapShift($jenis)
    {
        // Sesuaikan string dari API kampus Anda
        if (in_array(strtoupper($jenis), ['MALAM', 'KARYAWAN', 'SORE'])) {
            return 'malam';
        }
        return 'pagi';
    }

    /**
     * Sinkronisasi Mata Kuliah
     */
    public function syncCourses()
    {
        try {
            /** @var Response $response */
            $response = Http::timeout(60)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/mata-kuliah');

            if ($response->failed()) {
                return ['status' => false, 'message' => 'Gagal koneksi API: ' . $response->status()];
            }

            $dataSiakad = $response->json()['data'] ?? [];
            $count = 0;
            $skipped = 0;

            foreach ($dataSiakad as $row) {
                // A. Cari ID Kurikulum Lokal berdasarkan Kode Kurikulum Siakad
                $kurikulum = Kurikulum::where('code', $row['kode_kurikulum'])->first();

                if (!$kurikulum) {
                    // Skip jika kurikulum belum ada di DB lokal
                    // (User harus sync data kurikulum dulu)
                    $skipped++;
                    continue;
                }

                // B. Auto-Generate Tags berdasarkan SKS
                $tags = [];
                $sksPraktik = intval($row['sks_praktek'] ?? 0);
                $sksLapangan = intval($row['sks_lapangan'] ?? 0);

                if ($sksPraktik > 0) {
                    $tags[] = 'lab_komputer'; // Default tag
                }
                if ($sksLapangan > 0) {
                    $tags[] = 'lapangan';
                }

                // C. Simpan / Update
                Course::updateOrCreate(
                    [
                        // Kunci Unik (sesuai schema unique constraint anda)
                        'code' => $row['kode_mk'],
                        'kurikulum_id' => $kurikulum->id,
                    ],
                    [
                        'name' => $row['nama_mk'],
                        'semester' => $row['semester'],
                        'sks_teori' => $row['sks_teori'] ?? 0,
                        'sks_praktik' => $sksPraktik,
                        'sks_lapangan' => $sksLapangan,
                        'required_tags' => !empty($tags) ? json_encode($tags) : null,
                    ]
                );
                $count++;
            }

            $msg = "Berhasil sync $count mata kuliah.";
            if ($skipped > 0) $msg .= " ($skipped dilewati karena Kode Kurikulum tidak ditemukan).";

            return ['status' => true, 'message' => $msg];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Sinkronisasi Data Kurikulum
     */
    public function syncKurikulums()
    {
        try {
            /** @var Response $response */
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/kurikulum');

            if ($response->failed()) {
                return ['status' => false, 'message' => 'Gagal koneksi API: ' . $response->status()];
            }

            $dataSiakad = $response->json()['data'] ?? [];
            $count = 0;

            foreach ($dataSiakad as $row) {

                $prodi = Prodi::where('code', $row['kode_prodi'])->first();
                if (!$prodi) continue; // Skip jika prodi tidak ada

                // 2. Mapping Status Aktif
                // Asumsi API kirim boolean atau string "1"/"0"
                $isActive = filter_var($row['status_aktif'], FILTER_VALIDATE_BOOLEAN);

                // 3. Update or Create
                Kurikulum::updateOrCreate(
                    [
                        // KUNCI PENCARIAN (Agar tidak duplikat)
                        'code' => $row['kode_kurikulum'],
                        'prodi_id' => $prodi->id,
                    ],
                    [
                        // DATA YANG DIUPDATE
                        'name' => $row['nama_kurikulum'],
                        'tanggal' => $row['tanggal_berlaku'] ?? null,
                        'is_active' => $isActive,
                        // file_path dibiarkan null karena biasanya file SK diupload manual
                    ]
                );
                $count++;
            }

            return ['status' => true, 'message' => "Berhasil sinkronisasi $count kurikulum."];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}

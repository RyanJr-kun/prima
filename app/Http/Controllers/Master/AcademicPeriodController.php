<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Enums\DistributionStatus;

class AcademicPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periods = AcademicPeriod::orderBy('created_at', 'desc')->get();
        return view('content.master.academic.index', compact('periods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:academic_periods,name', // Contoh: "2025/2026 Ganjil"
        ]);

        AcademicPeriod::create([
            'name' => $request->name,
            'is_active' => false, // Default mati, harus diaktifkan manual
        ]);

        return back()->with('success', 'Periode Akademik berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $period = AcademicPeriod::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:academic_periods,name,' . $period->id,
        ]);

        $period->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Nama Periode berhasil diperbarui!');
    }

    public function setActive($id)
    {
        // 1. Matikan SEMUA periode dulu
        AcademicPeriod::query()->update(['is_active' => false]);

        // 2. Aktifkan periode yang dipilih
        $period = AcademicPeriod::findOrFail($id);
        $period->update(['is_active' => true]);

        return back()->with('success', "Periode {$period->name} sekarang AKTIF! Sistem berpindah ke semester ini.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $period = AcademicPeriod::findOrFail($id);
        if ($period->is_active) {
            return back()->with('error', 'Gagal Hapus: Periode ini sedang AKTIF. Aktifkan periode lain dulu sebelum menghapus ini.');
        }

        try {
            $period->delete();
            return back()->with('success', 'Periode berhasil dihapus.');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                return back()->with('error', 'Gagal Hapus: Data periode ini sudah digunakan (Ada Kelas/Distribusi di semester ini).');
            }
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }



    public function approveKaprodi($id)
    {
        $period = AcademicPeriod::findOrFail($id);
        $period->update([
            'distribution_kaprodi_id' => auth()->id(),
            'distribution_kaprodi_at' => now(),
            'distribution_status' => DistributionStatus::VERIFIED_KAPRODI
        ]);

        return back();
    }

    public function approveWadir1($id)
    {
        $period = AcademicPeriod::findOrFail($id);

        if ($period->distribution_status !== DistributionStatus::VERIFIED_WADIR1) {
            return back()->with('error', '  Belum disetujui Wadir 1!');
        }
    }
}

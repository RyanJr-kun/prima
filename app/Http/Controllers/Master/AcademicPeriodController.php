<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class AcademicPeriodController extends Controller
{
    public function index()
    {
        $periods = AcademicPeriod::orderBy('created_at', 'desc')->get();
        return view('content.master.academic.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:academic_periods,name',
        ]);

        AcademicPeriod::create([
            'name' => $request->name,
            'is_active' => false,
        ]);

        return back()->with('success', 'Periode Akademik berhasil ditambahkan!');
    }

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
        AcademicPeriod::query()->update(['is_active' => false]);

        $period = AcademicPeriod::findOrFail($id);
        $period->update(['is_active' => true]);

        return back()->with('success', "Periode {$period->name} sekarang AKTIF! Sistem berpindah ke semester ini.");
    }

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
}

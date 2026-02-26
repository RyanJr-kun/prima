<?php

namespace App\Http\Controllers\Master;

use App\Models\User;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class ProdiController extends Controller
{
    public function index()
    {
        $prodis = Prodi::with('kaprodi')
            ->orderBy('code', 'asc')
            ->get();

        $dosens = User::role(['dosen', 'kaprodi'])
            ->orderBy('name')
            ->get();

        return view('content.master.prodi.index', compact('prodis', 'dosens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'           => 'required|unique:prodis,code',
            'name'           => 'required|string',
            'jenjang'        => 'required|string',
            'lama_studi'     => 'required|numeric',
            'primary_campus' => 'required|in:kampus_1,kampus_2',
            'kaprodi_id'     => 'nullable|exists:users,id',
        ]);
        Prodi::create($request->all());

        return redirect()->route('master.program-studi.index')->with('success', 'Prodi berhasil disimpan!');
    }

    public function update(Request $request, string $id)
    {
        $prodi = Prodi::findOrFail($id);

        $request->validate([
            'code'           => ['required', Rule::unique('prodis', 'code')->ignore($id)],
            'name'           => 'required|string',
            'jenjang'        => 'required|string',
            'lama_studi'     => 'required|numeric',
            'primary_campus' => 'required|in:kampus_1,kampus_2',
            'kaprodi_id'     => 'nullable|exists:users,id',
        ]);

        $prodi->update($request->all());

        return redirect()->route('master.program-studi.index')->with('success', 'Program Studi berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        try {
            $prodis = Prodi::findOrFail($id);
            $prodis->delete();

            return redirect()->route('master.program-studi.index')
                ->with('success', 'Program Studi berhasil dihapus!');
        } catch (QueryException $e) {

            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('master.program-studi.index')
                    ->with('error', 'Gagal menghapus: Data Program Studi');
            }

            return redirect()->route('master.program-studi.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }
}

<?php

namespace App\Http\Controllers\Master;

use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class ProdiController extends Controller
{
    public function index()
    {
        $prodis = Prodi::all();
        $dosens = \App\Models\User::role('kaprodi')->get(); 
        return view('content.master.prodi.index', compact('prodis', 'dosens'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
         $request->validate([
            'code' => 'required|unique:prodis,code',
            'name' => 'required',
            'jenjang' => 'required',
            'lama_studi' => 'required|numeric',
            'kaprodi_id' => 'required|exists:users,id',
        ]);
        Prodi::create([
            'code' => $request->code,
            'name' => $request->name,
            'jenjang' => $request->jenjang,
            'kaprodi_id' => $request->kaprodi_id,
            'lama_studi' => $request->lama_studi,
        ]);        
        return redirect()->route('master.program-studi.index')->with('success', 'Prodi berhasil disimpan!');

    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $prodis = Prodi::findOrFail($id);

        $request->validate([
            'code' => ['required', Rule::unique('prodis', 'code')->ignore($id)],
            'name' => 'required',
            'jenjang' => 'required',
            'lama_studi' => 'required|numeric',
            'kaprodi_id' => 'required|exists:users,id',
        ]);

        $data = $request->all(); 

        $prodis->update($data);
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

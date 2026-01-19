<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Kurikulum;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $prodis = \App\Models\Prodi::all();
        $courses = Course::with('kurikulum')->get();
        $kurikulums = Kurikulum::where('is_active', true)->get();
        return view('content.master.courses.index', compact('courses','kurikulums','prodis'));
    }

    public function create()
    {
        return view('content.master.courses.create');
    }

    public function store(Request $request)
    {
        // Validasi Input SKS Pecahan
        $request->validate([
            'code' => 'required|unique:courses,code',
            'name' => 'required',
            'semester' => 'required|numeric',
            'kurikulum_id' => 'required',
            'prodi_id' => 'required',
            'sks_teori' => 'required|numeric|min:0',
            'sks_praktik' => 'required|numeric|min:0',
            'sks_lapangan' => 'required|numeric|min:0',
        ]);

        Course::create($request->all());
        return redirect()->route('master.courses.index')->with('success', 'Mata Kuliah tersimpan!');
    }

    public function edit(Request $request)
    {
        //
    }

    public function update(Request $request)
    {
        //
    }

    public function destroy(Request $request)
    {
        //
    }
}

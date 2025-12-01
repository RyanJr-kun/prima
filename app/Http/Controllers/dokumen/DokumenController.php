<?php

namespace App\Http\Controllers\dokumen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DokumenController extends Controller
{
    public function index()
    {
        return view('content.dokumen.index');
    }
}

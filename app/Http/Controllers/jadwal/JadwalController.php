<?php

namespace App\Http\Controllers\jadwal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
  {
    return view('content.jadwal.index');
  }
}

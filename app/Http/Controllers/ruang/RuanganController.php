<?php

namespace App\Http\Controllers\ruang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index()
  {
    return view('content.tempat.index');
  }
}

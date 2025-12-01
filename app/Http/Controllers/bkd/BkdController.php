<?php

namespace App\Http\Controllers\bkd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BkdController extends Controller
{
    public function index()
  {
    return view('content.bkd.index');
  }
}

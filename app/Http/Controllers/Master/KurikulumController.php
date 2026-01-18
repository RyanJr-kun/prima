<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KurikulumController extends Controller
{
    public function index()
  {
    return view('content.master.kurikulum.index');
  }

  public function create()
  {
    return view('content.kurikulum.create');
  }

  public function store(Request $request)
  {

  }

  public function edit(Request $request)
  {

  }

  public function update(Request $request)
  {

  }

  public function destroy(Request $request)
  {

  }
}

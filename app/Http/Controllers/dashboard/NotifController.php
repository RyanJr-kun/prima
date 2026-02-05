<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class NotifController extends Controller
{
  public function index()
  {
    $user = Auth::user();

    // Default settings jika null
    $settings = $user->notification_settings ?? [
      'notif_jadwal'   => true,
      'notif_bkd'      => true,
      'notif_approval' => true,
      'notif_login'    => false,
    ];

    // AMBIL DATA NOTIFIKASI (Pagination 5 atau 10 per halaman)
    $notifications = $user->notifications()->paginate(15);

    return view('content.dashboard.notifikasi', compact('user', 'settings', 'notifications'));
  }

  public function update(Request $request)
  {
    $user = User::find(Auth::id());

    $settings = [
      'notif_jadwal'   => $request->has('notif_jadwal'),
      'notif_bkd'      => $request->has('notif_bkd'),
      'notif_approval' => $request->has('notif_approval'),
      'notif_login'    => $request->has('notif_login'),
    ];

    $user->notification_settings = $settings;
    $user->save();

    return redirect()->back()->with('success', 'Preferensi notifikasi berhasil disimpan.');
  }

  public function markAllRead()
  {
    Auth::user()->unreadNotifications->markAsRead();
    return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
  }
}

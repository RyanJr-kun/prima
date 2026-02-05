<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class PengaturanController extends Controller
{
  public function index()
  {
    $user = Auth::user();
    return view('content.pengaturan.index', compact('user'));
  }

  public function update(Request $request)
  {
    $user = User::find(Auth::id());

    // 1. Validasi Dasar
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email,' . $user->id,
      'username' => 'required|string|unique:users,username,' . $user->id,
      'nidn' => 'nullable|string',
      'upload' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
      'signature' => 'nullable|image|mimes:png|max:1024',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->username = $request->username;
    $user->nidn = $request->nidn;

    if ($request->hasFile('upload')) {
      if ($user->profile_photo_path && Storage::exists('public/' . $user->profile_photo_path)) {
        Storage::delete('public/' . $user->profile_photo_path);
      }
      $path = $request->file('upload')->store('avatars', 'public');
    }

    if ($user->hasAnyRole(['direktur', 'wadir1', 'wadir2', 'wadir3', 'kaprodi'])) {
      if ($request->hasFile('signature')) {
        if ($user->signature_path && Storage::exists('public/' . $user->signature_path)) {
          Storage::delete('public/' . $user->signature_path);
        }

        $sigPath = $request->file('signature')->store('signatures', 'public');
        $user->signature_path = $sigPath;
      }
    }

    if ($request->filled('new_password')) {
      $request->validate([
        'new_password' => 'min:6|confirmed',
      ]);
      $user->password = Hash::make($request->new_password);
    }

    $user->save();

    return redirect()->back()->with('success', 'Profile berhasil diperbarui!');
  }

  public function deleteAvatar()
  {
    $user = User::find(Auth::id());

    if ($user->profile_photo_path) {
      if (Storage::disk('public')->exists($user->profile_photo_path)) {
        Storage::disk('public')->delete($user->profile_photo_path);
      }
      $user->profile_photo_path = null;
      $user->save();
    }

    return back()->with('success', 'Foto profil berhasil dihapus.');
  }
}

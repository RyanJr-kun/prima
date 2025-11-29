<?php

namespace App\Http\Controllers\authentications;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('content.authentications.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $apiUrl = '/api/mock-login';

        try {
            $response = Http::post($apiUrl, [
                'username' => $request->username,
                'password' => $request->password,
            ]);

            // Cek jika API menjawab SUKSES (Biasanya status 200)
            if ($response->successful()) {

                $dataApi = $response->json();

                // struktur JSON dari API:
                // {
                //    "status": "success",
                //    "data": {
                //        "nidn_nim": "12345",
                //        "nama_lengkap": "Budi Santoso",
                //        "role": "Dosen",
                //        "email": "budi@polinus.ac.id"
                //    }
                // }

                // --- UPDATE ATAU BUAT USER LOKAL ---
                // Logikanya: Jika user sudah pernah login, update datanya.
                // Jika baru pertama kali, buat user baru di database lokal.

                $user = User::updateOrCreate(
                    ['username' => $dataApi['data']['nidn_nim']],
                    [
                        'name' => $dataApi['data']['nama_lengkap'],
                        'email' => $dataApi['data']['email'] ?? null,
                        'role_siakad' => $dataApi['data']['role'],
                        'password' => null,
                    ]
                );

                Auth::login($user);
                return redirect()->intended('/')->with('success', 'Selamat Datang, ' . $user->name);

            } else {
                return back()->withErrors(['username' => 'Login Gagal! Cek Username/Password SI-AKAD.']);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['username' => 'Terjadi kesalahan koneksi ke server SI-AKAD.']);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

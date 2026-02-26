<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('content.authentications.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginValue = $request->input('login');
        $password = $request->input('password');

        $user = User::where('username', $loginValue)
            ->orWhere('email', $loginValue)
            ->orWhere('nidn', $loginValue)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);

            $request->session()->regenerate();
            return redirect()->intended('/Dashboard')->with('success', 'Selamat Datang, ' . Auth::user()->name);
        }

        return back()->withErrors([
            'login' =>  __('auth.failed'), // atau __('auth.failed')
        ])->onlyInput('login'); // Ubah dari 'username' menjadi 'login' agar value tetap bertahan di form
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

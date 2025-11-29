<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/mock-login', function (Request $request) {
    // Ambil input
    $username = $request->input('username');
    $password = $request->input('password');

    // Skenario 1: Login sebagai MAHASISWA
    if ($username === 'MHS001' && $password === '123456') {
        return response()->json([
            'status' => 'success',
            'message' => 'Login Berhasil',
            'data' => [
                'id_user' => 'MHS001',
                'nama_lengkap' => 'Ahmad Mahasiswa',
                'email' => 'ahmad@mhs.polinus.ac.id',
                'role' => 'mahasiswa', // Role penting!
                'prodi' => 'Teknologi Informasi'
            ]
        ], 200);
    }

    // Skenario 2: Login sebagai DOSEN
    if ($username === 'DSN001' && $password === '123456') {
        return response()->json([
            'status' => 'success',
            'message' => 'Login Berhasil',
            'data' => [
                'id_user' => 'DSN001',
                'nama_lengkap' => 'Budi Santoso, M.Kom',
                'email' => 'budi@dosen.polinus.ac.id',
                'role' => 'dosen',
                'nidn' => '06123456'
            ]
        ], 200);
    }

    // Skenario 3: Login sebagai BAAK (Admin)
    if ($username === 'BAAK001' && $password === 'admin123') {
        return response()->json([
            'status' => 'success',
            'message' => 'Login Berhasil',
            'data' => [
                'id_user' => 'BAAK001',
                'nama_lengkap' => 'Staff Administrasi',
                'email' => 'baak@polinus.ac.id',
                'role' => 'baak',
            ]
        ], 200);
    }

    // Skenario Gagal
    return response()->json([
        'status' => 'error',
        'message' => 'Username atau Password Salah!'
    ], 401);
});

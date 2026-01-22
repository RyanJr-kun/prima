<?php

namespace App\Http\Controllers\authentications;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $data = User::with('roles')->orderBy('id', 'DESC')->get();
        $roles = Role::pluck('name', 'name')->all();
        return view('content.authentications.user', compact('data', 'roles'));
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'nidn' => 'nullable|unique:users,nidn',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'userRole' => 'required|array',
            'signature_path' => 'nullable|image|mimes:png,webp|max:3072', 
            'status' => 'required|boolean',
        ]);

        $input['password'] = Hash::make($input['password']);

        if ($request->hasFile('signature_path')) {
            $input['signature_path'] = $request->file('signature_path')->store('signatures', 'public');
        }

        $user = User::create($input);
        $user->assignRole($request->input('userRole'));


        return redirect()->route('user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $input = $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username,'.$id,
            'nidn' => 'nullable|unique:users,nidn',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|same:confirm-password',
            'userRole' => 'required|array',
            'signature_path' => 'nullable|image|mimes:png,webp|max:3072', 
            'status' => 'required|boolean',
        ]);

        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input, array('password'));
        }

        $user = User::find($id);

        if ($request->hasFile('signature_path')) {
            if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $filePath = $request->file('signature_path')->store('signatures', 'public');
            $input['signature_path'] = $filePath;
        }

        $user->update($input);

        // syncRoles otomatis menghapus role lama yang tidak dipilih & pasang yang baru
        $user->syncRoles($request->input('userRole'));

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
       try {
            $user = User::findOrFail($id);

            if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                 Storage::disk('public')->delete($user->signature_path);
            }

            $user->delete();

            return redirect()->route('user.index')
                ->with('success', 'User berhasil dihapus!');

        } catch (QueryException $e) {
           
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('user.index')
                    ->with('error', 'Gagal menghapus: Data User ini masih digunakan oleh data lain.');
            }

            return redirect()->route('user.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
  }

    public function syncSiakad()
    {
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer TOKEN_RAHASIA_DARI_MAS_ANDRE', 
            ])->get('#link_api');
            
            if ($response->failed()) {
                return back()->with('error', 'Gagal koneksi ke SIAKAD.');
            }

            $dosenSiakad = $response->json();
            $updatedCount = 0;
            $createdCount = 0;

            // 2. Looping Data dari API
            foreach ($dosenSiakad as $dosen) {
                
                // Bersihkan nama (trim spasi) agar pencocokan akurat
                $namaApi = trim($dosen['nama']); 
                
                // CARI: Apakah dosen ini sudah ada di database lokal kita?
                // Kita cari berdasarkan NAMA (karena NIDN mungkin kosong di lokal)
                $localUser = User::where('name', 'LIKE', $namaApi)->first();

                if ($localUser) {
                    // SKENARIO A: User Sudah Ada (dari Seeder)
                    // Cek apakah NIDN atau Email masih kosong/null?
                    $needUpdate = false;

                    if (empty($localUser->nidn) && !empty($dosen['nidn'])) {
                        $localUser->nidn = $dosen['nidn'];
                        $needUpdate = true;
                    }
                    
                    if (empty($localUser->email) && !empty($dosen['email'])) {
                        // Pastikan email dari API tidak duplikat dengan user lain
                        $emailExists = User::where('email', $dosen['email'])->where('id', '!=', $localUser->id)->exists();
                        if (!$emailExists) {
                            $localUser->email = $dosen['email'];
                            $needUpdate = true;
                        }
                    }

                    if ($needUpdate) {
                        $localUser->save();
                        $updatedCount++;
                    }

                } else {
                    // SKENARIO B: User Belum Ada (Dosen Baru di SIAKAD)
                    // Opsional: Anda mau buat user baru atau abaikan?
                    // Jika ingin buat baru:
                    User::create([
                        'name' => $namaApi,
                        'nidn' => $dosen['nidn'],
                        'email' => $dosen['email'] ?? str_replace(' ', '', strtolower($namaApi)) . '@default.com', // Email dummy jika kosong
                        'password' => Hash::make('dosen123'), // Password Default
                        'role' => 'dosen', // Sesuaikan dengan role Anda
                    ]);
                    $createdCount++;
                }
            }

            return back()->with('success', "Sinkronisasi Selesai. $updatedCount data diperbarui, $createdCount data baru ditambahkan.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
    
}

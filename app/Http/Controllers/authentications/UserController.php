<?php

namespace App\Http\Controllers\authentications;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
    
}

<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'userRole' => 'required|array' // Pastikan input berupa Array
        ]);

        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);

        // Gunakan assignRole untuk array
        $user->assignRole($request->input('userRole'));

        return redirect()->route('user')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $input = $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username,'.$id,
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|same:confirm-password',
            'userRole' => 'required|array' // Pastikan input berupa Array
        ]);

        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input, array('password'));
        }

        $user = User::find($id);
        $user->update($input);

        // syncRoles otomatis menghapus role lama yang tidak dipilih & pasang yang baru
        $user->syncRoles($request->input('userRole'));

        return redirect()->route('user')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        User::find($id)->delete();
        return redirect()->route('user')->with('success', 'User berhasil dihapus.');
    }
}

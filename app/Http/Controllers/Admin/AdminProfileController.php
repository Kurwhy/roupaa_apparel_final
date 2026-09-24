<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminProfileController extends Controller
{
    public function index()
    {
        return view('admin.profile.index');
    }

    public function getData()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $profile = $user->role === 'owner'
            ? $user->load('owner')->owner
            : $user->load('admin')->admin;

        return response()->json([
            'nama_lengkap' => $profile?->nama_lengkap,
            'email'        => $user->email,
            'no_telepon'   => $profile?->no_telepon,
            'nip_karyawan' => $profile?->nip_karyawan ?? null,
            'divisi'       => $profile?->divisi       ?? null,
            'role'         => $user->role,
            'member_since' => $user->created_at->translatedFormat('d F Y'),
        ]);
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|min:3|max:255|regex:/^[\p{L}\s\'\-]+$/u',
            'no_telepon'   => 'required|digits_between:10,13',
            'email'        => 'required|email|max:255|unique:users,email,' . $user->id,
        ], [
            'nama_lengkap.required'      => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min'           => 'Nama minimal 3 karakter.',
            'nama_lengkap.regex'         => 'Nama hanya boleh huruf dan spasi.',
            'no_telepon.required'        => 'Nomor telepon wajib diisi.',
            'no_telepon.digits_between'  => 'Nomor telepon harus 10-13 digit angka.',
            'email.required'             => 'Email wajib diisi.',
            'email.email'                => 'Format email tidak valid.',
            'email.unique'               => 'Email sudah digunakan akun lain.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update(['email' => $request->email]);

        if ($user->role === 'owner') {
            $user->owner()->update([
                'nama_lengkap' => $request->nama_lengkap,
                'no_telepon'   => $request->no_telepon,
            ]);
        } else {
            $user->admin()->update([
                'nama_lengkap' => $request->nama_lengkap,
                'no_telepon'   => $request->no_telepon,
            ]);
        }

        return response()->json(['message' => 'Profil berhasil diperbarui.']);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password'      => 'required|string',
            'password'              => [
                'required',
                'string',
                'min:8',
                'max:100',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d).+$/'
            ],
            'password_confirmation' => 'required',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password minimal 8 karakter.',
            'password.regex'            => 'Password harus mengandung minimal satu huruf besar dan satu angka.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['Password saat ini tidak sesuai.']]
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = auth('api')->user();
        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        return view('customer.profile.index');
    }

    public function getData()
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();
        $user->load('pelanggan');

        return response()->json([
            'email'               => $user->email,
            'member_since'        => $user->created_at->translatedFormat('d F Y'),
            'nama_lengkap'        => $user->pelanggan?->nama_lengkap,
            'no_whatsapp'         => $user->pelanggan?->no_whatsapp,
            'nama_instansi_brand' => $user->pelanggan?->nama_instansi_brand,
            'alamat_pengiriman'   => $user->pelanggan?->alamat_pengiriman,
        ]);
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'nama_lengkap'        => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'no_whatsapp'         => 'required|digits_between:10,13',
            'email'               => 'required|email|max:255|unique:users,email,' . $user->id,
            'nama_instansi_brand' => 'nullable|string|max:255',
            'alamat_pengiriman'   => 'nullable|string|max:1000',
        ], [
            'nama_lengkap.required'      => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min'           => 'Nama minimal 3 karakter.',
            'nama_lengkap.regex'         => 'Nama hanya boleh huruf, spasi, tanda hubung, dan apostrof.',
            'no_whatsapp.required'       => 'Nomor WhatsApp wajib diisi.',
            'no_whatsapp.digits_between' => 'Nomor WhatsApp harus 10-13 digit angka.',
            'email.required'             => 'Email wajib diisi.',
            'email.email'                => 'Format email tidak valid.',
            'email.unique'               => 'Email sudah digunakan akun lain.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update(['email' => $request->email]);

        $user->pelanggan()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_lengkap'        => $request->nama_lengkap,
                'no_whatsapp'         => $request->no_whatsapp,
                'nama_instansi_brand' => $request->nama_instansi_brand ?: null,
                'alamat_pengiriman'   => $request->alamat_pengiriman   ?: null,
            ]
        );

        return response()->json(['message' => 'Profil berhasil diperbarui!']);
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
        $user = Auth::guard('api')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['Password saat ini tidak sesuai.']]
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}

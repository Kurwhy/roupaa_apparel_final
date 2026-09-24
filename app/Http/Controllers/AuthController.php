<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8|max:100',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        if (!$token = $guard->attempt($validator->validated())) {
            return response()->json(['error' => 'Email atau kata sandi salah.'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'no_whatsapp'  => 'required|digits_between:10,13',
            'email'        => 'required|email|max:255|unique:users',
            'password'     => [
                'required',
                'string',
                'min:8',
                'max:100',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d).+$/'
            ],
        ], [
            'nama_lengkap.required'      => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min'           => 'Nama minimal 3 karakter.',
            'nama_lengkap.regex'         => 'Nama hanya boleh huruf, spasi, tanda hubung, dan apostrof.',
            'no_whatsapp.required'       => 'Nomor WhatsApp wajib diisi.',
            'no_whatsapp.digits_between' => 'Nomor WhatsApp harus 10-13 digit angka.',
            'email.required'             => 'Email wajib diisi.',
            'email.email'                => 'Format email tidak valid.',
            'email.unique'               => 'Email sudah terdaftar.',
            'password.required'          => 'Password wajib diisi.',
            'password.min'               => 'Password minimal 8 karakter.',
            'password.regex'             => 'Password harus mengandung minimal satu huruf besar dan satu angka.',
            'password.confirmed'         => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => 'pelanggan',
                ]);

                Pelanggan::create([
                    'user_id'      => $user->id,
                    'nama_lengkap' => $request->nama_lengkap,
                    'no_whatsapp'  => $request->no_whatsapp,
                ]);
            });

            return response()->json(['message' => 'Akun berhasil dibuat! Silakan masuk.'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mendaftarkan akun.'], 500);
        }
    }

    public function logout()
    {
        try {
            /** @var \Tymon\JWTAuth\JWTGuard $guard */
            $guard = auth('api');
            $guard->logout();
            return response()->json(['message' => 'Berhasil keluar.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Berhasil keluar dari sesi lokal.'], 200);
        }
    }

    protected function respondWithToken($token)
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $user  = $guard->user();

        $namaLengkap = '';
        if ($user->role === 'pelanggan' && $user->pelanggan) {
            $namaLengkap = $user->pelanggan->nama_lengkap;
        } elseif ($user->role === 'admin' && $user->admin) {
            $namaLengkap = $user->admin->nama_lengkap;
        } elseif ($user->role === 'owner' && $user->owner) {
            $namaLengkap = $user->owner->nama_lengkap;
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $guard->factory()->getTTL() * 60,
            'user'         => [
                'id'           => $user->id,
                'email'        => $user->email,
                'role'         => $user->role,
                'nama_lengkap' => $namaLengkap,
            ]
        ]);
    }
}

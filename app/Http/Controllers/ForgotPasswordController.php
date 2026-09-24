<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.exists'   => 'Email tidak terdaftar dalam sistem.',
        ]);

        $email = $request->email;

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $email,
            'token'      => bcrypt($token),
            'created_at' => Carbon::now(),
        ]);

        $user = User::where('email', $email)->first();

        $nama = match ($user->role) {
            'pelanggan' => $user->pelanggan?->nama_lengkap,
            'admin'     => $user->admin?->nama_lengkap,
            'owner'     => $user->owner?->nama_lengkap,
            default     => null,
        } ?? 'Pengguna';

        Mail::to($email)->send(new ResetPasswordMail($nama, $token, $email));

        return back()->with('success', 'Link reset password telah dikirim ke email kamu. Silakan cek inbox atau folder spam.');
    }

    public function showResetForm(Request $request, string $token)
    {
        $email = $request->query('email');

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !password_verify($token, $record->token)) {
            return redirect('/forgot-password')->withErrors([
                'token' => 'Link reset password tidak valid atau sudah digunakan.',
            ]);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect('/forgot-password')->withErrors([
                'token' => 'Link reset password telah kedaluwarsa. Silakan minta link baru.',
            ]);
        }
    
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => ['required', 'email', 'exists:users,email'],
            'token'                 => ['required'],
            'password'              => [
                'required',
                'min:8',
                'max:100',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d).+$/'
            ],
            'password_confirmation' => ['required'],
        ], [
            'email.exists'       => 'Email tidak ditemukan.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.regex'     => 'Password harus mengandung minimal satu huruf besar dan satu angka.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !password_verify($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Link reset password tidak valid atau sudah digunakan.']);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'Link reset password telah kedaluwarsa. Silakan minta link baru.']);
        }

        User::where('email', $request->email)->update([
            'password' => bcrypt($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect('/login')->with('success', 'Password berhasil diubah. Silakan login dengan password baru kamu.');
    }
}

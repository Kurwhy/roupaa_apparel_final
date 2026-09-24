<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class AdminManagementController extends Controller
{
    public function index()
    {
        return view('owner.admins.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $onlyTrashed = $request->boolean('trashed', false);

        $query = $onlyTrashed
            ? Admin::onlyTrashed()->with('user')
            : Admin::with('user');

        $admins = $query->orderByDesc('created_at')->get();

        return response()->json([
            'admins' => $admins->map(fn($a) => [
                'id'               => $a->id,
                'nama_lengkap'     => $a->nama_lengkap,
                'email'            => $a->user?->email ?? '-',
                'no_telepon'       => $a->no_telepon,
                'nip_karyawan' => $a->nip_karyawan ?? '-',
                'divisi'           => $a->divisi,
                'tanggal_bergabung' => $a->created_at?->format('d M Y') ?? '-',
                'deleted_at'       => $a->deleted_at?->format('d M Y') ?? null,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'email'        => 'required|email|max:255|unique:users,email',
            'password'     => ['required', 'string', 'min:8', 'max:100', 'regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
            'no_telepon'   => 'required|digits_between:10,13',
            'divisi'       => 'required|string|max:100',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min'      => 'Nama minimal 3 karakter.',
            'nama_lengkap.regex'    => 'Nama hanya boleh huruf, spasi, tanda hubung, dan apostrof.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah terdaftar.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.regex'        => 'Password harus mengandung minimal satu huruf besar dan satu angka.',
            'no_telepon.required'   => 'Nomor telepon wajib diisi.',
            'no_telepon.digits_between' => 'Nomor telepon harus 10-13 digit angka.',
            'divisi.required'       => 'Divisi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::transaction(function () use ($request) {
            $user = User::create([
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'admin',
            ]);

            Admin::create([
                'user_id'      => $user->id,
                'nama_lengkap' => $request->nama_lengkap,
                'no_telepon'   => $request->no_telepon,
                'nip_karyawan' => $this->generateNip(),
                'divisi'       => $request->divisi,
            ]);
        }, 3);

        return response()->json(['message' => 'Akun admin berhasil ditambahkan.']);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $admin = Admin::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'email'        => 'required|email|max:255|unique:users,email,' . $admin->user_id,
            'no_telepon'   => 'required|digits_between:10,13',
            'divisi'       => 'required|string|max:100',
            'password'     => ['nullable', 'string', 'min:8', 'max:100', 'regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min'      => 'Nama minimal 3 karakter.',
            'nama_lengkap.regex'    => 'Nama hanya boleh huruf, spasi, tanda hubung, dan apostrof.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah terdaftar.',
            'no_telepon.required'   => 'Nomor telepon wajib diisi.',
            'no_telepon.digits_between' => 'Nomor telepon harus 10-13 digit angka.',
            'divisi.required'       => 'Divisi wajib diisi.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.regex'        => 'Password harus mengandung minimal satu huruf besar dan satu angka.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::transaction(function () use ($request, $admin) {
            $admin->update([
                'nama_lengkap' => $request->nama_lengkap,
                'no_telepon'   => $request->no_telepon,
                'divisi'       => $request->divisi,
            ]);

            $userData = ['email' => $request->email];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $admin->user->update($userData);
        });

        return response()->json(['message' => 'Data admin berhasil diperbarui.']);
    }

    public function destroy($id): JsonResponse
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Akun admin berhasil dihapus.']);
    }

    public function restore($id): JsonResponse
    {
        $admin = Admin::withTrashed()->findOrFail($id);
        $admin->restore();

        return response()->json(['message' => 'Akun admin berhasil dipulihkan.']);
    }

    private function generateNip(): string
    {
        // lockForUpdate memastikan hanya satu transaction yang bisa baca+generate NIP secara bersamaan
        $last = Admin::withTrashed()
            ->whereNotNull('nip_karyawan')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('nip_karyawan');

        if (!$last) return 'OPS-001';

        $number = (int) substr($last, 4);
        return 'OPS-' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }
}

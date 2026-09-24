<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index()
    {
        return view('admin.customers.index');
    }

    public function show($id)
    {
        $customer = Pelanggan::with(['user', 'orders'])->findOrFail($id);
        return view('admin.customers.show', compact('customer'));
    }

    public function getData(Request $request): JsonResponse
    {
        $onlyTrashed = $request->boolean('trashed', false);

        $query = $onlyTrashed
            ? Pelanggan::onlyTrashed()->with('user')->withCount('orders')
            : Pelanggan::with('user')->withCount('orders');

        $customers = $query->orderByDesc('created_at')->get();

        return response()->json([
            'customers' => $customers->map(fn($c) => [
                'id'                  => $c->id,
                'nama_lengkap'        => $c->nama_lengkap,
                'email'               => $c->user?->email ?? '-',
                'no_whatsapp'         => $c->no_whatsapp,
                'nama_instansi_brand' => $c->nama_instansi_brand ?? '-',
                'orders_count'        => $c->orders_count,
                'tanggal_bergabung'   => $c->created_at?->format('d M Y') ?? '-',
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap'        => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'email'               => 'required|email|max:255|unique:users,email',
            'password'            => ['required', 'string', 'min:8', 'max:100', 'regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
            'no_whatsapp'         => 'required|digits_between:10,13',
            'nama_instansi_brand' => 'nullable|string|max:255',
            'alamat_pengiriman'   => 'nullable|string|max:1000',
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
            'no_whatsapp.required'  => 'Nomor WhatsApp wajib diisi.',
            'no_whatsapp.digits_between' => 'Nomor WhatsApp harus 10-13 digit angka.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::transaction(function () use ($request) {
            $user = User::create([
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'pelanggan',
            ]);

            Pelanggan::create([
                'user_id'             => $user->id,
                'nama_lengkap'        => $request->nama_lengkap,
                'no_whatsapp'         => $request->no_whatsapp,
                'nama_instansi_brand' => $request->nama_instansi_brand,
                'alamat_pengiriman'   => $request->alamat_pengiriman,
            ]);
        });

        return response()->json(['message' => 'Akun pelanggan berhasil ditambahkan.']);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_lengkap'        => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'email'               => 'required|email|max:255|unique:users,email,' . $pelanggan->user_id,
            'no_whatsapp'         => 'required|digits_between:10,13',
            'nama_instansi_brand' => 'nullable|string|max:255',
            'alamat_pengiriman'   => 'nullable|string|max:1000',
            'password'            => ['nullable', 'string', 'min:8', 'max:100', 'regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min'      => 'Nama minimal 3 karakter.',
            'nama_lengkap.regex'    => 'Nama hanya boleh huruf, spasi, tanda hubung, dan apostrof.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah terdaftar.',
            'no_whatsapp.required'  => 'Nomor WhatsApp wajib diisi.',
            'no_whatsapp.digits_between' => 'Nomor WhatsApp harus 10-13 digit angka.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.regex'        => 'Password harus mengandung minimal satu huruf besar dan satu angka.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::transaction(function () use ($request, $pelanggan) {
            $pelanggan->update([
                'nama_lengkap'        => $request->nama_lengkap,
                'no_whatsapp'         => $request->no_whatsapp,
                'nama_instansi_brand' => $request->nama_instansi_brand,
                'alamat_pengiriman'   => $request->alamat_pengiriman,
            ]);

            $userData = ['email' => $request->email];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $pelanggan->user->update($userData);
        });

        return response()->json(['message' => 'Data pelanggan berhasil diperbarui.']);
    }

    public function destroy($id): JsonResponse
    {
        $pelanggan = Pelanggan::findOrFail($id);

        if ($pelanggan->orders()->whereNotIn('status', ['selesai', 'dibatalkan'])->exists()) {
            return response()->json(['message' => 'Pelanggan masih memiliki pesanan aktif.'], 422);
        }

        $pelanggan->delete();
        return response()->json(['message' => 'Akun pelanggan berhasil dihapus.']);
    }

    public function restore($id): JsonResponse
    {
        $pelanggan = Pelanggan::withTrashed()->findOrFail($id);
        $pelanggan->restore();
        return response()->json(['message' => 'Akun pelanggan berhasil dipulihkan.']);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Admin;
use App\Models\Owner;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $defaultPassword = Hash::make('Password1');

        $userPelanggan = User::create([
            'email' => 'firmansyahw503@gmail.com',
            'password' => $defaultPassword,
            'role' => 'pelanggan'
        ]);
        Pelanggan::create([
            'user_id' => $userPelanggan->id,
            'nama_lengkap' => 'Wahyu Firmansyah',
            'no_whatsapp' => '081234567890',
            'nama_instansi_brand' => 'Universitas Muhammadiyah Yogyakarta',
            'alamat_pengiriman' => 'Jl. Brawijaya, Kasihan, Bantul, DIY'
        ]);

        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'email' => "customer{$i}@gmail.com",
                'password' => $defaultPassword,
                'role' => 'pelanggan'
            ]);
            Pelanggan::create([
                'user_id' => $user->id,
                'nama_lengkap' => $faker->name,
                'no_whatsapp' => '08' . $faker->numerify('##########'),
                'nama_instansi_brand' => $faker->company,
                'alamat_pengiriman' => $faker->address
            ]);
        }

        $userAdmin = User::create([
            'email' => 'admin@gmail.com',
            'password' => $defaultPassword,
            'role' => 'admin'
        ]);
        Admin::create([
            'user_id' => $userAdmin->id,
            'nama_lengkap' => 'Kang Sablon',
            'no_telepon' => '08999999999',
            'nip_karyawan' => 'OPS-001',
            'divisi' => 'Kepala Produksi'
        ]);

        for ($i = 2; $i <= 6; $i++) {
            $user = User::create([
                'email' => "admin{$i}@gmail.com",
                'password' => $defaultPassword,
                'role' => 'admin'
            ]);
            Admin::create([
                'user_id' => $user->id,
                'nama_lengkap' => $faker->name,
                'no_telepon' => '08' . $faker->numerify('##########'),
                'nip_karyawan' => 'OPS-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'divisi' => 'Staff Produksi'
            ]);
        }

        $userOwner = User::create([
            'email' => 'owner@gmail.com',
            'password' => $defaultPassword,
            'role' => 'owner'
        ]);
        Owner::create([
            'user_id' => $userOwner->id,
            'nama_lengkap' => 'Bos Roupaa',
            'no_telepon' => '08111111111'
        ]);
    }
}

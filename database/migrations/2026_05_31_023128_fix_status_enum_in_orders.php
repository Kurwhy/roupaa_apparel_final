<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY status VARCHAR(30) NOT NULL DEFAULT 'diskusi_desain'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY status ENUM('diskusi_desain','menunggu_spesifikasi','menunggu_estimasi','menunggu_pembayaran','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'diskusi_desain'");
    }
};

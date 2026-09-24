<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_type VARCHAR(20) NULL");

        Schema::table('orders', function (Blueprint $table) {
            $table->json('production_photo_path')->nullable()->after('payment_proof_path');
            $table->text('production_notes')->nullable()->after('production_photo_path');
            $table->timestamp('completed_at')->nullable()->after('payment_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['production_photo_path', 'production_notes', 'completed_at']);
        });

        DB::statement("ALTER TABLE orders MODIFY payment_type ENUM('dp','lunas') NULL");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_proof_path')->nullable()->after('final_price');

            $table->timestamp('payment_confirmed_at')->nullable()->after('payment_proof_path');

            $table->foreignId('confirmed_by')
                ->nullable()
                ->after('payment_confirmed_at')
                ->constrained('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['payment_proof_path', 'payment_confirmed_at', 'confirmed_by']);
        });
    }
};
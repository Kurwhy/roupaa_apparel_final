<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('restock_batches', function (Blueprint $table) {
            $table->decimal('total_pengeluaran', 12, 2)->nullable()->after('items_data');
            $table->string('struk_path')->nullable()->after('total_pengeluaran');
            $table->foreignId('verified_by')->nullable()->after('struk_path')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('restock_batches', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['total_pengeluaran', 'struk_path', 'verified_by']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('pelanggan_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('project_name');
            $table->text('design_notes')->nullable();
            $table->string('reference_file_path')->nullable();
            $table->string('final_design_path')->nullable();
            $table->string('final_mockup_path')->nullable();
            $table->boolean('is_design_approved')->default(false);

            $table->integer('total_quantity')->default(0);
            $table->decimal('estimated_price', 15, 2)->nullable();
            $table->decimal('final_price', 15, 2)->nullable();
            $table->enum('status', [
                'diskusi_desain',
                'menunggu_spesifikasi',
                'menunggu_estimasi',
                'menunggu_pembayaran',
                'diproses',
                'selesai',
                'dibatalkan'
            ])->default('diskusi_desain');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

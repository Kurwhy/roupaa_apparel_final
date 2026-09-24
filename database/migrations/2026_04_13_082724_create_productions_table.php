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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->enum('stage', ['antrean', 'sablon', 'finishing', 'selesai'])->default('antrean');
            $table->integer('progress_percentage')->default(0);
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->date('start_date')->nullable();
            $table->date('deadline_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};

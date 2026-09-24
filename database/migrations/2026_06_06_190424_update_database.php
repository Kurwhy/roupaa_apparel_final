<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('production_logs');
        Schema::dropIfExists('productions');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_confirmed_by_foreign');

            $table->dropColumn([
                'final_design_path',
                'payment_proof_path',
                'confirmed_by',
                'final_price',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('estimated_price', 'final_price');
        });

        DB::statement("
            ALTER TABLE `orders`
            MODIFY `payment_type`
            ENUM('dp', 'lunas', 'pelunasan') NULL DEFAULT NULL
        ");

        Schema::table('pelanggans', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::create('portofolios', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portofolios');

        Schema::table('admins', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        DB::statement("
            ALTER TABLE `orders`
            MODIFY `payment_type` VARCHAR(20) NULL DEFAULT NULL
        ");

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('final_price', 'estimated_price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('final_design_path')->nullable()->after('reference_file_path');
            $table->string('payment_proof_path')->nullable();
            $table->decimal('final_price', 15, 2)->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->foreign('confirmed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->enum('stage', ['antrean', 'sablon', 'finishing', 'selesai'])->default('antrean');
            $table->integer('progress_percentage')->default(0);
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->date('start_date')->nullable();
            $table->date('deadline_date')->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('admin_id');
            $table->string('action');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('production_id')->references('id')->on('productions')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }
};

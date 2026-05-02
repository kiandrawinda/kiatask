<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->default('general');
            $table->decimal('target_value', 10, 2)->default(100);
            $table->decimal('current_value', 10, 2)->default(0);
            $table->string('unit')->default('%');
            $table->date('deadline')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
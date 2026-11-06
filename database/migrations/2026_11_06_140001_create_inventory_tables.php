<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('draft');
            $table->date('counted_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('system_quantity');
            $table->unsignedInteger('counted_quantity');
            $table->integer('difference');
            $table->timestamps();
            $table->unique(['inventory_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_lines');
        Schema::dropIfExists('inventories');
    }
};

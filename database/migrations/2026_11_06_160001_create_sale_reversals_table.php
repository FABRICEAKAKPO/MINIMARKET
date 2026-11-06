<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_reversals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('type', 20)->default('cancellation');
            $table->text('reason');
            $table->timestamps();
            $table->unique(['sale_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_reversals');
    }
};

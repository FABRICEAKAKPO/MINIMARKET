<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('open');
            $table->unsignedBigInteger('opening_amount_xof')->default(0);
            $table->unsignedBigInteger('expected_amount_xof')->default(0);
            $table->unsignedBigInteger('closing_amount_xof')->nullable();
            $table->integer('difference_xof')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->text('closing_note')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cash_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('number')->unique();
            $table->string('status', 20)->default('completed');
            $table->unsignedBigInteger('subtotal_xof');
            $table->unsignedBigInteger('tax_xof')->default(0);
            $table->unsignedBigInteger('discount_xof')->default(0);
            $table->integer('rounding_xof')->default(0);
            $table->unsignedBigInteger('total_xof');
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->index(['cash_session_id', 'created_at']);
        });

        Schema::create('sale_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_xof');
            $table->unsignedBigInteger('unit_cost_xof');
            $table->unsignedBigInteger('tax_xof')->default(0);
            $table->unsignedBigInteger('discount_xof')->default(0);
            $table->unsignedBigInteger('line_total_xof');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->string('method', 30);
            $table->unsignedBigInteger('amount_xof');
            $table->string('reference')->nullable();
            $table->string('status', 20)->default('confirmed');
            $table->timestamps();
            $table->index(['method', 'created_at']);
        });

        Schema::create('cash_counts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cash_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('counted_amount_xof');
            $table->integer('difference_xof');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_counts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sale_lines');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('cash_sessions');
    }
};

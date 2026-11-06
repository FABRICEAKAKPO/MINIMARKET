<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('credit_limit_xof')->default(0);
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->boolean('consent_recorded')->default(false);
            $table->timestamp('consent_recorded_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['name', 'phone']);
        });

        Schema::create('credit_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('balance_xof')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->date('credit_due_at')->nullable()->after('total_xof');
        });

        Schema::create('credit_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('credit_account_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->unsignedBigInteger('amount_xof');
            $table->unsignedBigInteger('balance_after_xof');
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 80);
            $table->unsignedBigInteger('amount_xof');
            $table->string('payment_method', 30);
            $table->date('spent_at');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('credit_entries');
        Schema::table('sales', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn('credit_due_at');
        });
        Schema::dropIfExists('credit_accounts');
        Schema::dropIfExists('customers');
    }
};

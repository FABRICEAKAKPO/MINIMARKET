<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['cash_session_id', 'user_id', 'customer_id', 'number', 'status', 'subtotal_xof', 'tax_xof', 'discount_xof', 'rounding_xof', 'total_xof', 'credit_due_at', 'cancellation_reason'];
    public function cashSession(): BelongsTo { return $this->belongsTo(CashSession::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function lines(): HasMany { return $this->hasMany(SaleLine::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function reversals(): HasMany { return $this->hasMany(SaleReversal::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'cash_session_id', 'category', 'amount_xof', 'payment_method', 'spent_at', 'description'];
    protected function casts(): array { return ['spent_at' => 'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cashSession(): BelongsTo { return $this->belongsTo(CashSession::class); }
}

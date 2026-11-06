<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditEntry extends Model
{
    use HasFactory;
    protected $fillable = ['credit_account_id', 'sale_id', 'user_id', 'type', 'amount_xof', 'balance_after_xof', 'reference', 'note'];
    public function account(): BelongsTo { return $this->belongsTo(CreditAccount::class, 'credit_account_id'); }
    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}

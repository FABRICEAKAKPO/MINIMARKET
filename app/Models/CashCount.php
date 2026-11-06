<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashCount extends Model
{
    use HasFactory;

    protected $fillable = ['cash_session_id', 'user_id', 'counted_amount_xof', 'difference_xof', 'note'];
    public function cashSession(): BelongsTo { return $this->belongsTo(CashSession::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}

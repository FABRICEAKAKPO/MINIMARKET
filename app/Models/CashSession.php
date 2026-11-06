<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'status', 'opening_amount_xof', 'expected_amount_xof', 'closing_amount_xof', 'difference_xof', 'opened_at', 'closed_at', 'closing_note'];
    protected function casts(): array { return ['opened_at' => 'datetime', 'closed_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }
    public function cashCounts(): HasMany { return $this->hasMany(CashCount::class); }
}

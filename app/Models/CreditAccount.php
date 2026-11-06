<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditAccount extends Model
{
    use HasFactory;
    protected $fillable = ['customer_id', 'balance_xof', 'status'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function entries(): HasMany { return $this->hasMany(CreditEntry::class); }
}

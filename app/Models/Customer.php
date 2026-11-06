<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email', 'address', 'credit_limit_xof', 'loyalty_points', 'consent_recorded', 'consent_recorded_at', 'is_active'];
    protected function casts(): array { return ['consent_recorded' => 'boolean', 'consent_recorded_at' => 'datetime', 'is_active' => 'boolean']; }
    public function creditAccount(): HasOne { return $this->hasOne(CreditAccount::class); }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }
}

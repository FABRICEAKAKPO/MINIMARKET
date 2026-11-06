<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'lot_number',
        'expires_at',
        'quantity',
        'unit_cost_xof',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'date'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

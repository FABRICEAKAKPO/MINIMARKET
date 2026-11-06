<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'unit_id',
        'name',
        'sku',
        'purchase_price_xof',
        'selling_price_xof',
        'tax_rate_basis_points',
        'is_tax_exempt',
        'is_weighted',
        'has_expiry',
        'low_stock_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_tax_exempt' => 'boolean',
            'is_weighted' => 'boolean',
            'has_expiry' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function stockLots(): HasMany
    {
        return $this->hasMany(StockLot::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStockQuantityAttribute(): int
    {
        return (int) ($this->relationLoaded('stockLots')
            ? $this->stockLots->sum('quantity')
            : $this->stockLots()->sum('quantity'));
    }
}

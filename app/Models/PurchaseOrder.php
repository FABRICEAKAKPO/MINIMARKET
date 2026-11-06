<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'user_id', 'number', 'status', 'ordered_at', 'expected_at', 'total_xof', 'notes'];

    protected function casts(): array
    {
        return ['ordered_at' => 'date', 'expected_at' => 'date'];
    }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function lines(): HasMany { return $this->hasMany(PurchaseOrderLine::class); }
    public function receipts(): HasMany { return $this->hasMany(GoodsReceipt::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptLine extends Model
{
    use HasFactory;

    protected $fillable = ['goods_receipt_id', 'purchase_order_line_id', 'stock_lot_id', 'quantity', 'unit_cost_xof'];
    public function receipt(): BelongsTo { return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id'); }
    public function orderLine(): BelongsTo { return $this->belongsTo(PurchaseOrderLine::class, 'purchase_order_line_id'); }
    public function stockLot(): BelongsTo { return $this->belongsTo(StockLot::class); }
}

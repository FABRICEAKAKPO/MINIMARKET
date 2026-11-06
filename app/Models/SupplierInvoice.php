<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierInvoice extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'purchase_order_id', 'invoice_number', 'issued_at', 'due_at', 'total_xof', 'paid_xof', 'status', 'notes'];
    protected function casts(): array { return ['issued_at' => 'date', 'due_at' => 'date']; }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function order(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id'); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_variant_id',
        'quantity',
        'reserved_quantity',
        'min_stock_alert',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'min_stock_alert' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'min_stock_alert');
    }
}

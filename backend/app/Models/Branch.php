<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'is_central_warehouse',
        'is_active',
    ];

    protected $casts = [
        'is_central_warehouse' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}

<?php

namespace SGCart\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class SellerPayout extends Model
{
    use HasUlids;

    protected $fillable = [
        'ulid',
        'seller_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'admin_notes',
        'payout_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payout_date' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName()
    {
        return 'ulid';
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function commissions()
    {
        return $this->hasMany(SellerCommission::class);
    }
}

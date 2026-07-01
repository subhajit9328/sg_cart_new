<?php

namespace SGCart\LogisticTracking\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCourier extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shipping_couriers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'url',
        'support_email',
    ];
}

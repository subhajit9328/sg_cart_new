<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $primaryKey = 'id';
    
    public $incrementing = false;
    
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'is_installed',
        'is_enabled',
        'config',
    ];

    protected $casts = [
        'is_installed' => 'boolean',
        'is_enabled' => 'boolean',
        'config' => 'array',
    ];
}

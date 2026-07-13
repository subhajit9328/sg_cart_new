<?php

namespace SGCart\SocialShare\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class SocialFollow extends Model
{
    protected $table = 'social_follows';

    protected $fillable = [
        'follower_id',
        'influencer_id',
    ];

    /**
     * Get the follower customer.
     */
    public function follower()
    {
        return $this->belongsTo(Customer::class, 'follower_id');
    }

    /**
     * Get the influencer customer.
     */
    public function influencer()
    {
        return $this->belongsTo(Customer::class, 'influencer_id');
    }
}

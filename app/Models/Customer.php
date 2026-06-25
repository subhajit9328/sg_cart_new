<?php

namespace App\Models;

use App\Mail\RegistrationSuccessMail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

#[Fillable(['name', 'email', 'password', 'phone_no'])]
#[Hidden(['password', 'remember_token'])]
class Customer extends Authenticatable
{
    protected static function booted(): void
    {
        static::created(function ($customer) {
            if ($customer->email) {
                try {
                    Mail::to($customer->email)
                        ->send(new RegistrationSuccessMail($customer));
                } catch (\Exception $e) {
                    Log::error('Failed to send registration success mail: '.$e->getMessage());
                }
            }
        });
    }

    use HasFactory, HasUlids, Notifiable;

    /**
     * Only auto-generate ULID for the `ulid` column.
     * The `id` column remains a standard auto-increment integer PK.
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    /**
     * The column used for route model binding (exposes ULID, not integer id).
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /**
     * Get the orders placed by the customer.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the saved addresses of the customer.
     */
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

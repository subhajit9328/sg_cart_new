<?php

namespace SGCart\CrmTickets\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use SGCart\CrmTickets\Enums\TicketPriority;

class Ticket extends Model
{
    protected $fillable = [
        'customer_id',
        'subject',
        'description',
        'priority',
        'ticketable_type',
        'ticketable_id',
        'status_id',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function ticketable()
    {
        return $this->morphTo();
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }
}

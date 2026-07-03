<?php

namespace SGCart\CrmTickets\Models;

use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    protected $fillable = [
        'ticket_id',
        'body',
        'commentable_type',
        'commentable_id',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * The commenter (Customer or User/Admin) — polymorphic.
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }
}

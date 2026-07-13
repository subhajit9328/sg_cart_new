<?php

namespace SGCart\CrmTickets\Observers;

use SGCart\CrmTickets\Models\TicketComment;

class TicketCommentObserver
{
    /**
     * Handle the TicketComment "created" event.
     */
    public function created(TicketComment $comment): void
    {
        if (class_exists(\App\Helpers\NotificationHelper::class)) {
            $ticket = $comment->ticket;
            if ($ticket) {
                $commenter = $comment->commentable;
                if ($commenter && $commenter->id == $ticket->customer_id && get_class($commenter) === \App\Models\Customer::class) {
                    // Customer commented -> Notify Admin
                    \App\Helpers\NotificationHelper::sendToAdmin(
                        'New Ticket Comment',
                        "Customer {$commenter->name} commented on Ticket #{$ticket->id}: " . \Illuminate\Support\Str::limit($comment->body, 50),
                        route('admin.tickets.show', $ticket->id)
                    );
                } else {
                    // Admin/Staff commented -> Notify Customer
                    \App\Helpers\NotificationHelper::sendToCustomer(
                        $ticket->customer_id,
                        'New Ticket Comment',
                        "Reply to your Ticket #{$ticket->id}: " . \Illuminate\Support\Str::limit($comment->body, 50),
                        route('store.tickets.show', $ticket->id)
                    );
                }
            }
        }
    }
}

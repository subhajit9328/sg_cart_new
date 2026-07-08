<?php

namespace SGCart\CrmTickets\Observers;

use SGCart\CrmTickets\Models\Ticket;

class TicketObserver
{
    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if ($ticket->isDirty('status_id')) {
            $newStatus = $ticket->status ? $ticket->status->name : 'Updated';

            if ($ticket->customer_id && class_exists(\App\Helpers\NotificationHelper::class)) {
                \App\Helpers\NotificationHelper::sendToCustomer(
                    $ticket->customer_id,
                    'Ticket Status Updated',
                    "Your support ticket #{$ticket->id} status has been changed to {$newStatus}.",
                    route('store.tickets.show', $ticket->id),
                    'info',
                    'fa-ticket'
                );
            }
        }
    }
}

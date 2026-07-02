<?php

namespace SGCart\CrmTickets\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use SGCart\CrmTickets\Http\Requests\StoreTicketRequest;
use SGCart\CrmTickets\Http\Requests\StoreTicketCommentRequest;
use SGCart\CrmTickets\Models\Ticket;
use SGCart\CrmTickets\Models\TicketAttachment;
use SGCart\CrmTickets\Models\TicketComment;

class TicketController extends Controller
{
    /**
     * Store a new ticket (customer-facing, backend only).
     */
    public function store(StoreTicketRequest $request)
    {
        $customer = auth('customer')->user();

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority ?? 'Medium',
            'ticketable_type' => $request->ticketable_type,
            'ticketable_id' => $request->ticketable_id,
            'status_id' => 1, // Open
        ]);

        // Handle image attachments on the ticket itself
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('ticket-attachments', 'public');
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'file_path' => $path,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Support ticket created successfully.');
    }

    /**
     * Show a single ticket (customer-facing, backend only).
     */
    public function show(Ticket $ticket)
    {
        $customer = auth('customer')->user();

        // Ensure customer can only view their own tickets
        if ($ticket->customer_id !== $customer->id) {
            abort(403);
        }

        $ticket->load(['status', 'comments.commentable', 'comments.attachments', 'attachments', 'ticketable']);

        return view('crm-tickets::store.show', compact('ticket'));
    }

    /**
     * Add a comment to a ticket (customer-facing).
     */
    public function addComment(StoreTicketCommentRequest $request, Ticket $ticket)
    {
        $customer = auth('customer')->user();

        // Ensure customer can only comment on their own tickets
        if ($ticket->customer_id !== $customer->id) {
            abort(403);
        }

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'body' => $request->body,
            'commentable_type' => get_class($customer),
            'commentable_id' => $customer->id,
        ]);

        // Handle image attachments on the comment
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('ticket-attachments', 'public');
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'ticket_comment_id' => $comment->id,
                    'file_path' => $path,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Comment added successfully.');
    }
}

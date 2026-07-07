<?php

namespace SGCart\CrmTickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SGCart\CrmTickets\Enums\TicketPriority;
use SGCart\CrmTickets\Enums\TicketStatus;
use SGCart\CrmTickets\Http\Requests\StoreTicketCommentRequest;
use SGCart\CrmTickets\Models\Ticket;
use SGCart\CrmTickets\Models\TicketAttachment;
use SGCart\CrmTickets\Models\TicketComment;
use SGCart\CrmTickets\Models\TicketStatus as TicketStatusModel;

class TicketController extends Controller
{
    /**
     * Display ticket listing with KPI stats.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['customer', 'status:id,name', 'ticketable']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->latest()->paginate(15);

        // KPI stats
        $totalTickets = Ticket::count();
        $openCount = Ticket::where('status_id', 1)->count();
        $reviewCount = Ticket::where('status_id', 2)->count();
        $resolvedCount = Ticket::where('status_id', 3)->count();
        $rejectedCount = Ticket::where('status_id', 4)->count();

        $statuses = TicketStatusModel::all();

        return view('crm-tickets::admin.index', compact(
            'tickets',
            'totalTickets',
            'openCount',
            'reviewCount',
            'resolvedCount',
            'rejectedCount',
            'statuses'
        ));
    }

    /**
     * Show a single ticket with comment thread.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load([
            'customer',
            'status',
            'ticketable',
            'attachments',
            'comments' => function ($q) {
                $q->oldest();
            },
            'comments.commentable',
            'comments.attachments',
        ]);

        $statuses = TicketStatusModel::all();

        return view('crm-tickets::admin.show', compact('ticket', 'statuses'));
    }

    /**
     * Admin adds a comment to a ticket.
     */
    public function addComment(StoreTicketCommentRequest $request, Ticket $ticket)
    {
        $admin = auth()->user();

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'body' => $request->body,
            'commentable_type' => get_class($admin),
            'commentable_id' => $admin->id,
        ]);

        // Handle image attachments
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

        return redirect()->back()->with('success', 'Reply added successfully.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status_id' => ['required', 'exists:ticket_statuses,id'],
        ]);

        $ticket->update(['status_id' => $request->status_id]);

        return redirect()->back()->with('success', 'Ticket status updated.');
    }

    /**
     * Update ticket priority.
     */
    public function updatePriority(Request $request, Ticket $ticket)
    {
        $request->validate([
            'priority' => ['required', Rule::enum(TicketPriority::class)],
        ]);

        $ticket->update(['priority' => $request->priority]);

        return redirect()->back()->with('success', 'Ticket priority updated.');
    }

    /**
     * Delete a single ticket.
     */
    public function destroy(Ticket $ticket)
    {
        // Delete all attached images from disk
        foreach ($ticket->attachments as $att) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($att->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($att->file_path);
            }
            $att->delete();
        }

        // Delete the ticket (comments will cascade delete)
        $ticket->delete();

        return redirect()->back()->with('success', 'Ticket has been deleted successfully.');
    }

    /**
     * Perform bulk actions on multiple tickets.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'bulk_ids' => 'required|array',
            'bulk_ids.*' => 'exists:tickets,id',
            'action' => 'required|in:delete',
        ]);

        $ids = $request->bulk_ids;
        $action = $request->action;

        if ($action === 'delete') {
            $tickets = Ticket::with('attachments')->whereIn('id', $ids)->get();
            foreach ($tickets as $ticket) {
                foreach ($ticket->attachments as $att) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($att->file_path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($att->file_path);
                    }
                    $att->delete();
                }
                $ticket->delete();
            }
            $message = 'Selected tickets have been deleted successfully.';
        }

        return redirect()->back()->with('success', $message);
    }
}

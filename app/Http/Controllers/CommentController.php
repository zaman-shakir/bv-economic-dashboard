<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CommentController extends Controller
{
    /**
     * Display all comments page with search and filters.
     */
    public function indexPage(Request $request): View
    {
        $search = $request->input('search', '');
        $userId = $request->input('user_id', '');

        $query = InvoiceComment::with(['user:id,name', 'invoice'])
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($search) {
            $query->where('comment', 'LIKE', "%{$search}%");
        }

        // User filter
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $comments = $query->paginate(50);

        // Get all users who have commented for filter dropdown
        $users = \App\Models\User::whereHas('comments')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('comments.index', compact('comments', 'users', 'search', 'userId'));
    }

    /**
     * Get all comments for a specific invoice (API).
     */
    public function index($invoiceId): JsonResponse
    {
        $comments = InvoiceComment::where('invoice_id', $invoiceId)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'comment', 'user_id', 'created_at']);

        return response()->json($comments);
    }

    /**
     * Store a new comment for an invoice.
     */
    public function store(Request $request, $invoiceId): JsonResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // Verify invoice exists
        $invoice = Invoice::findOrFail($invoiceId);

        $comment = InvoiceComment::create([
            'invoice_id' => $invoiceId,
            'user_id' => auth()->id(),
            'comment' => $validated['comment']
        ]);

        $comment->load('user:id,name');

        return response()->json($comment, 201);
    }

    /**
     * Get comment counts for multiple invoices (efficient batch loading).
     */
    public function counts(Request $request): JsonResponse
    {
        $invoiceIds = $request->input('invoice_ids', []);

        if (empty($invoiceIds)) {
            return response()->json([]);
        }

        $counts = InvoiceComment::whereIn('invoice_id', $invoiceIds)
            ->selectRaw('invoice_id, COUNT(*) as count')
            ->groupBy('invoice_id')
            ->pluck('count', 'invoice_id');

        return response()->json($counts);
    }
}

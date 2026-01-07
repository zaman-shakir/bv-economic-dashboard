<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    /**
     * Get all comments for a specific invoice.
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

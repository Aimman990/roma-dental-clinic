<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Adjustment;
use Illuminate\Http\Request;

class AdjustmentsController extends Controller
{
    public function __construct()
    {
        // financial modifications are admin-only
        $this->middleware(function($request, $next){
            if (! auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Only administrators may manage adjustments');
            }
            return $next($request);
        });
    }

    public function index(Invoice $invoice)
    {
        return response()->json($invoice->adjustments()->with('user')->orderBy('created_at','desc')->get());
    }

    public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string',
            'reason' => 'nullable|string'
        ]);

        $adj = Adjustment::create([
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id() ?? null,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'reference' => $data['reference'] ?? null,
            'reason' => $data['reason'] ?? null,
        ]);

        // recalculate invoice remaining based on payments and adjustments
        $paid = $invoice->payments()->sum('amount');
        $credits = $invoice->adjustments()->where('type','credit')->sum('amount');
        $debits = $invoice->adjustments()->where('type','debit')->sum('amount');

        $effectiveTotal = $invoice->total - $credits + $debits;
        $remaining = max(0, $effectiveTotal - $paid);
        $invoice->update(['remaining' => $remaining]);

        return response()->json($adj, 201);
    }
}

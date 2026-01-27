<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    public function __construct()
    {
        // expenses are financial — admin only
        $this->middleware(function($request, $next){
            if (! auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Only administrators may access expenses');
            }
            return $next($request);
        });
    }
    public function index(Request $request)
    {
        $q = $request->query('q');
        $expenses = Expense::when($q, function($query, $q){
            $query->where('title', 'like', "%$q%")
                ->orWhere('category', 'like', "%$q%");
        })->orderBy('incurred_on','desc')->paginate(25);

        return response()->json($expenses);
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();
        $data['recorded_by'] = $data['recorded_by'] ?? auth()->id() ?? null;
        $expense = Expense::create($data);
        return response()->json($expense, 201);
    }

    public function show(Expense $expense)
    {
        return response()->json($expense);
    }

    public function update(StoreExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());
        return response()->json($expense);
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(['message' => 'deleted']);
    }
}

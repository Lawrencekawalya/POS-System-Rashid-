<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    protected $categories = ['Rent', 'Utilities', 'Transport', 'Salaries', 'Supplies', 'Marketing', 'Repair', 'Others'];
    protected $methods = ['Cash', 'Mobile Money', 'Bank Transfer', 'Cheque'];

    public function index(Request $request)
    {
        $query = Expense::with('user');

        // Professional Filter: Filter by month if requested, otherwise show all this month
        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereMonth('expense_date', $date->month)->whereYear('expense_date', $date->year);
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(50);

        // Stats for the header
        $totalThisMonth = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'totalThisMonth' => $totalThisMonth,
            'categories' => $this->categories,
            'methods' => $this->methods
        ]);
    }

    public function create()
    {
        return view('expenses.create', [
            'categories' => $this->categories,
            'methods' => $this->methods
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'expense_date' => 'required|date',
            'reference_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string'
        ]);

        $request->user()->expenses()->create($data);

        return redirect()->back()->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        // Professional safety: Prevent editing if already confirmed
        if ($expense->status !== 'pending') {
            return redirect()->route('expenses.index')->with('error', 'Confirmed expenses cannot be edited.');
        }

        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => $this->categories,
            'methods' => $this->methods
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->status !== 'pending') {
            return redirect()->route('expenses.index')->with('error', 'Confirmed expenses are locked.');
        }

        $data = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->back()->with('success', 'Expense record removed.');
    }
}
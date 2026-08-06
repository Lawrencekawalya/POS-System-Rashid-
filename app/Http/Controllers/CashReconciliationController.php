<?php

namespace App\Http\Controllers;

use App\Models\CashReconciliation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Expense;
use App\Models\Sale;

class CashReconciliationController extends Controller
{
    public function index()
    {
        $reconciliations = CashReconciliation::with('user')
            ->orderByDesc('business_date')
            // ->get();
            ->paginate(20);

        return view('cash-reconciliation.index', compact('reconciliations'));
    }

    public function create()
    {
        $date = now()->toDateString();

        // 1. CUMULATIVE REVENUE
        $totalSales = Sale::sum('total_amount');

        $totalConfirmedExpenses = Expense::where('status', 'confirmed')
            ->where('payment_method', 'Cash')
            ->sum('amount');

        $openingBalance = $totalSales - $totalConfirmedExpenses;

        // 2. Pending Expenses (to justify now)
        $pendingExpenses = Expense::where('status', 'pending')
            ->where('payment_method', 'Cash')
            ->get();

        // 3. New Cash Sales (Display only – not used in math)
        $newSales = 0;

        // Initial Expected Cash (before selecting new expenses)
        $cashExpected = $openingBalance;

        return view('cash-reconciliation.create', compact(
            'date',
            'openingBalance',
            'newSales',
            'cashExpected',
            'pendingExpenses'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_date' => 'required|date',
            'cash_counted' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'expense_ids' => 'nullable|array',
        ]);

        $userId = Auth::id();

        // 1️ Recalculate cumulative revenue safely
        $totalSales = Sale::sum('total_amount');

        $totalConfirmedExpenses = Expense::where('status', 'confirmed')
            ->where('payment_method', 'Cash')
            ->sum('amount');

        $maxExpected = $totalSales - $totalConfirmedExpenses;

        // 2️ Get selected pending expenses safely
        $selectedExpenseIds = $request->expense_ids ?? [];

        $selectedExpenseTotal = Expense::whereIn('id', $selectedExpenseIds)
            ->where('status', 'pending')
            ->where('payment_method', 'Cash')
            ->sum('amount');

        // 3️ Compute real expected cash (DO NOT trust frontend)
        $realExpected = $maxExpected - $selectedExpenseTotal;

        // $realExpected = $maxExpected - $selectedExpenseTotal;

        if ($realExpected < 0) {
            return back()->withErrors([
                'reconciliation' => 'Selected expenses exceed available cumulative revenue.'
            ]);
        }

        // 4️ Compute difference
        $cashCounted = $request->cash_counted ?? 0;
        $difference = $cashCounted - $realExpected;

        // Create reconciliation using SERVER-CALCULATED value
        $reconciliation = CashReconciliation::create([
            'business_date' => $request->business_date,
            'user_id' => $userId,
            'cash_expected' => $realExpected, // use realExpected
            'cash_counted' => $cashCounted,
            'difference' => $difference,
            'status' => $difference == 0
                ? 'balanced'
                : ($difference > 0 ? 'over' : 'short'),
            'notes' => $request->notes,
        ]);

        // Confirm selected expenses only
        if (!empty($selectedExpenseIds)) {
            Expense::whereIn('id', $selectedExpenseIds)
                ->where('status', 'pending')
                ->update([
                    'status' => 'confirmed',
                    'cash_reconciliation_id' => $reconciliation->id
                ]);
        }

        return redirect()
            ->route('cash.reconcile.index')
            ->with('success', 'Cash reconciliation recorded and expenses verified.');
    }

    public function edit(CashReconciliation $reconciliation)
    {
        $user = auth()->user();

        // Admin can edit anything
        if ($user->isAdmin()) {
            return view('cash-reconciliation.edit', compact('reconciliation'));
        }

        // Cashier can edit only if pending
        if ($user->isCashier() && $reconciliation->status === 'pending') {
            return view('cash-reconciliation.edit', compact('reconciliation'));
        }

        abort(403, 'You are not allowed to edit this reconciliation.');
    }

    public function update(Request $request, CashReconciliation $reconciliation)
    {
        $user = auth()->user();

        // Permission check
        if (
            !$user->isAdmin() &&
            !($user->isCashier() && $reconciliation->status === 'pending')
        ) {
            abort(403);
        }

        $request->validate([
            'cash_expected' => 'required|numeric',
            'cash_counted' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $cashExpected = $request->cash_expected;
        $cashCounted = $request->cash_counted ?? $cashExpected;
        $difference = $cashCounted - $cashExpected;

        $reconciliation->update([
            'cash_expected' => $cashExpected,
            'cash_counted' => $cashCounted,
            'difference' => $difference,
            'status' => $difference == 0
                ? 'balanced'
                : ($difference > 0 ? 'over' : 'short'),
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('cash.reconcile.index')
            ->with('success', 'Reconciliation updated successfully.');
    }

    public function cancel(CashReconciliation $reconciliation)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        if ($reconciliation->status === 'cancelled') {
            return back()->withErrors([
                'reconciliation' => 'This reconciliation is already cancelled.'
            ]);
        }

        // Reverse linked expenses
        Expense::where('cash_reconciliation_id', $reconciliation->id)
            ->update([
                'status' => 'pending',
                'cash_reconciliation_id' => null
            ]);

        // Mark reconciliation as cancelled
        $reconciliation->update([
            'status' => 'cancelled'
        ]);

        return redirect()
            ->route('cash.reconcile.index')
            ->with('success', 'Reconciliation cancelled successfully.');
    }

}


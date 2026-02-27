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
    // public function create()
    // {
    //     $date = now()->toDateString();
    //     // $userId = auth()->user()?->id;
    //     $userId = Auth::id();

    //     // reuse your Z-report logic here
    //     $cashExpected = app(\App\Services\ZReportService::class)
    //         ->cashExpectedForDate($date, $userId);

    //     return view('cash-reconciliation.create', compact(
    //         'date',
    //         'cashExpected'
    //     ));
    // }
    public function create()
    {
        $date = now()->toDateString();

        // 1. Get the last reconciliation to find the "Remaining Balance"
        $lastReconciliation = CashReconciliation::orderByDesc('id')->first();
        $openingBalance = $lastReconciliation ? $lastReconciliation->cash_counted : 0;
        $lastReconciledAt = $lastReconciliation ? $lastReconciliation->created_at : now()->subYear();

        // 2. Get Sales since that last reconciliation
        $newSales = Sale::where('created_at', '>', $lastReconciledAt)
            // ->where('payment_method', 'Cash')
            ->sum('total_amount');

        // 3. Get Pending Expenses
        $pendingExpenses = Expense::where('status', 'pending')
            ->where('payment_method', 'Cash')
            ->get();

        // Initial Cash Expected (before expenses are checked)
        $cashExpected = $openingBalance + $newSales;

        return view('cash-reconciliation.create', compact(
            'date',
            'openingBalance',
            'newSales',
            'cashExpected',
            'pendingExpenses'
        ));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'business_date' => 'required|date',
    //         'cash_expected' => 'required|numeric',
    //         'cash_counted' => 'required|numeric|min:0',
    //         'notes' => 'nullable|string',
    //     ]);

    //     $difference = $request->cash_counted - $request->cash_expected;

    //     CashReconciliation::create([
    //         'business_date' => $request->business_date,
    //         'user_id' => Auth::id(),
    //         'cash_expected' => $request->cash_expected,
    //         'cash_counted' => $request->cash_counted,
    //         'difference' => $difference,
    //         'status' => $difference == 0
    //             ? 'balanced'
    //             : ($difference > 0 ? 'over' : 'short'),
    //         'notes' => $request->notes,
    //     ]);

    //     return redirect()->route('cash.reconcile.index')
    //         ->with('success', 'Cash reconciliation recorded');
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'business_date' => 'required|date',
    //         'cash_expected' => 'required|numeric',
    //         'cash_counted' => 'required|numeric|min:0',
    //         'notes' => 'nullable|string',
    //     ]);

    //     $userId = Auth::id();

    //     // 🔒 Prevent duplicate reconciliation
    //     $alreadyReconciled = CashReconciliation::where('business_date', $request->business_date)
    //         ->where('user_id', $userId)
    //         ->exists();

    //     if ($alreadyReconciled) {
    //         return back()->withErrors([
    //             'reconciliation' => 'Cash has already been reconciled for today.',
    //         ]);
    //     }

    //     $difference = $request->cash_counted - $request->cash_expected;

    //     CashReconciliation::create([
    //         'business_date' => $request->business_date,
    //         'user_id' => $userId,
    //         'cash_expected' => $request->cash_expected,
    //         'cash_counted' => $request->cash_counted,
    //         'difference' => $difference,
    //         'status' => $difference == 0
    //             ? 'balanced'
    //             : ($difference > 0 ? 'over' : 'short'),
    //         'notes' => $request->notes,
    //     ]);

    //     return redirect()
    //         ->route('cash.reconcile.index')
    //         ->with('success', 'Cash reconciliation recorded successfully.');
    // }

    public function store(Request $request)
    {
        $request->validate([
            'business_date' => 'required|date',
            'cash_expected' => 'required|numeric',
            'cash_counted' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'expense_ids' => 'nullable|array', // Added to catch the checkboxes
        ]);

        $userId = Auth::id();

        // 🔒 Prevent duplicate reconciliation
        // $alreadyReconciled = CashReconciliation::where('business_date', $request->business_date)
        //     ->where('user_id', $userId)
        //     ->exists();

        // if ($alreadyReconciled) {
        //     return back()->withErrors(['reconciliation' => 'Cash has already been reconciled for today.']);
        // }
        // 1. Check if there are any pending expenses or new sales to reconcile
        $hasPendingExpenses = Expense::where('status', 'pending')->exists();

        // Optional: Check if sales have happened since the last reconciliation
        $lastReconciliation = CashReconciliation::latest()->first();
        $lastTime = $lastReconciliation ? $lastReconciliation->created_at : now()->startOfDay();
        $hasNewSales = Sale::where('created_at', '>', $lastTime)->exists();

        if (!$hasPendingExpenses && !$hasNewSales) {
            return back()->withErrors([
                'reconciliation' => 'There are no new sales or pending expenses to reconcile at this time.'
            ]);
        }

        $difference = $request->cash_counted - $request->cash_expected;

        // 1. Create the Reconciliation Record
        $reconciliation = CashReconciliation::create([
            'business_date' => $request->business_date,
            'user_id' => $userId,
            'cash_expected' => $request->cash_expected,
            'cash_counted' => $request->cash_counted,
            'difference' => $difference,
            'status' => $difference == 0 ? 'balanced' : ($difference > 0 ? 'over' : 'short'),
            'notes' => $request->notes,
        ]);

        // 2. THE FIX: Update only the CHECKED expenses to 'confirmed'
        if ($request->has('expense_ids')) {
            Expense::whereIn('id', $request->expense_ids)
                ->update([
                    'status' => 'confirmed',
                    'cash_reconciliation_id' => $reconciliation->id // Link them to this report
                ]);
        }

        // 3. Optional: Mark other pending cash expenses as 'rejected' if they weren't justified?
        // Or leave them for the next shift if they were just forgotten.

        return redirect()
            ->route('cash.reconcile.index')
            ->with('success', 'Cash reconciliation recorded and expenses verified.');
    }

}


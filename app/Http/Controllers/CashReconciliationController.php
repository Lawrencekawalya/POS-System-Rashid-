<?php

namespace App\Http\Controllers;

use App\Models\CashReconciliation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashReconciliationController extends Controller
{
    public function index()
    {
        $reconciliations = CashReconciliation::with('user')
            ->orderByDesc('business_date')
            ->get();

        return view('cash-reconciliation.index', compact('reconciliations'));
    }
    public function create()
    {
        $date = now()->toDateString();
        // $userId = auth()->user()?->id;
        $userId = Auth::id();

        // reuse your Z-report logic here
        $cashExpected = app(\App\Services\ZReportService::class)
            ->cashExpectedForDate($date, $userId);

        return view('cash-reconciliation.create', compact(
            'date',
            'cashExpected'
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

    public function store(Request $request)
    {
        $request->validate([
            'business_date' => 'required|date',
            'cash_expected' => 'required|numeric',
            'cash_counted' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $userId = Auth::id();

        // 🔒 Prevent duplicate reconciliation
        $alreadyReconciled = CashReconciliation::where('business_date', $request->business_date)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyReconciled) {
            return back()->withErrors([
                'reconciliation' => 'Cash has already been reconciled for today.',
            ]);
        }

        $difference = $request->cash_counted - $request->cash_expected;

        CashReconciliation::create([
            'business_date' => $request->business_date,
            'user_id' => $userId,
            'cash_expected' => $request->cash_expected,
            'cash_counted' => $request->cash_counted,
            'difference' => $difference,
            'status' => $difference == 0
                ? 'balanced'
                : ($difference > 0 ? 'over' : 'short'),
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('cash.reconcile.index')
            ->with('success', 'Cash reconciliation recorded successfully.');
    }

}


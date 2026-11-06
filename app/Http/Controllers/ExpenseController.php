<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        return view('treasury.expenses', ['expenses' => Expense::with('user')->latest('spent_at')->paginate(20)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'max:80'],
            'amount_xof' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'in:cash,bank,tmoney,flooz,card'],
            'spent_at' => ['required', 'date'],
            'description' => ['required', 'string', 'max:500'],
        ]);
        Expense::create([...$data, 'user_id' => $request->user()->id]);
        return back()->with('status', 'Dépense enregistrée.');
    }
}

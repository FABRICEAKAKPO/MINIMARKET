<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        return view('customers.index', [
            'customers' => Customer::with('creditAccount')->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'credit_limit_xof' => ['required', 'integer', 'min:0'],
            'consent_recorded' => ['sometimes', 'boolean'],
        ]);
        $data['consent_recorded_at'] = $request->boolean('consent_recorded') ? now() : null;
        $customer = Customer::create($data);
        $customer->creditAccount()->create();
        return back()->with('status', 'Client enregistré.');
    }

    public function payment(Request $request, Customer $customer, CreditService $creditService): RedirectResponse
    {
        $data = $request->validate([
            'amount_xof' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $creditService->payment($customer, $data['amount_xof'], (int) $request->user()->id, $data['reference'] ?? null, $data['note'] ?? null);
        return back()->with('status', 'Paiement crédit enregistré.');
    }

    public function statement(Customer $customer): View
    {
        $customer->load([
            'creditAccount.entries' => fn ($query) => $query->with('sale')->latest(),
            'sales' => fn ($query) => $query->latest()->limit(50),
        ]);

        return view('customers.statement', compact('customer'));
    }
}

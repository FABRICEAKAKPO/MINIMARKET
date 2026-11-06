<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashController extends Controller
{
    public function index(Request $request): View
    {
        return view('cash.index', ['session' => CashSession::where('user_id', $request->user()->id)->where('status', 'open')->latest()->first()]);
    }

    public function open(Request $request): RedirectResponse
    {
        $data = $request->validate(['opening_amount_xof' => ['required', 'integer', 'min:0']]);
        if (CashSession::where('user_id', $request->user()->id)->where('status', 'open')->exists()) {
            return back()->withErrors(['cash' => 'Vous avez déjà une caisse ouverte.']);
        }
        CashSession::create(['user_id' => $request->user()->id, 'opening_amount_xof' => $data['opening_amount_xof'], 'expected_amount_xof' => $data['opening_amount_xof'], 'opened_at' => now(), 'status' => 'open']);
        return redirect()->route('pos.index')->with('status', 'Caisse ouverte.');
    }

    public function close(Request $request, CashSession $cashSession): RedirectResponse
    {
        abort_unless($cashSession->user_id === $request->user()->id && $cashSession->status === 'open', 403);
        $data = $request->validate(['closing_amount_xof' => ['required', 'integer', 'min:0'], 'closing_note' => ['nullable', 'string', 'max:500']]);
        $cashSession->update(['status' => 'closed', 'closing_amount_xof' => $data['closing_amount_xof'], 'difference_xof' => $data['closing_amount_xof'] - $cashSession->expected_amount_xof, 'closed_at' => now(), 'closing_note' => $data['closing_note'] ?? null]);
        $cashSession->cashCounts()->create(['user_id' => $request->user()->id, 'counted_amount_xof' => $data['closing_amount_xof'], 'difference_xof' => $cashSession->difference_xof, 'note' => $data['closing_note'] ?? null]);
        activity('cash')->causedBy($request->user())->performedOn($cashSession)->log('Caisse clôturée');
        return redirect()->route('cash.index')->with('status', 'Caisse clôturée.');
    }
}

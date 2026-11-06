<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Relevé - {{ $customer->name }}</title><style>body{font-family:Arial,sans-serif;max-width:900px;margin:2rem auto;color:#222}.no-print{margin-bottom:2rem}table{width:100%;border-collapse:collapse}th,td{border-bottom:1px solid #ddd;padding:.6rem;text-align:left}@media print{.no-print{display:none}}</style></head>
<body>
    <div class="no-print"><button onclick="window.print()">Imprimer</button> <a href="{{ route('customers.index') }}">Retour</a></div>
    <h1>Relevé client</h1><p><strong>{{ $customer->name }}</strong><br>{{ $customer->phone }}<br>Édité le {{ now()->format('d/m/Y H:i') }}</p>
    <p>Solde dû : <strong>{{ number_format($customer->creditAccount?->balance_xof ?? 0, 0, ',', ' ') }} FCFA</strong> — Points fidélité : <strong>{{ $customer->loyalty_points }}</strong></p>
    <table><thead><tr><th>Date</th><th>Type</th><th>Référence</th><th>Montant</th><th>Solde après</th></tr></thead><tbody>
    @foreach ($customer->creditAccount?->entries ?? [] as $entry)<tr><td>{{ $entry->created_at->format('d/m/Y H:i') }}</td><td>{{ $entry->type === 'charge' ? 'Crédit' : 'Paiement' }}</td><td>{{ $entry->sale?->number ?? $entry->reference ?? '—' }}</td><td>{{ number_format($entry->amount_xof, 0, ',', ' ') }} FCFA</td><td>{{ number_format($entry->balance_after_xof, 0, ',', ' ') }} FCFA</td></tr>@endforeach
    </tbody></table>
</body>
</html>

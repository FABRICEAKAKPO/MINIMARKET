<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Ticket {{ $sale->number }}</title><style>body{font-family:monospace;width:80mm;margin:0 auto;font-size:12px}.row{display:flex;justify-content:space-between}hr{border:0;border-top:1px dashed #000}@media print{.no-print{display:none}}</style></head>
<body>
    <div class="no-print"><button onclick="window.print()">Imprimer</button> <a href="{{ route('pos.index') }}">← Retour caisse</a></div>
    <h2 style="text-align:center">MINIMARKET TOGO</h2><div style="text-align:center">Ticket de caisse</div><hr>
    <div>N° {{ $sale->number }}</div><div>Date : {{ $sale->created_at->format('d/m/Y H:i') }}</div><div>Caissier : {{ $sale->user->name }}</div><hr>
    @foreach ($sale->lines as $line)<div>{{ $line->product->name }}</div><div class="row"><span>{{ $line->quantity }} x {{ number_format($line->unit_price_xof, 0, ',', ' ') }}</span><span>{{ number_format($line->line_total_xof, 0, ',', ' ') }}</span></div>@endforeach
    <hr><div class="row"><strong>Total</strong><strong>{{ number_format($sale->total_xof, 0, ',', ' ') }} FCFA</strong></div><div>TVA : {{ number_format($sale->tax_xof, 0, ',', ' ') }} FCFA</div><hr>
    @foreach ($sale->payments as $payment)<div class="row"><span>{{ $payment->method }}</span><span>{{ number_format($payment->amount_xof, 0, ',', ' ') }}</span></div>@endforeach
    <p style="text-align:center">Merci pour votre visite</p>
</body>
</html>

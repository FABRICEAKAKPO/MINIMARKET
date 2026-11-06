<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchasingController extends Controller
{
    public function index(): View
    {
        return view('purchasing.index', [
            'orders' => PurchaseOrder::with('supplier', 'lines.product')->latest()->paginate(15),
            'suppliers' => Supplier::withCount('invoices')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function supplierStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:80'],
        ]);
        Supplier::create($data);

        return back()->with('status', 'Fournisseur enregistré.');
    }

    public function orderCreate(): View
    {
        return view('purchasing.create', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function orderStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'ordered_quantity' => ['required', 'integer', 'min:1'],
            'unit_cost_xof' => ['required', 'integer', 'min:0'],
            'expected_at' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = DB::transaction(function () use ($data, $request): PurchaseOrder {
            $order = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $request->user()->id,
                'number' => 'BC-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'status' => 'sent',
                'ordered_at' => today(),
                'expected_at' => $data['expected_at'] ?? null,
                'total_xof' => $data['ordered_quantity'] * $data['unit_cost_xof'],
                'notes' => $data['notes'] ?? null,
            ]);
            $order->lines()->create([
                'product_id' => $data['product_id'],
                'ordered_quantity' => $data['ordered_quantity'],
                'unit_cost_xof' => $data['unit_cost_xof'],
                'line_total_xof' => $data['ordered_quantity'] * $data['unit_cost_xof'],
            ]);

            return $order;
        });

        return redirect()->route('purchasing.index')->with('status', "Bon de commande {$order->number} créé.");
    }

    public function receiveForm(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load('supplier', 'lines.product');

        return view('purchasing.receive', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder, StockService $stockService): RedirectResponse
    {
        $data = $request->validate([
            'purchase_order_line_id' => ['required', 'exists:purchase_order_lines,id'],
            'lot_number' => ['required', 'string', 'max:80'],
            'quantity' => ['required', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $line = $purchaseOrder->lines()->with('product')->findOrFail($data['purchase_order_line_id']);
        $remaining = $line->ordered_quantity - $line->received_quantity;
        if ($data['quantity'] > $remaining) {
            return back()->withErrors(['quantity' => "Réception maximale restante : {$remaining}."])->withInput();
        }

        DB::transaction(function () use ($data, $purchaseOrder, $line, $stockService, $request): void {
            $lot = $stockService->receive(
                $line->product,
                $data['lot_number'],
                $data['quantity'],
                $line->unit_cost_xof,
                $data['expires_at'] ?? null,
                (int) $request->user()->id,
                "Réception {$purchaseOrder->number}",
            );
            $receipt = GoodsReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'user_id' => $request->user()->id,
                'number' => 'BR-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'received_at' => today(),
                'notes' => $data['notes'] ?? null,
            ]);
            $receipt->lines()->create([
                'purchase_order_line_id' => $line->id,
                'stock_lot_id' => $lot->id,
                'quantity' => $data['quantity'],
                'unit_cost_xof' => $line->unit_cost_xof,
            ]);
            $line->increment('received_quantity', $data['quantity']);
            $purchaseOrder->update([
                'status' => $purchaseOrder->lines()->whereColumn('received_quantity', '<', 'ordered_quantity')->exists() ? 'partially_received' : 'received',
            ]);
        });

        return redirect()->route('purchasing.index')->with('status', 'Réception partielle enregistrée et stock augmenté.');
    }

    public function orderDocument(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'user', 'lines.product', 'receipts']);

        return view('purchasing.order-document', compact('purchaseOrder'));
    }

    public function orderCsv(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['supplier', 'user', 'lines.product']);

        return response()->streamDownload(function () use ($purchaseOrder): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Bon de commande '.$purchaseOrder->number, 'Fournisseur', $purchaseOrder->supplier->name], ';');
            fputcsv($handle, ['Généré le', now()->format('d/m/Y H:i'), 'Par', auth()->user()->name], ';');
            fputcsv($handle, ['Produit', 'Quantité commandée', 'Quantité reçue', 'Coût unitaire FCFA', 'Total FCFA'], ';');
            foreach ($purchaseOrder->lines as $line) {
                fputcsv($handle, [$line->product->name, $line->ordered_quantity, $line->received_quantity, $line->unit_cost_xof, $line->line_total_xof], ';');
            }
            fclose($handle);
        }, 'commande-'.$purchaseOrder->number.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function supplierDocument(Supplier $supplier): View
    {
        $supplier->load(['purchaseOrders' => fn ($query) => $query->with('lines.product')->latest()]);

        return view('purchasing.supplier-document', compact('supplier'));
    }

    public function supplierCsv(Supplier $supplier): Response
    {
        $supplier->load(['purchaseOrders' => fn ($query) => $query->with('lines.product')->latest()]);

        return response()->streamDownload(function () use ($supplier): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Produits commandés auprès de '.$supplier->name], ';');
            fputcsv($handle, ['Commande', 'Date', 'Produit', 'Quantité', 'Coût unitaire FCFA', 'Total FCFA'], ';');
            foreach ($supplier->purchaseOrders as $order) {
                foreach ($order->lines as $line) {
                    fputcsv($handle, [$order->number, $order->ordered_at->format('d/m/Y'), $line->product->name, $line->ordered_quantity, $line->unit_cost_xof, $line->line_total_xof], ';');
                }
            }
            fclose($handle);
        }, 'fournisseur-'.str($supplier->name)->slug().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

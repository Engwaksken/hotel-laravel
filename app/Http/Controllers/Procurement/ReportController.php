<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\SupplierInvoice;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $propertyId = $request->user()->property_id;
        $filters = $request->validate([
            'period' => ['nullable', 'in:7,30,90,custom'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $period = $filters['period'] ?? '30';
        $to = $request->date('to')?->toDateString() ?? today()->toDateString();
        $from = $period === 'custom'
            ? ($request->date('from')?->toDateString() ?? today()->subDays(29)->toDateString())
            : today()->subDays((int) $period - 1)->toDateString();

        $lowStock = InventoryItem::where('property_id', $propertyId)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('quantity_on_hand')->limit(50)->get();
        $expiring = InventoryBatch::where('property_id', $propertyId)
            ->whereNotNull('expires_at')->whereBetween('expires_at', [today(), today()->addDays(60)])
            ->orderBy('expires_at')->limit(50)->get();
        $movementsQuery = StockMovement::where('property_id', $propertyId)
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ;
        $movements = (clone $movementsQuery)->with('inventoryItem')->latest()->limit(100)->get();
        $payables = SupplierInvoice::with('supplier')->where('property_id', $propertyId)
            ->whereIn('status', ['unpaid', 'part_paid'])->orderBy('due_date')->limit(50)->get();

        return view('procurement.reports.index', [
            'lowStock' => $lowStock,
            'expiring' => $expiring,
            'movements' => $movements,
            'payables' => $payables,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'stats' => [
                'lowStock' => InventoryItem::where('property_id', $propertyId)->whereColumn('quantity_on_hand', '<=', 'reorder_level')->count(),
                'expiring' => InventoryBatch::where('property_id', $propertyId)->whereNotNull('expires_at')->whereBetween('expires_at', [today(), today()->addDays(60)])->count(),
                'payables' => (float) SupplierInvoice::where('property_id', $propertyId)->whereIn('status', ['unpaid', 'part_paid'])->sum('total_amount') - (float) SupplierInvoice::where('property_id', $propertyId)->whereIn('status', ['unpaid', 'part_paid'])->sum('paid_amount'),
                'movements' => (clone $movementsQuery)->count(),
            ],
        ]);
    }
}

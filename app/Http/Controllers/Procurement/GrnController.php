<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Models\Store;
use App\Services\Procurement\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GrnController extends Controller
{
    public function index()
    {
        $propertyId = (int) auth()->user()->property_id;

        return view('procurement.grns.index', [
            'rows' => GoodsReceivedNote::with(['order.supplier', 'store', 'items.inventoryItem'])
                ->where('property_id', $propertyId)
                ->latest()
                ->paginate(30),
            'orders' => PurchaseOrder::with('items.inventoryItem')
                ->where('property_id', $propertyId)
                ->whereIn('status', ['approved', 'sent', 'part_received'])
                ->get(),
            'stores' => Store::where('property_id', $propertyId)->where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $propertyId = (int) $request->user()->property_id;
        $data = $request->validate([
            'purchase_order_id' => [
                'required', 'integer',
                Rule::exists('purchase_orders', 'id')->where(fn ($query) => $query
                    ->where('property_id', $propertyId)
                    ->whereIn('status', ['approved', 'sent', 'part_received'])),
            ],
            'store_id' => [
                'required', 'integer',
                Rule::exists('stores', 'id')->where(fn ($query) => $query
                    ->where('property_id', $propertyId)
                    ->where('is_active', true)),
            ],
            'delivery_note_number' => ['nullable', 'string', 'max:100'],
            'purchase_order_item_id' => ['required', 'array', 'min:1'],
            'purchase_order_item_id.*' => ['required', 'integer', 'distinct'],
            'quantity_received' => ['required', 'array'],
            'quantity_received.*' => ['required', 'numeric', 'min:0'],
            'quantity_rejected' => ['nullable', 'array'],
            'quantity_rejected.*' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'array'],
            'unit_cost.*' => ['required', 'numeric', 'min:0'],
            'batch_number' => ['nullable', 'array'],
            'expires_at' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($data, $propertyId, $request): void {
            $order = PurchaseOrder::where('property_id', $propertyId)
                ->whereIn('status', ['approved', 'sent', 'part_received'])
                ->lockForUpdate()
                ->findOrFail($data['purchase_order_id']);

            $grn = GoodsReceivedNote::create([
                'property_id' => $propertyId,
                'purchase_order_id' => $order->id,
                'store_id' => $data['store_id'],
                'number' => 'GRN-'.now()->format('Ymd-His').'-'.random_int(10, 99),
                'received_date' => today(),
                'delivery_note_number' => $data['delivery_note_number'] ?? null,
                'received_by' => $request->user()->id,
            ]);

            foreach ($data['purchase_order_item_id'] as $index => $purchaseOrderItemId) {
                $line = $order->items()->findOrFail($purchaseOrderItemId);
                $grn->items()->create([
                    'purchase_order_item_id' => $line->id,
                    'inventory_item_id' => $line->inventory_item_id,
                    'quantity_received' => $data['quantity_received'][$index],
                    'quantity_rejected' => $data['quantity_rejected'][$index] ?? 0,
                    'unit_cost' => $data['unit_cost'][$index],
                    'batch_number' => $data['batch_number'][$index] ?? null,
                    'expires_at' => $data['expires_at'][$index] ?? null,
                ]);
            }
        });

        return back()->with('success', 'GRN created. Post it to update stock.');
    }

    public function post(GoodsReceivedNote $grn, ProcurementService $service)
    {
        abort_unless((int) $grn->property_id === (int) auth()->user()->property_id, 404);
        $service->postGrn($grn, (int) auth()->id());

        return back()->with('success', 'GRN posted and inventory updated.');
    }
}

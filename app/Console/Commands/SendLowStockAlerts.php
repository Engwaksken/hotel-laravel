<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use Illuminate\Console\Command;

class SendLowStockAlerts extends Command
{
    use ConcernsWithOperationalNotifications;

    protected $signature = 'inventory:low-stock-alerts';
    protected $description = 'Notify stores/procurement and branch management about items at or below reorder level.';

    public function handle(): int
    {
        $sent = 0;
        $items = InventoryItem::query()
            ->with('property')
            ->where('is_active', true)
            ->where('track_stock', true)
            ->where('reorder_level', '>', 0)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('property_id')
            ->orderBy('name')
            ->get();

        foreach ($items as $item) {
            $eventKey = 'low-stock:'.today()->format('Ymd');
            foreach ($this->operationalRecipients($item->property_id, ['store_manager', 'procurement_officer']) as $user) {
                if ($this->notifyOnce($user, $item, 'low_stock', $eventKey, [
                    'event' => 'low_stock',
                    'subject' => 'Low stock: '.$item->name,
                    'message' => $item->name.' has reached or fallen below its reorder level.',
                    'detail' => 'On hand: '.rtrim(rtrim(number_format((float) $item->quantity_on_hand, 4, '.', ''), '0'), '.').' '.$item->unit.' · Reorder level: '.rtrim(rtrim(number_format((float) $item->reorder_level, 4, '.', ''), '0'), '.').' '.$item->unit,
                    'url' => url('/inventory'),
                    'action' => 'Open inventory',
                    'icon' => 'fa-box-open',
                ])) {
                    $sent++;
                }
            }
        }

        $this->info("Checked {$items->count()} low-stock item(s); sent {$sent} notification(s).");
        return self::SUCCESS;
    }
}

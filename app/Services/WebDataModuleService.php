<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Schema;

class WebDataModuleService
{
    public function modules(): array
    {
        return [
            'rooms' => [
                'label' => 'Rooms',
                'table' => 'rooms',
                'columns' => [
                    'room_number',
                    'name',
                    'building',
                    'floor',
                    'status',
                    'housekeeping_status',
                    'notes',
                ],
            ],
            'reservations' => [
                'label' => 'Reservations',
                'table' => $this->firstTable(['reservations', 'bookings']),
                'columns' => [
                    'booking_reference',
                    'reference',
                    'guest_name',
                    'status',
                    'check_in_date',
                    'check_out_date',
                    'adults',
                    'children',
                    'notes',
                ],
            ],
            'guests' => [
                'label' => 'Guests',
                'table' => $this->firstTable(['guests', 'customers']),
                'columns' => [
                    'full_name',
                    'name',
                    'email',
                    'phone',
                    'nationality',
                    'address',
                    'notes',
                ],
            ],
            'inventory' => [
                'label' => 'Inventory',
                'table' => $this->firstTable([
                    'inventory_items',
                    'stock_items',
                    'items',
                ]),
                'columns' => [
                    'name',
                    'item_name',
                    'sku',
                    'code',
                    'category',
                    'unit',
                    'reorder_level',
                    'cost_price',
                    'selling_price',
                    'notes',
                ],
            ],
            'suppliers' => [
                'label' => 'Suppliers',
                'table' => 'suppliers',
                'columns' => [
                    'name',
                    'contact_person',
                    'email',
                    'phone',
                    'address',
                    'tin',
                    'notes',
                ],
            ],
            'employees' => [
                'label' => 'Employees',
                'table' => 'employees',
                'columns' => [
                    'employee_number',
                    'full_name',
                    'name',
                    'email',
                    'phone',
                    'department',
                    'job_title',
                    'status',
                ],
            ],
            'corporate-accounts' => [
                'label' => 'Corporate Accounts',
                'table' => $this->firstTable([
                    'corporate_accounts',
                    'companies',
                ]),
                'columns' => [
                    'name',
                    'contact_person',
                    'email',
                    'phone',
                    'tin',
                    'credit_limit',
                    'status',
                ],
            ],
            'events' => [
                'label' => 'Events',
                'table' => 'events',
                'columns' => [
                    'title',
                    'name',
                    'event_date',
                    'start_at',
                    'end_at',
                    'status',
                    'guest_count',
                    'notes',
                ],
            ],
        ];
    }

    public function get(string $module): array
    {
        $modules = $this->modules();

        abort_unless(
            isset($modules[$module]),
            404,
            'Unsupported data module.'
        );

        $definition = $modules[$module];

        abort_unless(
            $definition['table']
            && Schema::hasTable($definition['table']),
            404,
            $definition['label'].' table is not available.'
        );

        $definition['columns'] = array_values(
            array_filter(
                $definition['columns'],
                fn (string $column): bool =>
                    Schema::hasColumn(
                        $definition['table'],
                        $column
                    )
            )
        );

        return $definition;
    }

    private function firstTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }
}

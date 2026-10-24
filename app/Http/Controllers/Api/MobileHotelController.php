<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileHotelController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard(Request $request): JsonResponse
    {
        $propertyId = $this->propertyId($request);
        $today = now()->toDateString();

        $roomsTable = $this->firstTable(['rooms']);
        $bookingsTable = $this->firstTable(['reservations', 'bookings']);

        $occupied = 0;
        $available = 0;

        if ($roomsTable) {
            $roomQuery = $this->scopedQuery($roomsTable, $propertyId);

            if (Schema::hasColumn($roomsTable, 'status')) {
                $occupied = (clone $roomQuery)
                    ->whereIn(DB::raw('LOWER(status)'), [
                        'occupied',
                        'in_house',
                        'checked_in',
                    ])
                    ->count();

                $available = (clone $roomQuery)
                    ->whereIn(DB::raw('LOWER(status)'), [
                        'available',
                        'vacant',
                        'ready',
                    ])
                    ->count();
            } else {
                $available = $roomQuery->count();
            }
        }

        $arrivals = 0;
        $departures = 0;

        if ($bookingsTable) {
            $bookingQuery = $this->scopedQuery($bookingsTable, $propertyId);

            $arrivalColumn = $this->firstColumn($bookingsTable, [
                'check_in_date',
                'check_in',
                'arrival_date',
                'arrival_at',
            ]);

            $departureColumn = $this->firstColumn($bookingsTable, [
                'check_out_date',
                'check_out',
                'departure_date',
                'departure_at',
            ]);

            if ($arrivalColumn) {
                $arrivals = (clone $bookingQuery)
                    ->whereDate($arrivalColumn, $today)
                    ->count();
            }

            if ($departureColumn) {
                $departures = (clone $bookingQuery)
                    ->whereDate($departureColumn, $today)
                    ->count();
            }
        }

        $openTasks = 0;

        foreach ([
            'housekeeping_tasks',
            'maintenance_requests',
            'guest_service_requests',
            'room_service_requests',
            'laundry_orders',
            'guest_complaints',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = $this->scopedQuery($table, $propertyId);

            if (Schema::hasColumn($table, 'assigned_to')) {
                $query->where('assigned_to', $request->user()->id);
            }

            if (Schema::hasColumn($table, 'status')) {
                $query->whereNotIn(DB::raw('LOWER(status)'), [
                    'completed',
                    'done',
                    'resolved',
                    'closed',
                    'cancelled',
                    'canceled',
                    'delivered',
                    'inspected',
                ]);
            }

            $openTasks += $query->count();
        }

        return response()->json([
            'stats' => [
                'occupied_rooms' => $occupied,
                'available_rooms' => $available,
                'arrivals_today' => $arrivals,
                'departures_today' => $departures,
                'open_tasks' => $openTasks,
                'revenue_today' => $this->revenueToday($propertyId, $today),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Rooms
    |--------------------------------------------------------------------------
    */

    public function rooms(Request $request): JsonResponse
    {
        $table = $this->requireTable(['rooms'], 'Rooms');

        $query = $this->scopedQuery($table, $this->propertyId($request));

        if ($search = trim((string) $request->query('search', ''))) {
            $this->applySearch($query, $table, $search, [
                'room_number',
                'number',
                'name',
                'code',
                'status',
            ]);
        }

        $this->safeOrder($query, $table, [
            'room_number',
            'number',
            'name',
            'id',
        ]);

        $rows = $query
            ->limit($this->limit($request))
            ->get();

        return response()->json([
            'data' => $rows
                ->map(fn ($row) => $this->normaliseRoom((array) $row))
                ->values(),
        ]);
    }

    public function room(Request $request, int $id): JsonResponse
    {
        $table = $this->requireTable(['rooms'], 'Rooms');

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Room not found.');

        return response()->json([
            'data' => $this->normaliseRoom((array) $row),
        ]);
    }

    public function updateRoom(Request $request, int $id): JsonResponse
    {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable(['rooms'], 'Rooms');

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Room not found.');

        $payload = $this->editablePayload(
            $request,
            $table,
            [
                'room_number',
                'number',
                'name',
                'code',
                'building',
                'floor',
                'status',
                'housekeeping_status',
                'is_active',
                'notes',
            ]
        );

        $this->updateRecord($table, $id, $payload);

        $fresh = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        return response()->json([
            'message' => 'Room updated successfully.',
            'data' => $this->normaliseRoom((array) $fresh),
        ]);
    }

    public function deleteRoom(Request $request, int $id): JsonResponse
    {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable(['rooms'], 'Rooms');

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Room not found.');

        foreach ([
            'reservations',
            'bookings',
            'housekeeping_tasks',
            'maintenance_requests',
        ] as $relatedTable) {
            if (
                ! Schema::hasTable($relatedTable)
                || ! Schema::hasColumn($relatedTable, 'room_id')
            ) {
                continue;
            }

            $related = DB::table($relatedTable)
                ->where('room_id', $id);

            if (Schema::hasColumn($relatedTable, 'deleted_at')) {
                $related->whereNull('deleted_at');
            }

            if (Schema::hasColumn($relatedTable, 'status')) {
                $related->whereNotIn(DB::raw('LOWER(status)'), [
                    'cancelled',
                    'canceled',
                    'completed',
                    'closed',
                    'checked_out',
                    'resolved',
                    'done',
                ]);
            }

            if ($related->exists()) {
                return response()->json([
                    'message' =>
                        'This room cannot be deleted because it has active hotel records. '
                        .'Complete or cancel those records first.',
                ], 422);
            }
        }

        $this->deleteRecord($table, $id);

        return response()->json([
            'message' => 'Room deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Bookings / Reservations
    |--------------------------------------------------------------------------
    */

    public function bookings(Request $request): JsonResponse
    {
        $table = $this->requireTable(
            ['reservations', 'bookings'],
            'Bookings'
        );

        $query = $this->scopedQuery(
            $table,
            $this->propertyId($request)
        );

        if ($search = trim((string) $request->query('search', ''))) {
            $this->applySearch($query, $table, $search, [
                'booking_reference',
                'reference',
                'confirmation_number',
                'reservation_number',
                'guest_name',
                'status',
            ]);
        }

        $this->safeOrder($query, $table, [
            'check_in_date',
            'check_in',
            'arrival_date',
            'created_at',
            'id',
        ], 'desc');

        $rows = $query
            ->limit($this->limit($request))
            ->get();

        return response()->json([
            'data' => $rows
                ->map(fn ($row) => $this->normaliseBooking((array) $row))
                ->values(),
        ]);
    }

    public function booking(Request $request, int $id): JsonResponse
    {
        $table = $this->requireTable(
            ['reservations', 'bookings'],
            'Bookings'
        );

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Booking not found.');

        return response()->json([
            'data' => $this->normaliseBooking((array) $row),
        ]);
    }

    public function updateBooking(Request $request, int $id): JsonResponse
    {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable(
            ['reservations', 'bookings'],
            'Bookings'
        );

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Booking not found.');

        $payload = $this->editablePayload(
            $request,
            $table,
            [
                'booking_reference',
                'reference',
                'confirmation_number',
                'reservation_number',
                'guest_name',
                'status',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'arrival_date',
                'departure_date',
                'arrival_at',
                'departure_at',
                'adults',
                'children',
                'special_requests',
                'notes',
            ]
        );

        $this->updateRecord($table, $id, $payload);

        $fresh = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        return response()->json([
            'message' => 'Booking updated successfully.',
            'data' => $this->normaliseBooking((array) $fresh),
        ]);
    }

    public function deleteBooking(Request $request, int $id): JsonResponse
    {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable(
            ['reservations', 'bookings'],
            'Bookings'
        );

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Booking not found.');

        $status = strtolower((string) ((array) $row)['status'] ?? '');

        if (in_array($status, ['checked_in', 'in_house'], true)) {
            return response()->json([
                'message' =>
                    'This booking cannot be deleted while the guest is checked in.',
            ], 422);
        }

        $this->deleteRecord($table, $id);

        return response()->json([
            'message' => 'Booking deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Guests
    |--------------------------------------------------------------------------
    */

    public function guests(Request $request): JsonResponse
    {
        $table = $this->requireTable(
            ['guests', 'customers'],
            'Guests'
        );

        $query = $this->scopedQuery(
            $table,
            $this->propertyId($request)
        );

        if ($search = trim((string) $request->query('search', ''))) {
            $this->applySearch($query, $table, $search, [
                'full_name',
                'name',
                'first_name',
                'last_name',
                'email',
                'phone',
                'telephone',
                'mobile',
            ]);
        }

        $this->safeOrder($query, $table, [
            'full_name',
            'name',
            'first_name',
            'id',
        ]);

        $rows = $query
            ->limit($this->limit($request))
            ->get();

        return response()->json([
            'data' => $rows
                ->map(fn ($row) => $this->normaliseGuest((array) $row))
                ->values(),
        ]);
    }

    public function guest(Request $request, int $id): JsonResponse
    {
        $table = $this->requireTable(
            ['guests', 'customers'],
            'Guests'
        );

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Guest not found.');

        return response()->json([
            'data' => $this->normaliseGuest((array) $row),
        ]);
    }

    public function updateGuest(Request $request, int $id): JsonResponse
    {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable(
            ['guests', 'customers'],
            'Guests'
        );

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Guest not found.');

        $payload = $this->editablePayload(
            $request,
            $table,
            [
                'full_name',
                'name',
                'first_name',
                'last_name',
                'email',
                'phone',
                'telephone',
                'mobile',
                'address',
                'nationality',
                'gender',
                'date_of_birth',
                'id_type',
                'id_number',
                'passport_number',
                'company',
                'notes',
            ]
        );

        $this->updateRecord($table, $id, $payload);

        $fresh = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        return response()->json([
            'message' => 'Guest updated successfully.',
            'data' => $this->normaliseGuest((array) $fresh),
        ]);
    }

    public function deleteGuest(Request $request, int $id): JsonResponse
    {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable(
            ['guests', 'customers'],
            'Guests'
        );

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Guest not found.');

        foreach (['reservations', 'bookings'] as $bookingTable) {
            if (
                ! Schema::hasTable($bookingTable)
                || ! Schema::hasColumn($bookingTable, 'guest_id')
            ) {
                continue;
            }

            $active = DB::table($bookingTable)
                ->where('guest_id', $id);

            if (Schema::hasColumn($bookingTable, 'deleted_at')) {
                $active->whereNull('deleted_at');
            }

            if (Schema::hasColumn($bookingTable, 'status')) {
                $active->whereNotIn(DB::raw('LOWER(status)'), [
                    'cancelled',
                    'canceled',
                    'completed',
                    'checked_out',
                    'closed',
                ]);
            }

            if ($active->exists()) {
                return response()->json([
                    'message' =>
                        'This guest cannot be deleted because there are active bookings.',
                ], 422);
            }
        }

        $this->deleteRecord($table, $id);

        return response()->json([
            'message' => 'Guest deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tasks
    |--------------------------------------------------------------------------
    */

    public function tasks(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $propertyId = $this->propertyId($request);
        $items = collect();

        $definitions = [
            'housekeeping_tasks' => 'Housekeeping',
            'maintenance_requests' => 'Maintenance',
            'guest_service_requests' => 'Guest Service',
            'room_service_requests' => 'Room Service',
            'laundry_orders' => 'Laundry',
            'guest_complaints' => 'Guest Complaint',
        ];

        foreach ($definitions as $table => $type) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = $this->scopedQuery($table, $propertyId);

            if (! Schema::hasColumn($table, 'assigned_to')) {
                continue;
            }

            $query->where('assigned_to', $userId);

            if (Schema::hasColumn($table, 'status')) {
                $query->whereNotIn(DB::raw('LOWER(status)'), [
                    'completed',
                    'done',
                    'resolved',
                    'closed',
                    'cancelled',
                    'canceled',
                    'delivered',
                    'inspected',
                ]);
            }

            foreach ($query->limit(100)->get() as $row) {
                $data = (array) $row;

                $items->push([
                    'id' => $data['id'] ?? null,
                    'task_type' => $type,
                    'title' => $this->pick($data, [
                        'title',
                        'subject',
                        'request_type',
                        'service_type',
                        'complaint_type',
                    ], $type.' Task'),
                    'description' => $this->pick($data, [
                        'description',
                        'details',
                        'notes',
                        'request_details',
                        'issue',
                    ], ''),
                    'status' => $data['status'] ?? 'open',
                    'priority' => $data['priority'] ?? null,
                    'due_at' => $data['due_at'] ?? null,
                    'created_at' => $data['created_at'] ?? null,
                ]);
            }
        }

        $items = $items
            ->sortBy(fn ($item) => $item['due_at'] ?: '9999-12-31 23:59:59')
            ->values();

        return response()->json([
            'data' => $items,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POS / Restaurant / Bar
    |--------------------------------------------------------------------------
    */

    public function posOrders(Request $request): JsonResponse
    {
        $table = $this->requireTable([
            'pos_orders',
            'restaurant_orders',
            'orders',
        ], 'Restaurant/Bar orders');

        $query = $this->scopedQuery(
            $table,
            $this->propertyId($request)
        );

        if ($search = trim((string) $request->query('search', ''))) {
            $this->applySearch($query, $table, $search, [
                'order_number',
                'reference',
                'guest_name',
                'customer_name',
                'status',
            ]);
        }

        $this->safeOrder(
            $query,
            $table,
            ['created_at', 'ordered_at', 'id'],
            'desc'
        );

        $rows = $query
            ->limit($this->limit($request))
            ->get();

        return response()->json([
            'data' => $rows
                ->map(fn ($row) => $this->normalisePosOrder((array) $row))
                ->values(),
        ]);
    }

    public function updatePosOrder(Request $request, int $id): JsonResponse
    {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable([
            'pos_orders',
            'restaurant_orders',
            'orders',
        ], 'Restaurant/Bar orders');

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Order not found.');

        $payload = $this->editablePayload(
            $request,
            $table,
            [
                'status',
                'guest_name',
                'customer_name',
                'table_number',
                'room_number',
                'notes',
            ]
        );

        $this->updateRecord($table, $id, $payload);

        $fresh = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        return response()->json([
            'message' => 'Order updated successfully.',
            'data' => $this->normalisePosOrder((array) $fresh),
        ]);
    }

    public function deletePosOrder(Request $request, int $id): JsonResponse
    {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable([
            'pos_orders',
            'restaurant_orders',
            'orders',
        ], 'Restaurant/Bar orders');

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Order not found.');

        $data = (array) $row;
        $status = strtolower((string) ($data['status'] ?? ''));

        if (in_array($status, [
            'paid',
            'completed',
            'closed',
            'posted',
        ], true)) {
            return response()->json([
                'message' =>
                    'Completed or paid POS orders cannot be deleted. '
                    .'Void or reverse the order instead.',
            ], 422);
        }

        $this->deleteRecord($table, $id);

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    public function inventoryItems(Request $request): JsonResponse
    {
        $table = $this->requireTable([
            'inventory_items',
            'stock_items',
            'items',
        ], 'Inventory');

        $query = $this->scopedQuery(
            $table,
            $this->propertyId($request)
        );

        if ($search = trim((string) $request->query('search', ''))) {
            $this->applySearch($query, $table, $search, [
                'name',
                'item_name',
                'sku',
                'code',
                'category',
            ]);
        }

        $this->safeOrder(
            $query,
            $table,
            ['name', 'item_name', 'sku', 'id']
        );

        $rows = $query
            ->limit($this->limit($request))
            ->get();

        return response()->json([
            'data' => $rows
                ->map(fn ($row) => $this->normaliseInventory((array) $row))
                ->values(),
        ]);
    }

    public function updateInventoryItem(
        Request $request,
        int $id
    ): JsonResponse {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable([
            'inventory_items',
            'stock_items',
            'items',
        ], 'Inventory');

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Inventory item not found.');

        $payload = $this->editablePayload(
            $request,
            $table,
            [
                'name',
                'item_name',
                'sku',
                'code',
                'category',
                'unit',
                'reorder_level',
                'minimum_stock',
                'cost_price',
                'selling_price',
                'is_active',
                'notes',
            ]
        );

        $this->updateRecord($table, $id, $payload);

        $fresh = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        return response()->json([
            'message' => 'Inventory item updated successfully.',
            'data' => $this->normaliseInventory((array) $fresh),
        ]);
    }

    public function deleteInventoryItem(
        Request $request,
        int $id
    ): JsonResponse {
        $this->authoriseMobileWrite($request);

        $table = $this->requireTable([
            'inventory_items',
            'stock_items',
            'items',
        ], 'Inventory');

        $row = $this->findScopedRecord(
            $table,
            $id,
            $this->propertyId($request)
        );

        abort_if(! $row, 404, 'Inventory item not found.');

        foreach ([
            'inventory_movements',
            'stock_movements',
            'purchase_order_items',
            'goods_received_items',
        ] as $relatedTable) {
            if (! Schema::hasTable($relatedTable)) {
                continue;
            }

            $foreignKey = null;

            foreach ([
                'inventory_item_id',
                'stock_item_id',
                'item_id',
            ] as $candidate) {
                if (Schema::hasColumn($relatedTable, $candidate)) {
                    $foreignKey = $candidate;
                    break;
                }
            }

            if ($foreignKey && DB::table($relatedTable)
                ->where($foreignKey, $id)
                ->exists()) {
                return response()->json([
                    'message' =>
                        'This inventory item has transaction history and cannot be deleted.',
                ], 422);
            }
        }

        $this->deleteRecord($table, $id);

        return response()->json([
            'message' => 'Inventory item deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    public function notifications(Request $request): JsonResponse
    {
        if (! Schema::hasTable('notifications')) {
            return response()->json([
                'data' => [],
            ]);
        }

        $rows = DB::table('notifications')
            ->where('notifiable_type', get_class($request->user()))
            ->where('notifiable_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit($this->limit($request))
            ->get();

        return response()->json([
            'data' => $rows
                ->map(function ($row): array {
                    $data = json_decode(
                        (string) $row->data,
                        true
                    ) ?: [];

                    return [
                        'id' => (string) $row->id,
                        'title' =>
                            $data['title']
                            ?? $data['subject']
                            ?? 'Notification',
                        'message' =>
                            $data['message']
                            ?? $data['body']
                            ?? '',
                        'type' =>
                            $data['type']
                            ?? class_basename((string) $row->type),
                        'url' => $data['url'] ?? null,
                        'read_at' => $row->read_at,
                        'created_at' => $row->created_at,
                        'data' => $data,
                    ];
                })
                ->values(),
        ]);
    }

    public function markNotificationRead(
        Request $request,
        string $id
    ): JsonResponse {
        if (! Schema::hasTable('notifications')) {
            return response()->json([
                'message' => 'Notification marked as read.',
            ]);
        }

        $payload = [
            'read_at' => now(),
        ];

        if (Schema::hasColumn('notifications', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_type', get_class($request->user()))
            ->where('notifiable_id', $request->user()->id)
            ->update($payload);

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllNotificationsRead(
        Request $request
    ): JsonResponse {
        if (Schema::hasTable('notifications')) {
            $payload = [
                'read_at' => now(),
            ];

            if (Schema::hasColumn('notifications', 'updated_at')) {
                $payload['updated_at'] = now();
            }

            DB::table('notifications')
                ->where('notifiable_type', get_class($request->user()))
                ->where('notifiable_id', $request->user()->id)
                ->whereNull('read_at')
                ->update($payload);
        }

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Authorisation
    |--------------------------------------------------------------------------
    */

    private function authoriseMobileWrite(Request $request): void
    {
        $allowedRoles = [
            'super_admin',
            'admin',
            'hotel_owner',
            'general_manager',
            'branch_manager',
        ];

        $role = strtolower(
            (string) ($request->user()->role ?? '')
        );

        abort_unless(
            in_array($role, $allowedRoles, true),
            403,
            'You do not have permission to edit or delete this record.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Database Helpers
    |--------------------------------------------------------------------------
    */

    private function propertyId(Request $request): ?int
    {
        $value = $request->user()->property_id ?? null;

        return $value === null
            ? null
            : (int) $value;
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

    private function requireTable(
        array $tables,
        string $module
    ): string {
        $table = $this->firstTable($tables);

        abort_if(
            ! $table,
            503,
            $module.' table is not available in the current database.'
        );

        return $table;
    }

    private function scopedQuery(
        string $table,
        ?int $propertyId
    ): Builder {
        $query = DB::table($table);

        if ($propertyId !== null) {
            foreach ([
                'property_id',
                'branch_id',
                'hotel_id',
            ] as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $query->where($column, $propertyId);
                    break;
                }
            }
        }

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    private function findScopedRecord(
        string $table,
        int $id,
        ?int $propertyId
    ): ?object {
        return $this->scopedQuery($table, $propertyId)
            ->where('id', $id)
            ->first();
    }

    private function firstColumn(
        string $table,
        array $columns
    ): ?string {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function editablePayload(
        Request $request,
        string $table,
        array $allowedColumns
    ): array {
        $payload = [];

        foreach ($allowedColumns as $column) {
            if (
                ! Schema::hasColumn($table, $column)
                || ! $request->exists($column)
            ) {
                continue;
            }

            $value = $request->input($column);

            if ($column === 'is_active') {
                $normalised = filter_var(
                    $value,
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );

                $value = $normalised ?? ((int) $value === 1);
            }

            $payload[$column] = $value;
        }

        return $payload;
    }

    private function updateRecord(
        string $table,
        int $id,
        array $payload
    ): void {
        if (Schema::hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        if ($payload === []) {
            return;
        }

        DB::table($table)
            ->where('id', $id)
            ->update($payload);
    }

    private function deleteRecord(
        string $table,
        int $id
    ): void {
        if (Schema::hasColumn($table, 'deleted_at')) {
            $payload = [
                'deleted_at' => now(),
            ];

            if (Schema::hasColumn($table, 'updated_at')) {
                $payload['updated_at'] = now();
            }

            DB::table($table)
                ->where('id', $id)
                ->update($payload);

            return;
        }

        DB::table($table)
            ->where('id', $id)
            ->delete();
    }

    private function applySearch(
        Builder $query,
        string $table,
        string $search,
        array $columns
    ): void {
        $usable = array_values(array_filter(
            $columns,
            fn ($column) => Schema::hasColumn($table, $column)
        ));

        if ($usable === []) {
            return;
        }

        $query->where(
            function (Builder $sub) use (
                $usable,
                $search
            ): void {
                foreach ($usable as $index => $column) {
                    if ($index === 0) {
                        $sub->where(
                            $column,
                            'like',
                            '%'.$search.'%'
                        );
                    } else {
                        $sub->orWhere(
                            $column,
                            'like',
                            '%'.$search.'%'
                        );
                    }
                }
            }
        );
    }

    private function safeOrder(
        Builder $query,
        string $table,
        array $columns,
        string $direction = 'asc'
    ): void {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                $query->orderBy($column, $direction);
                return;
            }
        }
    }

    private function limit(Request $request): int
    {
        return max(
            1,
            min(
                200,
                (int) $request->query('limit', 100)
            )
        );
    }

    private function pick(
        array $row,
        array $keys,
        mixed $default = null
    ): mixed {
        foreach ($keys as $key) {
            if (
                array_key_exists($key, $row)
                && $row[$key] !== null
                && trim((string) $row[$key]) !== ''
            ) {
                return $row[$key];
            }
        }

        return $default;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalisers
    |--------------------------------------------------------------------------
    */

    private function normaliseRoom(array $row): array
    {
        return array_merge($row, [
            'room_number' => $this->pick(
                $row,
                ['room_number', 'number', 'name', 'code'],
                ''
            ),
            'name' => $this->pick(
                $row,
                ['name', 'room_number', 'number'],
                ''
            ),
            'status' => $this->pick(
                $row,
                ['status', 'housekeeping_status'],
                'unknown'
            ),
        ]);
    }

    private function normaliseBooking(array $row): array
    {
        return array_merge($row, [
            'booking_reference' => $this->pick(
                $row,
                [
                    'booking_reference',
                    'reference',
                    'confirmation_number',
                    'reservation_number',
                ],
                '#'.($row['id'] ?? '')
            ),
            'reference' => $this->pick(
                $row,
                [
                    'reference',
                    'booking_reference',
                    'confirmation_number',
                ],
                '#'.($row['id'] ?? '')
            ),
            'guest_name' => $this->pick(
                $row,
                [
                    'guest_name',
                    'customer_name',
                    'name',
                ],
                'Guest'
            ),
            'check_in' => $this->pick(
                $row,
                [
                    'check_in',
                    'check_in_date',
                    'arrival_date',
                    'arrival_at',
                ]
            ),
            'check_out' => $this->pick(
                $row,
                [
                    'check_out',
                    'check_out_date',
                    'departure_date',
                    'departure_at',
                ]
            ),
        ]);
    }

    private function normaliseGuest(array $row): array
    {
        $name = $this->pick(
            $row,
            ['full_name', 'name']
        );

        if (! $name) {
            $name = trim(
                ((string) ($row['first_name'] ?? ''))
                .' '
                .((string) ($row['last_name'] ?? ''))
            );
        }

        return array_merge($row, [
            'full_name' => $name ?: 'Guest',
            'name' => $name ?: 'Guest',
            'phone' => $this->pick(
                $row,
                ['phone', 'telephone', 'mobile']
            ),
        ]);
    }

    private function normalisePosOrder(array $row): array
    {
        return array_merge($row, [
            'order_number' => $this->pick(
                $row,
                [
                    'order_number',
                    'reference',
                    'receipt_number',
                ],
                '#'.($row['id'] ?? '')
            ),
            'reference' => $this->pick(
                $row,
                [
                    'reference',
                    'order_number',
                    'receipt_number',
                ],
                '#'.($row['id'] ?? '')
            ),
            'guest_name' => $this->pick(
                $row,
                [
                    'guest_name',
                    'customer_name',
                    'customer',
                ]
            ),
        ]);
    }

    private function normaliseInventory(array $row): array
    {
        return array_merge($row, [
            'name' => $this->pick(
                $row,
                [
                    'name',
                    'item_name',
                    'product_name',
                ],
                'Inventory Item'
            ),
            'item_name' => $this->pick(
                $row,
                [
                    'item_name',
                    'name',
                    'product_name',
                ],
                'Inventory Item'
            ),
            'sku' => $this->pick(
                $row,
                ['sku', 'code', 'item_code']
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Revenue
    |--------------------------------------------------------------------------
    */

    private function revenueToday(
        ?int $propertyId,
        string $today
    ): float {
        foreach ([
            'payments',
            'pos_payments',
            'transactions',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $amountColumn = $this->firstColumn(
                $table,
                [
                    'amount',
                    'paid_amount',
                    'total_amount',
                    'grand_total',
                ]
            );

            if (! $amountColumn) {
                continue;
            }

            $query = $this->scopedQuery(
                $table,
                $propertyId
            );

            $dateColumn = $this->firstColumn(
                $table,
                [
                    'paid_at',
                    'payment_date',
                    'transaction_date',
                    'created_at',
                ]
            );

            if ($dateColumn) {
                $query->whereDate(
                    $dateColumn,
                    $today
                );
            }

            if (Schema::hasColumn($table, 'status')) {
                $query->whereIn(
                    DB::raw('LOWER(status)'),
                    [
                        'paid',
                        'successful',
                        'success',
                        'completed',
                        'approved',
                    ]
                );
            }

            return (float) $query->sum($amountColumn);
        }

        return 0.0;
    }
}

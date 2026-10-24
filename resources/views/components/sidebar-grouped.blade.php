@php
$navGroups = [
    'Operations' => [
        ['label' => 'Dashboard', 'icon' => 'fas fa-chart-pie', 'route' => 'dashboard'],
        ['label' => 'Rooms', 'icon' => 'fas fa-bed', 'route' => 'rooms.index'],
        ['label' => 'Bookings', 'icon' => 'fas fa-calendar-check', 'route' => 'reservations.index'],
        ['label' => 'Guests', 'icon' => 'fas fa-users', 'route' => 'guests.index'],
        ['label' => 'Housekeeping', 'icon' => 'fas fa-broom', 'route' => 'operations.housekeeping.index'],
    ],
    'Food & Beverage' => [
        ['label' => 'Restaurant / Bar', 'icon' => 'fas fa-utensils', 'route' => 'pos.index'],
        ['label' => 'Orders', 'icon' => 'fas fa-receipt', 'route' => 'pos.orders.index'],
    ],
    'Stock & Procurement' => [
        ['label' => 'Inventory', 'icon' => 'fas fa-boxes-stacked', 'route' => 'inventory.index'],
        ['label' => 'Suppliers', 'icon' => 'fas fa-truck', 'route' => 'suppliers.index'],
        ['label' => 'Purchase Orders', 'icon' => 'fas fa-file-signature', 'route' => 'purchase-orders.index'],
    ],
    'Finance' => [
        ['label' => 'Invoices', 'icon' => 'fas fa-file-invoice', 'route' => 'invoices.index'],
        ['label' => 'Receipts', 'icon' => 'fas fa-receipt', 'route' => 'receipts.index'],
        ['label' => 'Reports', 'icon' => 'fas fa-chart-line', 'route' => 'reports.index'],
    ],
    'Administration' => [
        ['label' => 'Users', 'icon' => 'fas fa-user-shield', 'route' => 'admin.users.index'],
        ['label' => 'Permissions', 'icon' => 'fas fa-key', 'route' => 'admin.permissions.bulk'],
        ['label' => 'Configuration', 'icon' => 'fas fa-sliders', 'route' => 'admin.configuration.index'],
    ],
];
@endphp

<nav class="hms-sidebar-nav">
@foreach($navGroups as $group => $items)
    <details class="hms-nav-group" {{ $loop->first ? 'open' : '' }}>
        <summary>
            <span>{{ $group }}</span>
            <i class="fas fa-chevron-down"></i>
        </summary>
        <div class="hms-nav-items">
            @foreach($items as $item)
                @if(Route::has($item['route']))
                    <a href="{{ route($item['route']) }}" class="hms-nav-link">
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </details>
@endforeach
</nav>

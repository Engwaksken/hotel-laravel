<!doctype html>
<html lang="en">
<head>
    @php
        $systemName = \App\Support\SystemSetting::get(
            'system_name',
            auth()->user()?->property?->name ?? config('app.name', 'Hotel HMS')
        );

        $systemShortName = \App\Support\SystemSetting::get(
            'system_short_name',
            'Hotel HMS'
        );

        $primaryColor = \App\Support\SystemSetting::get(
            'primary_color',
            '#0f766e'
        );

        $secondaryColor = \App\Support\SystemSetting::get(
            'secondary_color',
            '#134e4a'
        );

        $accentColor = \App\Support\SystemSetting::get(
            'accent_color',
            '#14b8a6'
        );

        $normaliseBrandColor = static fn ($color, string $fallback): string =>
            is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color)
                ? $color
                : $fallback;

        $primaryColor = $normaliseBrandColor($primaryColor, '#0f766e');
        $secondaryColor = $normaliseBrandColor($secondaryColor, '#134e4a');
        $accentColor = $normaliseBrandColor($accentColor, '#14b8a6');

        $logoPath = \App\Support\SystemSetting::get('logo_path');
        $faviconPath = \App\Support\SystemSetting::get('favicon_path');

        $logoUrl = \App\Support\BrandingAsset::url($logoPath);
        $faviconUrl = \App\Support\BrandingAsset::url($faviconPath);

        $user = auth()->user();

        $isBranchManager = $user && method_exists($user, 'isBranchManager')
            ? $user->isBranchManager()
            : strtolower((string) ($user->role ?? '')) === 'branch_manager';

        $isSuperAdmin = strtolower((string) ($user->role ?? '')) === 'super_admin';

        $unreadNotifications = $user && method_exists($user, 'unreadNotifications')
            ? $user->unreadNotifications()->latest()->limit(8)->get()
            : collect();

        $unreadCount = $user && method_exists($user, 'unreadNotifications')
            ? $user->unreadNotifications()->count()
            : 0;

        $routeExists = fn (string $name): bool => \Illuminate\Support\Facades\Route::has($name);
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Hotel Management') · {{ $systemName }}
    </title>

    @if($faviconUrl)
        <link
            rel="icon"
            href="{{ $faviconUrl }}"
            type="{{ \App\Support\BrandingAsset::mimeType($faviconPath) }}"
            sizes="any"
        >
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/hms.css') }}?v={{ @filemtime(public_path('css/hms.css')) ?: time() }}"
    >

    <link rel="stylesheet" href="{{ asset('css/responsive-overrides.css') }}?v={{ @filemtime(public_path('css/responsive-overrides.css')) ?: time() }}">

    @if(file_exists(public_path('css/hms-enhancements.css')))
        <link
            rel="stylesheet"
            href="{{ asset('css/hms-enhancements.css') }}?v={{ @filemtime(public_path('css/hms-enhancements.css')) ?: time() }}"
        >
    @endif

    @if(file_exists(public_path('css/data-tools.css')))
        <link
            rel="stylesheet"
            href="{{ asset('css/data-tools.css') }}?v={{ @filemtime(public_path('css/data-tools.css')) ?: time() }}"
        >
    @endif

    @if(file_exists(public_path('css/hms-modals-exports.css')))
        <link
            rel="stylesheet"
            href="{{ asset('css/hms-modals-exports.css') }}?v={{ @filemtime(public_path('css/hms-modals-exports.css')) ?: time() }}"
        >
    @endif

    {{-- Load after the theme stylesheets so defaults cannot override saved branding. --}}
    <style id="hms-system-branding">
        :root {
            --hms-primary: {{ $primaryColor }};
            --hms-primary-dark: color-mix(in srgb, {{ $primaryColor }} 82%, #000);
            --hms-primary-soft: color-mix(in srgb, {{ $primaryColor }} 10%, #fff);
            --hms-secondary: {{ $secondaryColor }};
            --hms-accent: {{ $accentColor }};
            --hms-sidebar: {{ $secondaryColor }};
            --hms-sidebar-bg: {{ $secondaryColor }};
            --hms-sidebar-hover: color-mix(in srgb, {{ $secondaryColor }} 86%, #fff);
        }
    </style>

    @stack('styles')
</head>

<body>
<div class="shell">

    {{-- Mobile sidebar backdrop --}}
    <button
        type="button"
        class="sidebar-backdrop"
        id="sidebarBackdrop"
        aria-label="Close navigation"
        onclick="window.HMSLayout?.closeSidebar()"
    ></button>

    {{-- ============================================================
       SIDEBAR
       ============================================================ --}}
    <aside
        class="sidebar"
        id="sidebar"
        aria-label="Main navigation"
    >
        <div class="brand">
            <a
                href="{{ route('dashboard') }}"
                class="brand-link"
                aria-label="{{ $systemName }}"
            >
                <span class="brand-mark">
                    @if($logoUrl)
                        <img
                            src="{{ $logoUrl }}"
                            alt="{{ $systemName }}"
                            class="brand-logo"
                        >
                    @else
                        <i class="fas fa-hotel"></i>
                    @endif
                </span>

                <span class="brand-copy">
                    <strong>{{ $systemName }}</strong>
                    <small>{{ $systemShortName }}</small>
                </span>
            </a>

            <button
                type="button"
                class="sidebar-close"
                onclick="window.HMSLayout?.closeSidebar()"
                aria-label="Close navigation"
            >
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <nav class="sidebar-nav">

            {{-- Overview --}}
            <details
                class="nav-group"
                {{ request()->routeIs('dashboard') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-gauge-high"></i>
                        Overview
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    <a
                        href="{{ route('dashboard') }}"
                        class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    >
                        <i class="fas fa-gauge-high nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </details>

            {{-- Front Office --}}
            <details
                class="nav-group"
                {{ request()->routeIs('reservations.*', 'stays.*', 'folios.*', 'rooms.*', 'room-types.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-concierge-bell"></i>
                        Front Office
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @if($routeExists('reservations.calendar'))
                        <a
                            href="{{ route('reservations.calendar') }}"
                            class="{{ request()->routeIs('reservations.calendar') ? 'active' : '' }}"
                        >
                            <i class="fas fa-calendar-days nav-icon"></i>
                            <span>Room Calendar</span>
                        </a>
                    @endif

                    @if($routeExists('reservations.index'))
                        <a
                            href="{{ route('reservations.index') }}"
                            class="{{ request()->routeIs('reservations.index', 'reservations.show') ? 'active' : '' }}"
                        >
                            <i class="fas fa-calendar-check nav-icon"></i>
                            <span>Reservations</span>
                        </a>
                    @endif

                    @if($routeExists('reservations.create'))
                        <a
                            href="{{ route('reservations.create') }}"
                            class="{{ request()->routeIs('reservations.create') ? 'active' : '' }}"
                        >
                            <i class="fas fa-circle-plus nav-icon"></i>
                            <span>New / Walk-in</span>
                        </a>
                    @endif

                    @if($routeExists('rooms.index'))
                        <a
                            href="{{ route('rooms.index') }}"
                            class="{{ request()->routeIs('rooms.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-bed nav-icon"></i>
                            <span>Rooms</span>
                        </a>
                    @endif

                    @if($routeExists('room-types.index'))
                        <a
                            href="{{ route('room-types.index') }}"
                            class="{{ request()->routeIs('room-types.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-door-open nav-icon"></i>
                            <span>Room Types</span>
                        </a>
                    @endif
                </div>
            </details>

            {{-- POS / F&B --}}
            <details
                class="nav-group"
                {{ request()->routeIs('pos.*', 'kitchen.*', 'menu.*', 'inventory.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-utensils"></i>
                        POS &amp; F&amp;B
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @if($routeExists('pos.index'))
                        <a
                            href="{{ route('pos.index') }}"
                            class="{{ request()->routeIs('pos.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-cash-register nav-icon"></i>
                            <span>Restaurant &amp; Bar POS</span>
                        </a>
                    @endif

                    @if($routeExists('kitchen.index'))
                        <a
                            href="{{ route('kitchen.index') }}"
                            class="{{ request()->routeIs('kitchen.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-kitchen-set nav-icon"></i>
                            <span>Kitchen Display</span>
                        </a>
                    @endif

                    @if($routeExists('menu.index'))
                        <a
                            href="{{ route('menu.index') }}"
                            class="{{ request()->routeIs('menu.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-utensils nav-icon"></i>
                            <span>Menu Management</span>
                        </a>
                    @endif

                    @if($routeExists('inventory.index'))
                        <a
                            href="{{ route('inventory.index') }}"
                            class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-boxes-stacked nav-icon"></i>
                            <span>F&amp;B Inventory</span>
                        </a>
                    @endif
                </div>
            </details>

            {{-- Operations --}}
            <details
                class="nav-group"
                {{ request()->routeIs('operations.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-bell-concierge"></i>
                        Hotel Operations
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @php
                        $operationItems = [
                            ['operations.dashboard', 'fas fa-gauge', 'Operations'],
                            ['operations.housekeeping.index', 'fas fa-broom', 'Housekeeping'],
                            ['operations.maintenance.index', 'fas fa-screwdriver-wrench', 'Maintenance'],
                            ['operations.laundry.index', 'fas fa-shirt', 'Laundry'],
                            ['operations.minibar.index', 'fas fa-wine-bottle', 'Mini-bar'],
                            ['operations.guest-services.index', 'fas fa-hand-holding-heart', 'Guest Services'],
                            ['operations.lost-found.index', 'fas fa-magnifying-glass', 'Lost & Found'],
                        ];
                    @endphp

                    @foreach($operationItems as [$routeName, $icon, $label])
                        @if($routeExists($routeName))
                            <a
                                href="{{ route($routeName) }}"
                                class="{{ request()->routeIs(str_replace('.index', '.*', $routeName)) || request()->routeIs($routeName) ? 'active' : '' }}"
                            >
                                <i class="{{ $icon }} nav-icon"></i>
                                <span>{{ $label }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </details>

            {{-- Procurement --}}
            <details
                class="nav-group"
                {{ request()->routeIs('procurement.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-cart-shopping"></i>
                        Procurement &amp; Stores
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @php
                        $procurementItems = [
                            ['procurement.dashboard', 'fas fa-cart-shopping', 'Procurement'],
                            ['procurement.stores.index', 'fas fa-warehouse', 'Stores'],
                            ['procurement.suppliers.index', 'fas fa-truck-field', 'Suppliers'],
                            ['procurement.requisitions.index', 'fas fa-list-check', 'Requisitions'],
                            ['procurement.rfqs.index', 'fas fa-file-circle-question', 'RFQs'],
                            ['procurement.purchase-orders.index', 'fas fa-file-invoice', 'Purchase Orders'],
                            ['procurement.grns.index', 'fas fa-box-open', 'Goods Received'],
                            ['procurement.transfers.index', 'fas fa-right-left', 'Stock Transfers'],
                            ['procurement.stock-counts.index', 'fas fa-clipboard-list', 'Stock Counts'],
                            ['procurement.returns.index', 'fas fa-rotate-left', 'Purchase Returns'],
                            ['procurement.invoices.index', 'fas fa-file-invoice-dollar', 'Supplier Invoices'],
                            ['procurement.reports.index', 'fas fa-chart-column', 'Procurement Reports'],
                        ];
                    @endphp

                    @foreach($procurementItems as [$routeName, $icon, $label])
                        @if($routeExists($routeName))
                            <a
                                href="{{ route($routeName) }}"
                                class="{{ request()->routeIs(str_replace('.index', '.*', $routeName)) || request()->routeIs($routeName) ? 'active' : '' }}"
                            >
                                <i class="{{ $icon }} nav-icon"></i>
                                <span>{{ $label }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </details>

            {{-- Sales --}}
            <details
                class="nav-group"
                {{ request()->routeIs('sales.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-chart-line"></i>
                        Corporate Sales &amp; Events
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @php
                        $salesItems = [
                            ['sales.dashboard', 'fas fa-chart-line', 'Sales Dashboard'],
                            ['sales.companies.index', 'fas fa-building', 'Corporate Accounts'],
                            ['sales.agents.index', 'fas fa-user-tie', 'Travel Agents'],
                            ['sales.groups.index', 'fas fa-users', 'Group Reservations'],
                            ['sales.events.index', 'fas fa-calendar-days', 'Conferences & Events'],
                            ['sales.quotations.index', 'fas fa-file-signature', 'Quotations'],
                            ['sales.invoices.index', 'fas fa-file-invoice-dollar', 'Corporate Billing'],
                        ];
                    @endphp

                    @foreach($salesItems as [$routeName, $icon, $label])
                        @if($routeExists($routeName))
                            <a
                                href="{{ route($routeName) }}"
                                class="{{ request()->routeIs(str_replace('.index', '.*', $routeName)) || request()->routeIs($routeName) ? 'active' : '' }}"
                            >
                                <i class="{{ $icon }} nav-icon"></i>
                                <span>{{ $label }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </details>

            {{-- CRM --}}
            <details
                class="nav-group"
                {{ request()->routeIs('crm.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-address-book"></i>
                        CRM &amp; Loyalty
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @php
                        $crmItems = [
                            ['crm.dashboard', 'fas fa-address-book', 'CRM Dashboard'],
                            ['crm.guests.index', 'fas fa-user-group', 'Guest CRM'],
                            ['crm.loyalty.index', 'fas fa-award', 'Loyalty Programme'],
                            ['crm.complaints.index', 'fas fa-circle-exclamation', 'Complaints'],
                            ['crm.feedback.index', 'fas fa-star', 'Feedback'],
                            ['crm.promotions.index', 'fas fa-tags', 'Promotions'],
                            ['crm.marketing.index', 'fas fa-bullhorn', 'Marketing'],
                        ];
                    @endphp

                    @foreach($crmItems as [$routeName, $icon, $label])
                        @if($routeExists($routeName))
                            <a
                                href="{{ route($routeName) }}"
                                class="{{ request()->routeIs(str_replace('.index', '.*', $routeName)) || request()->routeIs($routeName) ? 'active' : '' }}"
                            >
                                <i class="{{ $icon }} nav-icon"></i>
                                <span>{{ $label }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </details>

            {{-- HR --}}
            <details
                class="nav-group"
                {{ request()->routeIs('hr.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-users-gear"></i>
                        Human Resources
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @php
                        $hrItems = [
                            ['hr.dashboard', 'fas fa-users-gear', 'HR Dashboard'],
                            ['hr.employees.index', 'fas fa-id-badge', 'Employees'],
                            ['hr.attendance.index', 'fas fa-user-check', 'Attendance'],
                            ['hr.shifts.index', 'fas fa-clock', 'Staff Shifts'],
                            ['hr.leave.index', 'fas fa-umbrella-beach', 'Leave'],
                            ['hr.payroll.index', 'fas fa-money-check-dollar', 'Payroll'],
                            ['hr.performance.index', 'fas fa-chart-simple', 'Performance'],
                        ];
                    @endphp

                    @foreach($hrItems as [$routeName, $icon, $label])
                        @if($routeExists($routeName))
                            <a
                                href="{{ route($routeName) }}"
                                class="{{ request()->routeIs(str_replace('.index', '.*', $routeName)) || request()->routeIs($routeName) ? 'active' : '' }}"
                            >
                                <i class="{{ $icon }} nav-icon"></i>
                                <span>{{ $label }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </details>

            {{-- Management --}}
            <details
                class="nav-group"
                {{ request()->routeIs('management.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-chart-pie"></i>
                        Management &amp; Control
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @if($routeExists('management.dashboard'))
                        <a
                            href="{{ route('management.dashboard') }}"
                            class="{{ request()->routeIs('management.dashboard') ? 'active' : '' }}"
                        >
                            <i class="fas fa-chart-pie nav-icon"></i>
                            <span>{{ $isBranchManager ? 'Branch Analytics' : 'Owner Analytics' }}</span>
                        </a>
                    @endif

                    @if($routeExists('management.budgets.index'))
                        <a
                            href="{{ route('management.budgets.index') }}"
                            class="{{ request()->routeIs('management.budgets.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-chart-line nav-icon"></i>
                            <span>Budgets &amp; Forecasts</span>
                        </a>
                    @endif

                    @unless($isBranchManager)
                        @php
                            $managementAdminItems = [
                                ['management.security.index', 'fas fa-shield-halved', 'Security & Audit'],
                                ['management.backups.index', 'fas fa-database', 'Backups'],
                                ['management.settings.index', 'fas fa-gears', 'System Settings'],
                                ['management.permissions.index', 'fas fa-user-lock', 'Permissions'],
                                ['management.health', 'fas fa-heart-pulse', 'System Health'],
                            ];
                        @endphp

                        @foreach($managementAdminItems as [$routeName, $icon, $label])
                            @if($routeExists($routeName))
                                <a
                                    href="{{ route($routeName) }}"
                                    class="{{ request()->routeIs(str_replace('.index', '.*', $routeName)) || request()->routeIs($routeName) ? 'active' : '' }}"
                                >
                                    <i class="{{ $icon }} nav-icon"></i>
                                    <span>{{ $label }}</span>
                                </a>
                            @endif
                        @endforeach
                    @endunless
                </div>
            </details>

            {{-- Administration --}}
            <details
                class="nav-group"
                {{ request()->routeIs('admin.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-sliders"></i>
                        {{ $isBranchManager ? 'Branch Administration' : 'System Administration' }}
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @if($routeExists('admin.index'))
                        <a
                            href="{{ route('admin.index') }}"
                            class="{{ request()->routeIs('admin.index') ? 'active' : '' }}"
                        >
                            <i class="fas fa-sliders nav-icon"></i>
                            <span>{{ $isBranchManager ? 'Branch Administration' : 'Administration' }}</span>
                        </a>
                    @endif

                    @if($routeExists('admin.setup'))
                        <a
                            href="{{ route('admin.setup') }}"
                            class="{{ request()->routeIs('admin.setup') ? 'active' : '' }}"
                        >
                            <i class="fas fa-building nav-icon"></i>
                            <span>Branch Profile</span>
                        </a>
                    @endif

                    @if($routeExists('admin.users'))
                        <a
                            href="{{ route('admin.users') }}"
                            class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-users-gear nav-icon"></i>
                            <span>Branch Users</span>
                        </a>
                    @endif

                    @if($routeExists('admin.imports'))
                        <a
                            href="{{ route('admin.imports') }}"
                            class="{{ request()->routeIs('admin.imports*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-file-import nav-icon"></i>
                            <span>Rooms &amp; Rates Import</span>
                        </a>
                    @endif

                    @unless($isBranchManager)
                        @if($routeExists('admin.configuration'))
                            <a
                                href="{{ route('admin.configuration') }}"
                                class="{{ request()->routeIs('admin.configuration') ? 'active' : '' }}"
                            >
                                <i class="fas fa-screwdriver-wrench nav-icon"></i>
                                <span>Hotel Configuration</span>
                            </a>
                        @endif

                        @if($routeExists('admin.website.index'))
                            <a
                                href="{{ route('admin.website.index') }}"
                                class="{{ request()->routeIs('admin.website.*') ? 'active' : '' }}"
                            >
                                <i class="fas fa-globe nav-icon"></i>
                                <span>Website &amp; Services</span>
                            </a>
                        @endif

                        @if($routeExists('admin.guest-features.index'))
                            <a
                                href="{{ route('admin.guest-features.index') }}"
                                class="{{ request()->routeIs('admin.guest-features.*') ? 'active' : '' }}"
                            >
                                <i class="fas fa-plug nav-icon"></i>
                                <span>Guest Experience &amp; Integrations</span>
                            </a>
                        @endif

                        @if($routeExists('admin.data-tools.index'))
                            <a
                                href="{{ route('admin.data-tools.index') }}"
                                class="{{ request()->routeIs('admin.data-tools.*') ? 'active' : '' }}"
                            >
                                <i class="fas fa-file-arrow-up nav-icon"></i>
                                <span>Bulk Upload &amp; Exports</span>
                            </a>
                        @endif

                        @if($isSuperAdmin && $routeExists('admin.system-configuration.index'))
                            <a
                                href="{{ route('admin.system-configuration.index') }}"
                                class="{{ request()->routeIs('admin.system-configuration.*') ? 'active' : '' }}"
                            >
                                <i class="fas fa-palette nav-icon"></i>
                                <span>System Branding</span>
                            </a>
                        @endif

                        @if($routeExists('admin.permissions.bulk'))
                            <a
                                href="{{ route('admin.permissions.bulk') }}"
                                class="{{ request()->routeIs('admin.permissions.bulk*') ? 'active' : '' }}"
                            >
                                <i class="fas fa-user-shield nav-icon"></i>
                                <span>Bulk Permissions</span>
                            </a>
                        @endif

                        @if($routeExists('admin.restore'))
                            <a
                                href="{{ route('admin.restore') }}"
                                class="{{ request()->routeIs('admin.restore*') ? 'active' : '' }}"
                            >
                                <i class="fas fa-clock-rotate-left nav-icon"></i>
                                <span>Restore</span>
                            </a>
                        @endif
                    @endunless
                </div>
            </details>

            {{-- My Account --}}
            @if($routeExists('profile.edit'))
                <details
                    class="nav-group"
                    {{ request()->routeIs('profile.*') ? 'open' : '' }}
                >
                    <summary>
                        <span>
                            <i class="fas fa-circle-user"></i>
                            My Account
                        </span>
                        <i class="fas fa-chevron-down nav-group-arrow"></i>
                    </summary>

                    <div class="nav-group-items">
                        <a
                            href="{{ route('profile.edit') }}"
                            class="{{ request()->routeIs('profile.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-user-pen nav-icon"></i>
                            <span>My Profile</span>
                        </a>
                    </div>
                </details>
            @endif

            {{-- Finance --}}
            <details
                class="nav-group"
                {{ request()->routeIs('accounting.*', 'cashier-shifts.*', 'invoices.*', 'receipts.*') ? 'open' : '' }}
            >
                <summary>
                    <span>
                        <i class="fas fa-calculator"></i>
                        Finance &amp; Accounting
                    </span>
                    <i class="fas fa-chevron-down nav-group-arrow"></i>
                </summary>

                <div class="nav-group-items">
                    @php
                        $financeItems = [
                            ['accounting.dashboard', 'fas fa-calculator', 'Accounting'],
                            ['accounting.journals.index', 'fas fa-book', 'General Ledger'],
                            ['accounting.night-audit.index', 'fas fa-moon', 'Night Audit'],
                            ['accounting.reconciliations.index', 'fas fa-scale-balanced', 'Reconciliation'],
                            ['accounting.reports.index', 'fas fa-chart-bar', 'Financial Reports'],
                            ['cashier-shifts.index', 'fas fa-cash-register', 'Cashier Shifts'],
                        ];
                    @endphp

                    @foreach($financeItems as [$routeName, $icon, $label])
                        @if($routeExists($routeName))
                            <a
                                href="{{ route($routeName) }}"
                                class="{{ request()->routeIs(str_replace('.index', '.*', $routeName)) || request()->routeIs($routeName) ? 'active' : '' }}"
                            >
                                <i class="{{ $icon }} nav-icon"></i>
                                <span>{{ $label }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </details>
        </nav>

        <div class="sidebar-footer">
            <small>{{ $systemShortName }}</small>
            <span>{{ now()->format('Y') }}</span>
        </div>
    </aside>

    {{-- ============================================================
       MAIN
       ============================================================ --}}
    <main class="main-panel">

        <header class="topbar">
            <div class="topbar-left">
                <button
                    type="button"
                    class="menu-btn"
                    onclick="window.HMSLayout?.toggleSidebar()"
                    aria-label="Toggle navigation"
                >
                    <i class="fas fa-bars"></i>
                </button>

                <div class="page-heading">
                    <b>@yield('page_title', 'Hotel Operations')</b>
                    <small>
                        {{ now()->format('l, d M Y · h:i A') }}
                    </small>
                </div>
            </div>

            <div class="topbar-actions">

                {{-- Notifications --}}
                <div class="notification-menu" id="notificationMenu">
                    <button
                        type="button"
                        class="topbar-icon-btn"
                        id="notificationToggle"
                        aria-label="Notifications"
                        aria-expanded="false"
                    >
                        <i class="fas fa-bell"></i>

                        @if($unreadCount > 0)
                            <span class="notification-badge">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <div
                        class="notification-dropdown"
                        id="notificationDropdown"
                        hidden
                    >
                        <div class="notification-dropdown-head">
                            <div>
                                <strong>Notifications</strong>
                                <small>{{ $unreadCount }} unread</small>
                            </div>

                            @if(
                                $unreadCount > 0
                                && $routeExists('staff-notifications.read-all')
                            )
                                <form
                                    method="POST"
                                    action="{{ route('staff-notifications.read-all') }}"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="link-btn"
                                    >
                                        Mark all read
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="notification-dropdown-body">
                            @forelse($unreadNotifications as $notification)
                                @php
                                    $notificationData = is_array($notification->data)
                                        ? $notification->data
                                        : [];

                                    $notificationTitle =
                                        $notificationData['title']
                                        ?? $notificationData['subject']
                                        ?? 'Notification';

                                    $notificationMessage =
                                        $notificationData['message']
                                        ?? $notificationData['body']
                                        ?? '';

                                    $notificationIcon =
                                        $notificationData['icon']
                                        ?? 'fas fa-bell';
                                @endphp

                                @if($routeExists('staff-notifications.read'))
                                    <form
                                        method="POST"
                                        action="{{ route('staff-notifications.read', $notification->id) }}"
                                        class="notification-item-form"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            class="notification-item"
                                        >
                                            <span class="notification-item-icon">
                                                <i class="{{ $notificationIcon }}"></i>
                                            </span>

                                            <span class="notification-item-copy">
                                                <strong>{{ $notificationTitle }}</strong>

                                                @if($notificationMessage)
                                                    <small>
                                                        {{ \Illuminate\Support\Str::limit($notificationMessage, 90) }}
                                                    </small>
                                                @endif

                                                <time>
                                                    {{ optional($notification->created_at)->diffForHumans() }}
                                                </time>
                                            </span>
                                        </button>
                                    </form>
                                @else
                                    <div class="notification-item">
                                        <span class="notification-item-icon">
                                            <i class="{{ $notificationIcon }}"></i>
                                        </span>

                                        <span class="notification-item-copy">
                                            <strong>{{ $notificationTitle }}</strong>

                                            @if($notificationMessage)
                                                <small>
                                                    {{ \Illuminate\Support\Str::limit($notificationMessage, 90) }}
                                                </small>
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            @empty
                                <div class="notification-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <strong>You're all caught up</strong>
                                    <small>No unread notifications.</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if($routeExists('profile.edit'))
                    <a
                        href="{{ route('profile.edit') }}"
                        class="topbar-icon-btn"
                        title="My Profile"
                        aria-label="My Profile"
                    >
                        <i class="fas fa-user-pen"></i>
                    </a>
                @endif

                {{-- User menu --}}
                <div class="user-menu">
                    <span class="user-avatar">
                        {{ strtoupper(substr((string) ($user->name ?? 'U'), 0, 1)) }}
                    </span>

                    <div class="user-copy">
                        <span>{{ $user->name }}</span>

                        <small>
                            {{ ucwords(str_replace('_', ' ', (string) $user->role)) }}

                            @if($user->property)
                                · {{ $user->property->name }}
                            @endif
                        </small>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            class="topbar-icon-btn"
                            type="submit"
                            title="Sign out"
                            aria-label="Sign out"
                        >
                            <i class="fas fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <section class="content">

            {{-- Flash messages --}}
            <div
                class="flash-stack"
                aria-live="polite"
                aria-atomic="true"
            >
                @if(session('success'))
                    <div class="alert success auto-dismiss">
                        <i class="fas fa-circle-check"></i>

                        <div>
                            {{ session('success') }}
                        </div>

                        <button
                            type="button"
                            class="alert-close"
                            aria-label="Close"
                        >
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert error auto-dismiss">
                        <i class="fas fa-circle-xmark"></i>

                        <div>
                            {{ session('error') }}
                        </div>

                        <button
                            type="button"
                            class="alert-close"
                            aria-label="Close"
                        >
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert warning auto-dismiss">
                        <i class="fas fa-triangle-exclamation"></i>

                        <div>
                            {{ session('warning') }}
                        </div>

                        <button
                            type="button"
                            class="alert-close"
                            aria-label="Close"
                        >
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert info auto-dismiss">
                        <i class="fas fa-circle-info"></i>

                        <div>
                            {{ session('info') }}
                        </div>

                        <button
                            type="button"
                            class="alert-close"
                            aria-label="Close"
                        >
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert error auto-dismiss">
                        <i class="fas fa-circle-exclamation"></i>

                        <div>
                            <b>Please correct the following:</b>

                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <button
                            type="button"
                            class="alert-close"
                            aria-label="Close"
                        >
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                @endif
            </div>

            @yield('content')
        </section>
    </main>
</div>

@stack('modals')

<script>
    window.HMSLayout = {
        sidebar: document.getElementById('sidebar'),
        backdrop: document.getElementById('sidebarBackdrop'),

        openSidebar() {
            this.sidebar?.classList.add('show');
            this.backdrop?.classList.add('show');
            document.body.classList.add('sidebar-open');
        },

        closeSidebar() {
            this.sidebar?.classList.remove('show');
            this.backdrop?.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        },

        toggleSidebar() {
            if (this.sidebar?.classList.contains('show')) {
                this.closeSidebar();
            } else {
                this.openSidebar();
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        /*
        |--------------------------------------------------------------------------
        | Sidebar behaviour
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll('.sidebar a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 1024) {
                    window.HMSLayout?.closeSidebar();
                }
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Only keep one sidebar group open at a time
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll('.nav-group').forEach(function (group) {
            group.addEventListener('toggle', function () {
                if (!group.open) {
                    return;
                }

                document.querySelectorAll('.nav-group').forEach(function (other) {
                    if (other !== group) {
                        other.removeAttribute('open');
                    }
                });
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        const notificationToggle =
            document.getElementById('notificationToggle');

        const notificationDropdown =
            document.getElementById('notificationDropdown');

        if (notificationToggle && notificationDropdown) {
            notificationToggle.addEventListener('click', function (event) {
                event.stopPropagation();

                const isHidden = notificationDropdown.hidden;

                notificationDropdown.hidden = !isHidden;
                notificationToggle.setAttribute(
                    'aria-expanded',
                    isHidden ? 'true' : 'false'
                );
            });

            document.addEventListener('click', function (event) {
                if (
                    !notificationDropdown.hidden
                    && !notificationDropdown.contains(event.target)
                    && !notificationToggle.contains(event.target)
                ) {
                    notificationDropdown.hidden = true;
                    notificationToggle.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Flash alerts
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll('.alert-close').forEach(function (button) {
            button.addEventListener('click', function () {
                const alert = button.closest('.alert');

                if (!alert) {
                    return;
                }

                alert.classList.add('fade-out');

                window.setTimeout(function () {
                    alert.remove();
                }, 300);
            });
        });

        document.querySelectorAll('.auto-dismiss').forEach(function (alert) {
            window.setTimeout(function () {
                if (!alert.isConnected) {
                    return;
                }

                alert.classList.add('fade-out');

                window.setTimeout(function () {
                    alert.remove();
                }, 300);
            }, 5000);
        });

        /*
        |--------------------------------------------------------------------------
        | Escape closes mobile sidebar / notification dropdown
        |--------------------------------------------------------------------------
        */

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            window.HMSLayout?.closeSidebar();

            if (notificationDropdown) {
                notificationDropdown.hidden = true;
                notificationToggle?.setAttribute(
                    'aria-expanded',
                    'false'
                );
            }
        });
    });
</script>

<script
    src="{{ asset('js/hms.js') }}?v={{ @filemtime(public_path('js/hms.js')) ?: time() }}"
></script>

<script
    src="{{ asset('js/form-placeholders.js') }}?v={{ @filemtime(public_path('js/form-placeholders.js')) ?: time() }}"
    defer
></script>

@if(file_exists(public_path('js/hms-modals.js')))
<script
    src="{{ asset('js/hms-modals.js') }}?v={{ @filemtime(public_path('js/hms-modals.js')) ?: time() }}"
></script>
@endif

@stack('scripts')
</body>
</html>

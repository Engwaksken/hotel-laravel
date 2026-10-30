<?php

declare(strict_types=1);

use App\Http\Controllers\{
    CashierShiftController,
    DashboardController,
    FolioController,
    FrontDeskController,
    GuestQrController,
    InvoiceDocumentController,
    ReservationWebController,
    RoomController,
    RoomTypeController
};
use App\Http\Controllers\Admin\{
    AdministrationController,
    BulkDataController,
    ImportController,
    PermissionBulkController,
    RestoreController,
    SystemConfigurationController
};
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\POS\{
    InventoryController,
    KitchenController,
    MenuController,
    PosController
};
use App\Http\Controllers\Procurement\{
    GrnController,
    ProcurementDashboardController,
    PurchaseOrderController,
    PurchaseReturnController,
    RequisitionController,
    ReportController,
    RfqController,
    StockCountController,
    StoreController,
    SupplierController,
    SupplierInvoiceController,
    TransferController
};
use App\Http\Controllers\Operations\{
    GuestServiceController,
    HousekeepingController,
    LaundryController,
    LostFoundController,
    MaintenanceController,
    MinibarController,
    OperationsDashboardController
};
use App\Http\Controllers\HR\{
    AttendanceController,
    EmployeeController,
    HrDashboardController,
    LeaveController,
    PayrollController,
    PerformanceController,
    ShiftController
};
use App\Http\Controllers\Sales\{
    CorporateController,
    CorporateInvoiceController,
    EventController,
    GroupReservationController,
    QuotationController,
    SalesDashboardController,
    TravelAgentController
};
use App\Http\Controllers\CRM\{
    ComplaintController,
    CrmDashboardController,
    FeedbackController,
    GuestCrmController,
    LoyaltyController,
    MarketingController,
    PromotionController
};
use App\Http\Controllers\Accounting\{
    AccountingDashboardController,
    JournalController,
    NightAuditController,
    ReconciliationController,
    ReportsController as AccountingReportsController
};
use App\Http\Controllers\Management\{
    BackupController,
    BudgetController,
    ManagementDashboardController,
    PermissionsController,
    SecurityController,
    SettingsController,
    SystemHealthController
};
use App\Http\Controllers\PublicSite\{
    HotelWebsiteController,
    OnlineBookingController
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Hotel HMS
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Authentication / Dashboard
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Rooms
    |--------------------------------------------------------------------------
    */

    Route::get('/rooms', [RoomController::class, 'index'])
        ->name('rooms.index');

    Route::post('/rooms', [RoomController::class, 'store'])
        ->name('rooms.store');

    Route::put('/rooms/{room}', [RoomController::class, 'update'])
        ->name('rooms.update');

    Route::get('/room-types', [RoomTypeController::class, 'index'])
        ->name('room-types.index');

    Route::post('/room-types', [RoomTypeController::class, 'store'])
        ->name('room-types.store');

    /*
    |--------------------------------------------------------------------------
    | Reservations / Front Desk
    |--------------------------------------------------------------------------
    */

    Route::get('/reservations/calendar', [ReservationWebController::class, 'calendar'])
        ->name('reservations.calendar');

    Route::get('/reservations', [ReservationWebController::class, 'index'])
        ->name('reservations.index');

    Route::get('/reservations/create', [ReservationWebController::class, 'create'])
        ->name('reservations.create');

    Route::post('/reservations', [ReservationWebController::class, 'store'])
        ->name('reservations.store');

    Route::get('/reservations/{reservation}', [ReservationWebController::class, 'show'])
        ->name('reservations.show');

    Route::post('/reservations/{reservation}/check-in', [FrontDeskController::class, 'checkIn'])
        ->name('reservations.check-in');

    Route::post('/stays/{stay}/check-out', [FrontDeskController::class, 'checkOut'])
        ->name('stays.check-out');

    /*
    |--------------------------------------------------------------------------
    | Folios / Guest Billing
    |--------------------------------------------------------------------------
    */

    Route::post('/folios/{folio}/charges', [FolioController::class, 'addCharge'])
        ->name('folios.charges');

    Route::post('/folios/{folio}/payments', [FolioController::class, 'payment'])
        ->name('folios.payments');

    Route::get('/folios/{folio}/invoice', [FolioController::class, 'invoice'])
        ->name('folios.invoice');

    Route::get('/folios/{folio}/receipt', [FolioController::class, 'receipt'])
        ->name('folios.receipt');

    /*
    |--------------------------------------------------------------------------
    | Invoice Preview / PDF / Instalment Receipts
    |--------------------------------------------------------------------------
    */

    Route::get('/invoices/{invoice}/preview', [InvoiceDocumentController::class, 'show'])
        ->name('invoices.preview');

    Route::get('/invoices/{invoice}/pdf', [InvoiceDocumentController::class, 'pdf'])
        ->name('invoices.pdf');

    Route::post('/invoices/{invoice}/receipts', [InvoiceDocumentController::class, 'receipt'])
        ->name('invoices.receipts.store');

    Route::get('/receipts/{receipt}/pdf', [InvoiceDocumentController::class, 'receiptPdf'])
        ->name('receipts.pdf');

    /*
    |--------------------------------------------------------------------------
    | Guest QR
    |--------------------------------------------------------------------------
    */

    Route::get('/guests/{guest}/qr', [GuestQrController::class, 'show'])
        ->name('guests.qr');

    Route::get('/guests/{guest}/qr.svg', [GuestQrController::class, 'svg'])
        ->name('guests.qr.svg');

    /*
    |--------------------------------------------------------------------------
    | POS / Restaurant / Bar
    |--------------------------------------------------------------------------
    */

    Route::get('/pos', [PosController::class, 'index'])
        ->name('pos.index');

    Route::get('/pos/outlets/{outlet}', [PosController::class, 'outlet'])
        ->name('pos.outlet');

    Route::post('/pos/outlets/{outlet}/orders', [PosController::class, 'store'])
        ->name('pos.orders.store');

    Route::get('/pos/orders/{order}', [PosController::class, 'show'])
        ->name('pos.orders.show');

    Route::post('/pos/orders/{order}/items', [PosController::class, 'addItem'])
        ->name('pos.orders.items');

    Route::post('/pos/orders/{order}/send-kitchen', [PosController::class, 'sendKitchen'])
        ->name('pos.orders.send-kitchen');

    Route::post('/pos/orders/{order}/settle', [PosController::class, 'settle'])
        ->name('pos.orders.settle');

    Route::get('/pos/orders/{order}/receipt', [PosController::class, 'receipt'])
        ->name('pos.orders.receipt');

    Route::get('/kitchen', [KitchenController::class, 'index'])
        ->name('kitchen.index');

    Route::post('/kitchen/items/{item}/status', [KitchenController::class, 'status'])
        ->name('kitchen.items.status');

    Route::get('/menu-management', [MenuController::class, 'index'])
        ->name('menu.index');

    Route::post('/menu-management/categories', [MenuController::class, 'category'])
        ->name('menu.categories.store');

    Route::post('/menu-management/items', [MenuController::class, 'item'])
        ->name('menu.items.store');

    Route::post('/menu-management/modifiers', [MenuController::class, 'modifier'])
        ->name('menu.modifiers.store');

    Route::post('/menu-management/items/{item}/recipe', [MenuController::class, 'recipe'])
        ->name('menu.items.recipe');

    Route::get('/inventory', [InventoryController::class, 'index'])
        ->name('inventory.index');

    Route::post('/inventory', [InventoryController::class, 'store'])
        ->name('inventory.store');

    Route::post('/inventory/{item}/adjust', [InventoryController::class, 'adjust'])
        ->name('inventory.adjust');

    /*
    |--------------------------------------------------------------------------
    | Accounting
    |--------------------------------------------------------------------------
    */

    Route::prefix('accounting')->name('accounting.')->group(function (): void {
        Route::get('/', AccountingDashboardController::class)
            ->name('dashboard');

        Route::get('/journals', [JournalController::class, 'index'])
            ->name('journals.index');

        Route::post('/journals', [JournalController::class, 'store'])
            ->name('journals.store');

        Route::get('/night-audit', [NightAuditController::class, 'index'])
            ->name('night-audit.index');

        Route::post('/night-audit/run', [NightAuditController::class, 'run'])
            ->name('night-audit.run');

        Route::get('/reports', [AccountingReportsController::class, 'index'])
            ->name('reports.index');

        Route::get('/reports/csv', [AccountingReportsController::class, 'csv'])
            ->name('reports.csv');

        Route::get('/reports/pdf', [AccountingReportsController::class, 'pdf'])
            ->name('reports.pdf');

        Route::get('/reconciliations', [ReconciliationController::class, 'index'])
            ->name('reconciliations.index');

        Route::post('/reconciliations', [ReconciliationController::class, 'store'])
            ->name('reconciliations.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Procurement
    |--------------------------------------------------------------------------
    */

    Route::prefix('procurement')->name('procurement.')->group(function (): void {
        Route::get('/', ProcurementDashboardController::class)
            ->name('dashboard');

        Route::get('/suppliers', [SupplierController::class, 'index'])
            ->name('suppliers.index');

        Route::post('/suppliers', [SupplierController::class, 'store'])
            ->name('suppliers.store');

        Route::get('/stores', [StoreController::class, 'index'])
            ->name('stores.index');

        Route::post('/stores', [StoreController::class, 'store'])
            ->name('stores.store');

        Route::get('/requisitions', [RequisitionController::class, 'index'])
            ->name('requisitions.index');

        Route::post('/requisitions', [RequisitionController::class, 'store'])
            ->name('requisitions.store');

        Route::post('/requisitions/{requisition}/approve', [RequisitionController::class, 'approve'])
            ->name('requisitions.approve');

        Route::get('/rfqs', [RfqController::class, 'index'])
            ->name('rfqs.index');

        Route::post('/rfqs', [RfqController::class, 'store'])
            ->name('rfqs.store');

        Route::post('/rfqs/{rfq}/suppliers/{supplier}/quote', [RfqController::class, 'quote'])
            ->name('rfqs.quote');

        Route::post('/rfqs/{rfq}/suppliers/{supplier}/award', [RfqController::class, 'award'])
            ->name('rfqs.award');

        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])
            ->name('purchase-orders.index');

        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])
            ->name('purchase-orders.store');

        Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
            ->name('purchase-orders.approve');

        Route::get('/grns', [GrnController::class, 'index'])
            ->name('grns.index');

        Route::post('/grns', [GrnController::class, 'store'])
            ->name('grns.store');

        Route::post('/grns/{grn}/post', [GrnController::class, 'post'])
            ->name('grns.post');

        Route::get('/transfers', [TransferController::class, 'index'])
            ->name('transfers.index');

        Route::post('/transfers', [TransferController::class, 'store'])
            ->name('transfers.store');

        Route::post('/transfers/{transfer}/post', [TransferController::class, 'post'])
            ->name('transfers.post');

        Route::get('/stock-counts', [StockCountController::class, 'index'])
            ->name('stock-counts.index');

        Route::post('/stock-counts', [StockCountController::class, 'store'])
            ->name('stock-counts.store');

        Route::post('/stock-counts/{stockCount}/submit', [StockCountController::class, 'submit'])
            ->name('stock-counts.submit');

        Route::get('/returns', [PurchaseReturnController::class, 'index'])
            ->name('returns.index');

        Route::post('/returns', [PurchaseReturnController::class, 'store'])
            ->name('returns.store');

        Route::post('/returns/{return}/post', [PurchaseReturnController::class, 'post'])
            ->name('returns.post');

        Route::get('/supplier-invoices', [SupplierInvoiceController::class, 'index'])
            ->name('invoices.index');

        Route::post('/supplier-invoices', [SupplierInvoiceController::class, 'store'])
            ->name('invoices.store');

        Route::post('/supplier-invoices/{invoice}/payments', [SupplierInvoiceController::class, 'pay'])
            ->name('invoices.pay');

        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    Route::prefix('operations')->name('operations.')->group(function (): void {
        Route::get('/', OperationsDashboardController::class)
            ->name('dashboard');

        Route::get('/housekeeping', [HousekeepingController::class, 'index'])
            ->name('housekeeping.index');

        Route::post('/housekeeping', [HousekeepingController::class, 'store'])
            ->name('housekeeping.store');

        Route::post('/housekeeping/{task}/status', [HousekeepingController::class, 'status'])
            ->name('housekeeping.status');

        Route::get('/maintenance', [MaintenanceController::class, 'index'])
            ->name('maintenance.index');

        Route::post('/maintenance', [MaintenanceController::class, 'store'])
            ->name('maintenance.store');

        Route::post('/maintenance/{maintenanceRequest}/status', [MaintenanceController::class, 'status'])
            ->name('maintenance.status');

        Route::get('/laundry', [LaundryController::class, 'index'])
            ->name('laundry.index');

        Route::post('/laundry/services', [LaundryController::class, 'service'])
            ->name('laundry.services.store');

        Route::post('/laundry/orders', [LaundryController::class, 'store'])
            ->name('laundry.orders.store');

        Route::post('/laundry/orders/{order}/status', [LaundryController::class, 'status'])
            ->name('laundry.orders.status');

        Route::get('/lost-found', [LostFoundController::class, 'index'])
            ->name('lost-found.index');

        Route::post('/lost-found', [LostFoundController::class, 'store'])
            ->name('lost-found.store');

        Route::post('/lost-found/{item}/status', [LostFoundController::class, 'status'])
            ->name('lost-found.status');

        Route::get('/minibar', [MinibarController::class, 'index'])
            ->name('minibar.index');

        Route::post('/minibar/configure', [MinibarController::class, 'configure'])
            ->name('minibar.configure');

        Route::post('/minibar/consume', [MinibarController::class, 'consume'])
            ->name('minibar.consume');

        Route::get('/guest-services', [GuestServiceController::class, 'index'])
            ->name('guest-services.index');

        Route::post('/guest-services', [GuestServiceController::class, 'store'])
            ->name('guest-services.store');

        Route::post('/guest-services/{guestServiceRequest}/status', [GuestServiceController::class, 'status'])
            ->name('guest-services.status');
    });

    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */

    Route::prefix('sales')->name('sales.')->group(function (): void {
        Route::get('/', SalesDashboardController::class)
            ->name('dashboard');

        Route::get('/companies', [CorporateController::class, 'index'])
            ->name('companies.index');

        Route::post('/companies', [CorporateController::class, 'store'])
            ->name('companies.store');

        Route::post('/companies/lpos', [CorporateController::class, 'lpo'])
            ->name('companies.lpo');

        Route::get('/agents', [TravelAgentController::class, 'index'])
            ->name('agents.index');

        Route::post('/agents', [TravelAgentController::class, 'store'])
            ->name('agents.store');

        Route::get('/groups', [GroupReservationController::class, 'index'])
            ->name('groups.index');

        Route::post('/groups', [GroupReservationController::class, 'store'])
            ->name('groups.store');

        Route::get('/events', [EventController::class, 'index'])
            ->name('events.index');

        Route::post('/events', [EventController::class, 'store'])
            ->name('events.store');

        Route::post('/events/venues', [EventController::class, 'venue'])
            ->name('events.venue');

        Route::get('/quotations', [QuotationController::class, 'index'])
            ->name('quotations.index');

        Route::post('/quotations', [QuotationController::class, 'store'])
            ->name('quotations.store');

        Route::post('/quotations/{quotation}/status', [QuotationController::class, 'status'])
            ->name('quotations.status');

        Route::get('/invoices', [CorporateInvoiceController::class, 'index'])
            ->name('invoices.index');

        Route::post('/invoices', [CorporateInvoiceController::class, 'store'])
            ->name('invoices.store');

        Route::post('/invoices/{invoice}/payments', [CorporateInvoiceController::class, 'pay'])
            ->name('invoices.pay');
    });

    /*
    |--------------------------------------------------------------------------
    | Human Resources
    |--------------------------------------------------------------------------
    */

    Route::prefix('hr')->name('hr.')->group(function (): void {
        Route::get('/', HrDashboardController::class)
            ->name('dashboard');

        Route::get('/employees', [EmployeeController::class, 'index'])
            ->name('employees.index');

        Route::post('/employees', [EmployeeController::class, 'store'])
            ->name('employees.store');

        Route::get('/attendance', [AttendanceController::class, 'index'])
            ->name('attendance.index');

        Route::post('/attendance', [AttendanceController::class, 'store'])
            ->name('attendance.store');

        Route::get('/shifts', [ShiftController::class, 'index'])
            ->name('shifts.index');

        Route::post('/shifts', [ShiftController::class, 'store'])
            ->name('shifts.store');

        Route::post('/shifts/assign', [ShiftController::class, 'assign'])
            ->name('shifts.assign');

        Route::get('/leave', [LeaveController::class, 'index'])
            ->name('leave.index');

        Route::post('/leave', [LeaveController::class, 'request'])
            ->name('leave.request');

        Route::post('/leave/{leave}/decision', [LeaveController::class, 'decision'])
            ->name('leave.decision');

        Route::get('/payroll', [PayrollController::class, 'index'])
            ->name('payroll.index');

        Route::post('/payroll/components', [PayrollController::class, 'component'])
            ->name('payroll.component');

        Route::post('/payroll/generate', [PayrollController::class, 'generate'])
            ->name('payroll.generate');

        Route::post('/payroll/{run}/approve', [PayrollController::class, 'approve'])
            ->name('payroll.approve');

        Route::post('/payroll/{run}/pay', [PayrollController::class, 'pay'])
            ->name('payroll.pay');

        Route::get('/performance', [PerformanceController::class, 'index'])
            ->name('performance.index');

        Route::post('/performance', [PerformanceController::class, 'store'])
            ->name('performance.store');
    });

    /*
    |--------------------------------------------------------------------------
    | CRM
    |--------------------------------------------------------------------------
    */

    Route::prefix('crm')->name('crm.')->group(function (): void {
        Route::get('/', CrmDashboardController::class)
            ->name('dashboard');

        Route::get('/guests', [GuestCrmController::class, 'index'])
            ->name('guests.index');

        Route::get('/guests/{guest}', [GuestCrmController::class, 'show'])
            ->name('guests.show');

        Route::post('/guests/{guest}/preferences', [GuestCrmController::class, 'preference'])
            ->name('guests.preference');

        Route::post('/guests/{guest}/notes', [GuestCrmController::class, 'note'])
            ->name('guests.note');

        Route::post('/guests/{guest}/consent', [GuestCrmController::class, 'consent'])
            ->name('guests.consent');

        Route::get('/loyalty', [LoyaltyController::class, 'index'])
            ->name('loyalty.index');

        Route::post('/loyalty/tiers', [LoyaltyController::class, 'tier'])
            ->name('loyalty.tiers.store');

        Route::post('/loyalty/guests/{guest}/adjust', [LoyaltyController::class, 'adjust'])
            ->name('loyalty.adjust');

        Route::get('/complaints', [ComplaintController::class, 'index'])
            ->name('complaints.index');

        Route::post('/complaints', [ComplaintController::class, 'store'])
            ->name('complaints.store');

        Route::post('/complaints/{complaint}/resolve', [ComplaintController::class, 'resolve'])
            ->name('complaints.resolve');

        Route::get('/feedback', [FeedbackController::class, 'index'])
            ->name('feedback.index');

        Route::post('/feedback', [FeedbackController::class, 'store'])
            ->name('feedback.store');

        Route::get('/promotions', [PromotionController::class, 'index'])
            ->name('promotions.index');

        Route::post('/promotions', [PromotionController::class, 'store'])
            ->name('promotions.store');

        Route::post('/promotions/{promotion}/toggle', [PromotionController::class, 'toggle'])
            ->name('promotions.toggle');

        Route::get('/marketing', [MarketingController::class, 'index'])
            ->name('marketing.index');

        Route::post('/marketing', [MarketingController::class, 'store'])
            ->name('marketing.store');

        Route::post('/marketing/{campaign}/prepare', [MarketingController::class, 'prepare'])
            ->name('marketing.prepare');
    });

    /*
    |--------------------------------------------------------------------------
    | Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('management')->name('management.')->group(function (): void {
        Route::get('/', ManagementDashboardController::class)
            ->name('dashboard');

        Route::get('/budgets', [BudgetController::class, 'index'])
            ->name('budgets.index');

        Route::post('/budgets', [BudgetController::class, 'store'])
            ->name('budgets.store');

        Route::post('/budgets/{budget}/lines', [BudgetController::class, 'line'])
            ->name('budgets.line');

        Route::post('/budgets/{budget}/approve', [BudgetController::class, 'approve'])
            ->name('budgets.approve');

        Route::get('/security', [SecurityController::class, 'index'])
            ->name('security.index');

        Route::get('/backups', [BackupController::class, 'index'])
            ->name('backups.index');

        Route::post('/backups', [BackupController::class, 'store'])
            ->name('backups.store');

        Route::get('/settings', [SettingsController::class, 'index'])
            ->name('settings.index');

        Route::post('/settings', [SettingsController::class, 'store'])
            ->name('settings.store');

        Route::get('/permissions', [PermissionsController::class, 'index'])
            ->name('permissions.index');

        Route::post('/permissions/role', [PermissionsController::class, 'role'])
            ->name('permissions.role');

        Route::get('/health', SystemHealthController::class)
            ->name('health');
    });

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [AdministrationController::class, 'index'])
            ->name('index');

        Route::get('/users', [AdministrationController::class, 'users'])
            ->name('users');

        Route::post('/users', [AdministrationController::class, 'saveUser'])
            ->name('users.store');

        Route::get('/setup', [AdministrationController::class, 'setup'])
            ->name('setup');

        Route::post('/setup/property', [AdministrationController::class, 'saveProperty'])
            ->name('setup.property');

        /*
        |--------------------------------------------------------------------------
        | Existing Administration Configuration
        |--------------------------------------------------------------------------
        */

        Route::get('/configuration', [AdministrationController::class, 'configuration'])
            ->name('configuration');

        Route::post('/taxes', [AdministrationController::class, 'tax'])
            ->name('tax');

        Route::post('/gateways', [AdministrationController::class, 'gateway'])
            ->name('gateway');

        Route::post('/printers', [AdministrationController::class, 'printer'])
            ->name('printer');

        Route::post('/providers', [AdministrationController::class, 'provider'])
            ->name('provider');

        /*
        |--------------------------------------------------------------------------
        | Super Admin System Configuration / Branding
        |--------------------------------------------------------------------------
        |
        | GET routes open configuration tabs.
        | PUT routes save configuration values.
        |
        */

        Route::get('/system-configuration', [SystemConfigurationController::class, 'index'])
            ->name('system-configuration.index');

        Route::get('/system-configuration/general', function () {
            return redirect()->route(
                'admin.system-configuration.index',
                ['tab' => 'general']
            );
        })->name('system-configuration.general.view');

        Route::get('/system-configuration/branding', function () {
            return redirect()->route(
                'admin.system-configuration.index',
                ['tab' => 'branding']
            );
        })->name('system-configuration.branding.view');

        Route::get('/system-configuration/documents', function () {
            return redirect()->route(
                'admin.system-configuration.index',
                ['tab' => 'documents']
            );
        })->name('system-configuration.documents.view');

        Route::put('/system-configuration/general', [SystemConfigurationController::class, 'updateGeneral'])
            ->name('system-configuration.general');

        Route::put('/system-configuration/branding', [SystemConfigurationController::class, 'updateBranding'])
            ->name('system-configuration.branding');

        Route::put('/system-configuration/documents', [SystemConfigurationController::class, 'updateDocuments'])
            ->name('system-configuration.documents');

        /*
        |--------------------------------------------------------------------------
        | Bulk Permission Assignment
        |--------------------------------------------------------------------------
        */

        Route::get('/permissions/bulk', [PermissionBulkController::class, 'index'])
            ->name('permissions.bulk');

        Route::post('/permissions/bulk', [PermissionBulkController::class, 'sync'])
            ->name('permissions.bulk.sync');

        /*
        |--------------------------------------------------------------------------
        | Existing Imports
        |--------------------------------------------------------------------------
        */

        Route::get('/imports', [ImportController::class, 'index'])
            ->name('imports');

        Route::get('/imports/rooms-template', [ImportController::class, 'roomTemplate'])
            ->name('imports.rooms-template');

        Route::get('/imports/rates-template', [ImportController::class, 'rateTemplate'])
            ->name('imports.rates-template');

        Route::post('/imports/rooms', [ImportController::class, 'rooms'])
            ->name('imports.rooms');

        Route::post('/imports/rates', [ImportController::class, 'rates'])
            ->name('imports.rates');

        /*
        |--------------------------------------------------------------------------
        | Extended Bulk CSV Tools
        |--------------------------------------------------------------------------
        */

        Route::get('/bulk/{module}/template', [BulkDataController::class, 'template'])
            ->name('bulk.template');

        Route::get('/bulk/{module}/export', [BulkDataController::class, 'export'])
            ->name('bulk.export');

        Route::post('/bulk/{module}/import', [BulkDataController::class, 'import'])
            ->name('bulk.import');

        /*
        |--------------------------------------------------------------------------
        | Restore
        |--------------------------------------------------------------------------
        */

        Route::get('/restore', [RestoreController::class, 'index'])
            ->name('restore');

        Route::post('/restore', [RestoreController::class, 'store'])
            ->name('restore.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Cashier Shifts
    |--------------------------------------------------------------------------
    */

    Route::get('/cashier-shifts', [CashierShiftController::class, 'index'])
        ->name('cashier-shifts.index');

    Route::post('/cashier-shifts/open', [CashierShiftController::class, 'open'])
        ->name('cashier-shifts.open');

    Route::post('/cashier-shifts/{shift}/close', [CashierShiftController::class, 'close'])
        ->name('cashier-shifts.close');
});

/*
|--------------------------------------------------------------------------
| Public Guest QR Verification
|--------------------------------------------------------------------------
|
| QR scans use a temporary/signed URL. This route remains outside auth so
| reception/security can verify a guest QR from a scanning device.
|
*/

Route::get('/guest/verify/{guest}/{token}', [GuestQrController::class, 'verify'])
    ->middleware('signed')
    ->name('guests.qr.verify');


/*
|--------------------------------------------------------------------------
| Password Reset
|--------------------------------------------------------------------------
*/

if (file_exists(__DIR__.'/password_reset.php')) {
    require __DIR__.'/password_reset.php';
}


/*
|--------------------------------------------------------------------------
| CSV / PDF Data Exports
|--------------------------------------------------------------------------
*/

if (file_exists(__DIR__.'/data_exports.php')) {
    require __DIR__.'/data_exports.php';
}

/*
|--------------------------------------------------------------------------
| Public Hotel Website / Online Booking
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Additional HMS Route Files
|--------------------------------------------------------------------------
*/

if (file_exists(__DIR__.'/managed_entries.php')) {
    require __DIR__.'/managed_entries.php';
}

if (file_exists(__DIR__.'/task_notifications.php')) {
    require __DIR__.'/task_notifications.php';
}

if (file_exists(__DIR__.'/profile_data_tools.php')) {
    require __DIR__.'/profile_data_tools.php';
}

/*
|--------------------------------------------------------------------------
| Public Hotel Website
|--------------------------------------------------------------------------
|
| The public website owns "/" and "/hotel".
| Staff/Admin continue to use "/login", while authenticated staff use
| "/dashboard". Keep this include after all staff/admin routes so the
| public website remains isolated from the internal HMS.
|
*/

if (file_exists(__DIR__.'/public_website.php')) {
    require __DIR__.'/public_website.php';
}

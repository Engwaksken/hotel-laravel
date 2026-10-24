<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| HMS Export Modules
|--------------------------------------------------------------------------
|
| Each module is intentionally allow-listed. The export controller will
| only export columns that really exist in the live database table.
|
| Add further modules here as new pages/tables are introduced.
|
*/

return [

    'rooms' => [
        'title' => 'Rooms',
        'table' => 'rooms',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'room_number' => 'Room Number',
            'name' => 'Room Name',
            'room_type_id' => 'Room Type',
            'floor' => 'Floor',
            'status' => 'Status',
            'created_at' => 'Created',
        ],
    ],

    'room-types' => [
        'title' => 'Room Types',
        'table' => 'room_types',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'name' => 'Name',
            'code' => 'Code',
            'capacity' => 'Capacity',
            'base_rate' => 'Base Rate',
            'status' => 'Status',
            'created_at' => 'Created',
        ],
    ],

    'reservations' => [
        'title' => 'Reservations',
        'table' => 'reservations',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'reservation_number' => 'Reservation No.',
            'booking_reference' => 'Booking Reference',
            'guest_id' => 'Guest',
            'room_id' => 'Room',
            'check_in' => 'Check In',
            'check_out' => 'Check Out',
            'status' => 'Status',
            'total_amount' => 'Total Amount',
            'balance' => 'Balance',
            'created_at' => 'Created',
        ],
    ],

    'guests' => [
        'title' => 'Guests',
        'table' => 'guests',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'nationality' => 'Nationality',
            'document_number' => 'Document Number',
            'created_at' => 'Created',
        ],
    ],

    'inventory' => [
        'title' => 'Inventory',
        'table' => 'inventory_items',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'name' => 'Item',
            'sku' => 'SKU',
            'category_id' => 'Category',
            'unit' => 'Unit',
            'quantity' => 'Quantity',
            'reorder_level' => 'Reorder Level',
            'unit_cost' => 'Unit Cost',
            'status' => 'Status',
            'created_at' => 'Created',
        ],
    ],

    'suppliers' => [
        'title' => 'Suppliers',
        'table' => 'suppliers',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'name' => 'Supplier',
            'contact_person' => 'Contact Person',
            'email' => 'Email',
            'phone' => 'Phone',
            'tin' => 'TIN',
            'address' => 'Address',
            'status' => 'Status',
            'created_at' => 'Created',
        ],
    ],

    'employees' => [
        'title' => 'Employees',
        'table' => 'employees',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'employee_number' => 'Employee No.',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'department' => 'Department',
            'job_title' => 'Job Title',
            'status' => 'Status',
            'created_at' => 'Created',
        ],
    ],

    'corporate-accounts' => [
        'title' => 'Corporate Accounts',
        'table' => 'corporate_accounts',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'name' => 'Company',
            'company_name' => 'Company Name',
            'tin' => 'TIN',
            'email' => 'Email',
            'phone' => 'Phone',
            'credit_limit' => 'Credit Limit',
            'status' => 'Status',
            'created_at' => 'Created',
        ],
    ],

    'events' => [
        'title' => 'Events',
        'table' => 'events',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'name' => 'Event',
            'title' => 'Title',
            'event_date' => 'Event Date',
            'start_at' => 'Starts',
            'end_at' => 'Ends',
            'venue_id' => 'Venue',
            'status' => 'Status',
            'total_amount' => 'Total',
            'created_at' => 'Created',
        ],
    ],

    'users' => [
        'title' => 'Users',
        'table' => 'users',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'role' => 'Role',
            'status' => 'Status',
            'created_at' => 'Created',
        ],
    ],

    'guest-comments' => [
        'title' => 'Guest Comments',
        'table' => 'guest_comments',
        'columns' => [
            'id' => 'ID',
            'property_id' => 'Property',
            'guest_name' => 'Guest',
            'email' => 'Email',
            'rating' => 'Rating',
            'comment' => 'Comment',
            'status' => 'Status',
            'is_featured' => 'Featured',
            'created_at' => 'Created',
        ],
    ],
];

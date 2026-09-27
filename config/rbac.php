<?php

return [
    // Grouped permission labels for the RBAC UI. Keys must stay in sync
    // with config/permissions.php (flat map used by Gates + middleware).
    'groups' => [
        'Main' => [
            'dashboard' => 'View dashboard',
            'pos' => 'POS access',
        ],
        'Catalog & Inventory' => [
            'products' => 'Manage products',
            'stocks' => 'Manage stocks',
        ],
        'Operations' => [
            'sales' => 'View sales',
            'kds' => 'Kitchen display',
            'floors' => 'Manage floors & tables',
            'branches' => 'Manage branches',
            'reservations' => 'Manage reservations',
            'gift-cards' => 'Gift cards',
        ],
        'People' => [
            'customers' => 'Manage customers',
            'employees' => 'Manage employees & roles',
        ],
        'Insights & System' => [
            'reports' => 'View reports',
            'settings' => 'Manage settings',
        ],
    ],

    'job_titles' => [
        'Manager',
        'Cashier',
        'Waiter',
        'Chef',
        'Cleaner',
        'Delivery Man',
    ],

    'default_roles' => [
        'Manager' => ['dashboard', 'pos', 'products', 'stocks', 'sales', 'kds', 'floors', 'branches', 'reservations', 'gift-cards', 'customers', 'employees', 'reports', 'settings'],
        'Cashier' => ['dashboard', 'pos', 'sales', 'customers', 'gift-cards', 'reservations'],
        'Waiter' => ['pos', 'kds', 'floors', 'reservations', 'sales'],
        'Chef' => ['kds', 'dashboard'],
    ],
];

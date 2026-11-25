<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Landing Page
$routes->get('/', 'Home::index');

// Masked addresses (address bar shows /v/<random code>)
$routes->get('v/(:segment)', 'Home::masked/$1');

// Auth Routes
$routes->get('auth', 'Auth::index');
$routes->get('auth', 'Auth::index');
$routes->post('auth/login', 'Auth::login');
$routes->get('auth/register', 'Auth::register');
$routes->post('auth/register', 'Auth::register');
$routes->get('auth/logout', 'Auth::logout');

// Forgot password (email OTP)
$routes->get('auth/forgot', 'PasswordReset::forgot');
$routes->post('auth/forgot', 'PasswordReset::forgot');
$routes->get('auth/reset', 'PasswordReset::reset');
$routes->post('auth/reset', 'PasswordReset::reset');

// Dashboard
$routes->get('dashboard', 'Dashboard::index');

// Items Routes (No Auth Required for Testing)
$routes->get('items', 'Items::index');
$routes->get('items/add', 'Items::add');
$routes->post('items/add', 'Items::add');
$routes->get('items/edit/(:num)', 'Items::edit/$1');
$routes->post('items/edit/(:num)', 'Items::edit/$1');
$routes->get('items/delete/(:num)', 'Items::delete/$1');

// Statistics/Dashboard Route
$routes->get('statistics', 'Statistics::index');

// Admin Routes
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');

    // Businesses (platform/superadmin only — enforced in the controller)
    $routes->get('businesses', 'Admin\Businesses::index');
    $routes->get('businesses/create', 'Admin\Businesses::create');
    $routes->post('businesses/create', 'Admin\Businesses::create');
    $routes->get('businesses/edit/(:num)', 'Admin\Businesses::edit/$1');
    $routes->post('businesses/edit/(:num)', 'Admin\Businesses::edit/$1');
    $routes->get('businesses/toggle/(:num)', 'Admin\Businesses::toggle/$1');
    $routes->post('businesses/delete/(:num)', 'Admin\Businesses::delete/$1');
    $routes->get('businesses/switch/(:num)', 'Admin\Businesses::switchTo/$1');
    $routes->get('businesses/switch-back', 'Admin\Businesses::switchBack');

    // Items Management
    $routes->get('items', 'Admin\Items::index');
    $routes->get('items/create', 'Admin\Items::create');
    $routes->post('items/create', 'Admin\Items::create');
    $routes->get('items/edit/(:num)', 'Admin\Items::edit/$1');
    $routes->post('items/edit/(:num)', 'Admin\Items::edit/$1');
    $routes->get('items/delete/(:num)', 'Admin\Items::delete/$1');
    $routes->post('items/bulk-delete', 'Admin\Items::bulkDelete');
    $routes->get('items/generate-code', 'Admin\Items::generateCode');
    $routes->post('items/update-category', 'Admin\Items::updateCategory');
    $routes->post('items/update-discount', 'Admin\Items::updateDiscount');
    $routes->get('items/import', 'Admin\Items::import');
    $routes->post('items/import', 'Admin\Items::import');
    $routes->get('items/import-template', 'Admin\Items::importTemplate');
    
    // Users Management
    $routes->get('users', 'Admin\Users::index');
    $routes->get('users/create', 'Admin\Users::create');
    $routes->post('users/create', 'Admin\Users::create');
    $routes->get('users/edit/(:num)', 'Admin\Users::edit/$1');
    $routes->post('users/edit/(:num)', 'Admin\Users::edit/$1');
    $routes->get('users/delete/(:num)', 'Admin\Users::delete/$1');
    
    // Categories Management
    $routes->get('categories', 'Admin\Categories::index');
    $routes->get('categories/create', 'Admin\Categories::create');
    $routes->post('categories/create', 'Admin\Categories::create');
    $routes->get('categories/edit/(:num)', 'Admin\Categories::edit/$1');
    $routes->post('categories/edit/(:num)', 'Admin\Categories::edit/$1');
    $routes->get('categories/delete/(:num)', 'Admin\Categories::delete/$1');
    
    // Barcode Generator
    $routes->get('barcodes', 'Admin\Barcodes::index');
    $routes->post('barcodes/generate', 'Admin\Barcodes::generate');

    // Payment Methods Management
    $routes->get('payment-methods', 'Admin\PaymentMethods::index');
    $routes->get('payment-methods/create', 'Admin\PaymentMethods::create');
    $routes->post('payment-methods/create', 'Admin\PaymentMethods::create');
    $routes->get('payment-methods/edit/(:num)', 'Admin\PaymentMethods::edit/$1');
    $routes->post('payment-methods/edit/(:num)', 'Admin\PaymentMethods::edit/$1');
    $routes->get('payment-methods/delete/(:num)', 'Admin\PaymentMethods::delete/$1');

    // Settings
    $routes->get('settings', 'Admin\Settings::index');
    $routes->post('settings/save', 'Admin\Settings::save');
    $routes->post('settings/account', 'Admin\Settings::updateAccount');

    // BIR Compliance
    $routes->get('birCompliance', 'Admin\BIRCompliance::index');
    $routes->get('birCompliance/exportCSV', 'Admin\BIRCompliance::exportCSV');
    $routes->get('birCompliance/exportPDF', 'Admin\BIRCompliance::exportPDF');

    // Suppliers
    $routes->get('suppliers', 'Admin\Suppliers::index');
    $routes->get('suppliers/create', 'Admin\Suppliers::create');
    $routes->post('suppliers/create', 'Admin\Suppliers::create');
    $routes->get('suppliers/edit/(:num)', 'Admin\Suppliers::edit/$1');
    $routes->post('suppliers/edit/(:num)', 'Admin\Suppliers::edit/$1');
    $routes->get('suppliers/delete/(:num)', 'Admin\Suppliers::delete/$1');

    // Purchase Orders
    $routes->get('purchase-orders', 'Admin\PurchaseOrders::index');
    $routes->get('purchase-orders/create', 'Admin\PurchaseOrders::create');
    $routes->post('purchase-orders/create', 'Admin\PurchaseOrders::create');
    $routes->get('purchase-orders/view/(:num)', 'Admin\PurchaseOrders::view/$1');
    $routes->get('purchase-orders/approve/(:num)', 'Admin\PurchaseOrders::approve/$1');
    $routes->get('purchase-orders/receive/(:num)', 'Admin\PurchaseOrders::receive/$1');
    $routes->get('purchase-orders/cancel/(:num)', 'Admin\PurchaseOrders::cancel/$1');
    $routes->post('purchase-orders/delete/(:num)', 'Admin\PurchaseOrders::delete/$1');

    // Expenses
    $routes->get('expenses', 'Admin\Expenses::index');
    $routes->get('expenses/create', 'Admin\Expenses::create');
    $routes->post('expenses/create', 'Admin\Expenses::create');
    $routes->get('expenses/edit/(:num)', 'Admin\Expenses::edit/$1');
    $routes->post('expenses/edit/(:num)', 'Admin\Expenses::edit/$1');
    $routes->get('expenses/delete/(:num)', 'Admin\Expenses::delete/$1');

    // Book of Accounts & Quarterly Reports
    $routes->get('books', 'Admin\BookOfAccounts::index');
    $routes->get('books/quarterly', 'Admin\BookOfAccounts::quarterly');
    $routes->get('books/export', 'Admin\BookOfAccounts::export');

    // Stock Ledger
    $routes->get('stock-movements', 'Admin\StockMovements::index');
    $routes->get('stock-movements/item/(:num)', 'Admin\StockMovements::item/$1');
    $routes->post('stock-movements/adjust', 'Admin\StockMovements::adjust');

    // Audit Log
    $routes->get('audit-log', 'Admin\AuditLog::index');
});

// Cashier Routes
$routes->group('cashier', ['filter' => 'auth'], function ($routes) {
    $routes->get('sales', 'Cashier\Sales::index');
    $routes->post('sales/process', 'Cashier\Sales::processSale');
    $routes->get('sales/receipt/(:num)', 'Cashier\Sales::receipt/$1');
    $routes->get('sales/history', 'Cashier\Sales::getSaleHistory');
});

// BIR Compliance Monitoring - requires login (previously public, exposed
// full sales history and user emails to anyone with no authentication)
$routes->group('bir', ['filter' => 'auth'], function ($routes) {
    $routes->get('', 'BIR::index');
    $routes->get('monitoring', 'BIR::index');
    $routes->get('exportCSV', 'BIR::exportCSV');
});

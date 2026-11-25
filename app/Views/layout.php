<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - Gmack' : (session()->get('business_name') ? session()->get('business_name') . ' · Gmack' : 'Gmack') ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='32' height='32'%3E%3Crect width='24' height='24' rx='5' fill='%231c2126'/%3E%3Cpath d='M4 15c0-5 3.6-9 8-9s8 4 8 9' fill='none' stroke='%23ff6b00' stroke-width='2' stroke-linecap='round'/%3E%3Cpath d='M12 6v2.6' stroke='%23ff6b00' stroke-width='2' stroke-linecap='round'/%3E%3Crect x='2' y='15' width='20' height='3.2' rx='1.6' fill='%23ffc400'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            /* Hardware & Construction industrial palette */
            --steel-900: #14181c;
            --steel-800: #1c2126;
            --steel-700: #262d34;
            --steel-600: #3a4550;
            --safety-orange: #ff6b00;
            --safety-orange-dark: #d95700;
            --caution-yellow: #ffc400;
            --blueprint-blue: #1d4ed8;
            --blueprint-blue-dark: #1638a3;
            --concrete-50: #f4f5f7;
            --concrete-100: #eceef1;
            --concrete-300: #cbd2d9;
            --concrete-600: #5b6672;

            --gradient-brand: linear-gradient(135deg, var(--safety-orange) 0%, var(--caution-yellow) 100%);
            --gradient-steel: linear-gradient(180deg, var(--steel-800) 0%, var(--steel-900) 100%);

            /* kept for any legacy references */
            --primary: var(--safety-orange);
            --primary-dark: var(--safety-orange-dark);
            --primary-light: var(--caution-yellow);
            --secondary: var(--concrete-50);
            --dark: #20262c;
            --light: var(--concrete-50);

            --navbar-h: 64px;
            --sidebar-w: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--light);
            background-image:
                linear-gradient(rgba(29, 78, 216, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(29, 78, 216, 0.035) 1px, transparent 1px);
            background-size: 34px 34px;
            font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--dark);
        }

        h1, h2, h3, h4, h5, .brand-mark-text, .stat-value {
            font-family: 'Barlow Condensed', 'Segoe UI', sans-serif;
            letter-spacing: 0.2px;
        }

        /* ---- Brand wordmark ---- */
        .brand-mark { display: inline-flex; align-items: center; gap: 8px; line-height: 1; color: inherit; }
        .brand-mark-icon { color: var(--safety-orange); flex-shrink: 0; }
        .brand-mark-text { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }
        .brand-mark-primary { color: var(--steel-800); }
        .brand-mark-accent { color: var(--safety-orange); }
        .brand-mark-link { text-decoration: none; }
        /* Gmack wordmark: brushed-chrome metal with a tool-steel edge */
        .brand-mark-metal {
            font-family: 'Barlow Condensed', 'Impact', sans-serif;
            font-weight: 800;
            font-size: 1.25em;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            background: linear-gradient(180deg, #ffffff 0%, #d7dde4 30%, #7b8794 50%, #e9edf2 62%, #a7b1bc 88%, #5b6672 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            filter: drop-shadow(0 1px 0 #14181c) drop-shadow(0 2px 3px rgba(20, 24, 28, 0.45));
        }
        .brand-mark-tagline { display: block; font-size: 0.65rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: var(--concrete-600); margin-top: 2px; }

        /* ---- Impersonation banner ---- */
        .impersonation-banner {
            background: var(--caution-yellow);
            color: var(--steel-900);
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            padding: 8px 12px;
            letter-spacing: 0.3px;
        }
        .impersonation-banner a { color: var(--steel-900); text-decoration: underline; margin-left: 10px; }

        /* ---- Navbar ---- */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 16px rgba(20, 24, 28, 0.08);
            padding: 0 1.5rem;
            min-height: var(--navbar-h);
            border-bottom: 3px solid transparent;
            border-image: var(--gradient-brand) 1;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar .container-fluid { min-height: calc(var(--navbar-h) - 3px); }
        .navbar-brand { display: flex; align-items: center; padding: 0; margin: 0; }

        .business-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--concrete-100);
            color: var(--steel-800);
            font-weight: 600;
            font-size: 12.5px;
            padding: 5px 12px;
            border-radius: 999px;
            margin-left: 14px;
            border: 1px solid var(--concrete-300);
        }
        .business-pill i { color: var(--safety-orange); }

        .navbar-text { color: var(--dark) !important; opacity: 0.85; font-size: 13px; font-weight: 500; transition: all 0.3s ease; }
        .navbar-text:hover { color: var(--safety-orange) !important; opacity: 1; }

        .navbar-nav .nav-link {
            color: var(--dark) !important;
            opacity: 0.75;
            font-weight: 500;
            margin-left: 10px;
            padding: 8px 12px !important;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .navbar-nav .nav-link:hover { background: var(--gradient-brand); color: white !important; opacity: 1; }

        .sidebar-toggle { color: var(--dark) !important; }
        .navbar-toggler { border-color: rgba(38, 45, 52, 0.3) !important; }
        .navbar-toggler:focus { box-shadow: 0 0 0 0.25rem rgba(255, 107, 0, 0.25) !important; }
        .navbar-toggler-icon { filter: invert(0.15) sepia(1) saturate(6) hue-rotate(-5deg); }

        /* ---- Sidebar ---- */
        .sidebar {
            background: var(--gradient-steel);
            color: white;
            height: calc(100vh - var(--navbar-h));
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: var(--navbar-h);
            width: var(--sidebar-w);
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.25);
            z-index: 1020;
        }

        .sidebar .nav-link {
            position: relative;
            color: rgba(255, 255, 255, 0.78);
            padding: 14px 24px;
            margin: 4px 0;
            transition: all 0.25s ease;
            font-size: 15px;
            font-weight: 500;
        }
        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: var(--gradient-brand);
            transform: scaleY(0);
            transition: transform 0.25s ease;
        }
        .sidebar .nav-link:hover { color: white; background-color: rgba(255, 107, 0, 0.14); }
        .sidebar .nav-link:hover::before,
        .sidebar .nav-link.active::before { transform: scaleY(1); }
        .sidebar .nav-link.active { background: rgba(255, 107, 0, 0.16); color: white; }

        .main-content {
            margin-left: var(--sidebar-w);
            padding: 20px 24px;
            min-height: calc(100vh - var(--navbar-h));
            animation: fadeSlideUp 0.45s ease both;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ---- Cards ---- */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(20, 24, 28, 0.08);
            overflow: hidden;
            margin-bottom: 20px;
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }
        .card:hover { box-shadow: 0 8px 22px rgba(20, 24, 28, 0.12); }

        .card-header {
            background: linear-gradient(135deg, var(--steel-800) 0%, var(--steel-900) 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 16px 20px;
        }
        /* Pages that explicitly opt into a plain Bootstrap header via
           "card-header bg-light" were still getting the dark steel gradient
           above (equal specificity, and this rule wins), turning the
           section title white-on-dark and its .text-muted caption
           (gray, meant for a light background) nearly invisible. Restore
           what bg-light actually asked for. */
        .card-header.bg-light {
            background: #f8f9fa !important;
            color: var(--dark) !important;
        }
        .card-header.bg-light h1, .card-header.bg-light h2, .card-header.bg-light h3,
        .card-header.bg-light h4, .card-header.bg-light h5, .card-header.bg-light h6 {
            color: var(--dark) !important;
        }
        .card-body { padding: 14px; }
        .card-footer { background-color: #f8f9fa; border-top: 1px solid #dee2e6; padding: 15px 20px; }

        .stat-card { border-left: 4px solid var(--safety-orange); }
        .stat-label { font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--concrete-600); }
        .stat-value { font-size: 2.4rem; font-weight: 800; color: var(--steel-800); line-height: 1.15; }

        .sticky-top { top: calc(var(--navbar-h) + 16px) !important; }

        /* ---- Buttons ---- */
        .btn-primary {
            background: var(--gradient-brand);
            border: none;
            color: var(--steel-900);
            padding: 10px 20px;
            font-weight: 700;
            transition: all 0.25s ease;
        }
        .btn-primary:hover, .btn-primary:focus {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 107, 0, 0.35);
            background: var(--gradient-brand);
            color: var(--steel-900);
        }
        .btn-secondary { background-color: var(--steel-700); border: none; font-weight: 500; transition: all 0.3s ease; }
        .btn-secondary:hover { transform: translateY(-2px); background-color: var(--steel-800); }

        /* ---- Badges / tables / forms ---- */
        .badge { padding: 6px 12px; font-size: 12px; }

        .table { color: var(--dark); margin-bottom: 0; width: 100%; overflow-x: auto; }
        .table-responsive { overflow-x: auto; }
        .table thead th { background-color: var(--concrete-50); border-bottom: 2px solid var(--concrete-300); font-weight: 700; color: var(--steel-800); white-space: nowrap; text-transform: uppercase; font-size: 12px; letter-spacing: 0.4px; }
        .table tbody tr { border-bottom: 1px solid var(--concrete-100); transition: background-color 0.2s; }
        .table tbody tr:hover { background-color: #fff7ef; }
        .table td, .table th { vertical-align: middle; padding: 12px; }

        .form-control, .form-select { border: 1px solid var(--concrete-300); border-radius: 6px; padding: 10px 14px; transition: all 0.3s; }
        .form-control:focus, .form-select:focus { border-color: var(--safety-orange); box-shadow: 0 0 0 0.2rem rgba(255, 107, 0, 0.18); }

        .alert { border: none; border-radius: 8px; }
        .alert-danger { background-color: #fde2d8; color: #9a2f0a; }
        .alert-success { background-color: #dff3e3; color: #17663a; }

        h1, h2, h3, h4, h5, h6 { color: var(--dark); font-weight: 700; }
        h1 { border-bottom: 3px solid var(--safety-orange); padding-bottom: 12px; margin-bottom: 24px; }

        /* .card-header sets a dark steel background + white text, but the
           bare h1-h6 rule above matches its heading directly and wins over
           inherited color — so every card title (e.g. "Inventory Status")
           was rendering near-black on a near-black background. */
        .card-header h1, .card-header h2, .card-header h3,
        .card-header h4, .card-header h5, .card-header h6 { color: white; }

        /* ---- Responsive ---- */
        @media (max-width: 991px) {
            .sidebar { position: fixed; left: -100%; width: var(--sidebar-w); max-width: 85vw; top: var(--navbar-h); height: calc(100vh - var(--navbar-h)); transition: left 0.3s ease; z-index: 1050; box-shadow: 4px 0 24px rgba(0, 0, 0, 0.35); }
            .sidebar.show { left: 0; }
            .sidebar-overlay { display: none; position: fixed; top: var(--navbar-h); left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1040; }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0 !important; padding: 20px 15px; width: 100%; }
            .sidebar-toggle { display: inline-flex !important; }
            .container-fluid { padding-left: 10px; padding-right: 10px; }
            .card { margin-bottom: 15px; }
            h1, h2 { font-size: 1.4rem; margin-bottom: 10px; }
            .table-responsive-custom { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .business-pill { display: none; }
        }

        @media (min-width: 992px) {
            .sidebar-toggle { display: none !important; }
            .sidebar-overlay { display: none !important; }
        }

        @media (max-width: 576px) {
            :root { --navbar-h: 52px; }
            .navbar { padding: 0 0.75rem; }
            .navbar-text { font-size: 11px; }
            .main-content { padding: 10px 8px; }
            .card-header { padding: 10px 12px; }
            .card-body { padding: 10px; }
            .form-control, .form-select { padding: 8px 10px; font-size: 14px; }
            .btn { padding: 7px 12px; font-size: 13px; }
            .btn-sm { padding: 4px 8px; font-size: 11px; }
            .table { font-size: 12px; }
            .table thead th { padding: 8px 4px; font-size: 11px; }
            .table tbody td { padding: 8px 4px; }
            .badge { padding: 4px 8px; font-size: 10px; }
            h1, h2 { font-size: 1.2rem; }
            .row.mb-4 { margin-bottom: 0.75rem !important; }
        }

        /* ---- DataTables retint ---- */
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--concrete-300);
            border-radius: 6px;
            padding: 6px 10px;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            outline: none;
            border-color: var(--safety-orange);
            box-shadow: 0 0 0 0.15rem rgba(255, 107, 0, 0.18);
        }
        .dataTables_wrapper .page-item.active .page-link,
        .dt-paging .dt-paging-button.current {
            background: var(--gradient-brand) !important;
            border-color: transparent !important;
            color: var(--steel-900) !important;
            font-weight: 700;
        }
        .dataTables_wrapper .dt-paging .dt-paging-button {
            border-radius: 6px !important;
            margin: 0 2px;
        }
        table.dataTable thead th { position: relative; }

        /* ---- Bootstrap pagination retint (server-side paged lists, e.g. Items) ---- */
        .pagination .page-link {
            color: var(--steel-800);
            border-color: var(--concrete-300);
        }
        .pagination .page-link:hover,
        .pagination .page-link:focus {
            color: var(--safety-orange-dark);
            background-color: var(--concrete-50);
            box-shadow: none;
        }
        .pagination .page-item.active .page-link {
            background: var(--gradient-brand);
            border-color: transparent;
            color: var(--steel-900);
            font-weight: 700;
        }
        .pagination .page-item.disabled .page-link { color: var(--concrete-600); }
    </style>
</head>
<body>
    <?php if (session()->get('impersonating')): ?>
    <div class="impersonation-banner">
        <i class="bi bi-eye"></i> Viewing as <strong><?= esc(session()->get('business_name')) ?></strong> (support access)
        <a href="<?= base_url('admin/businesses/switch-back') ?>">Exit to Businesses <i class="bi bi-box-arrow-right"></i></a>
    </div>
    <?php endif; ?>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <?php if (in_array(session()->get('role'), ['admin', 'superadmin'], true)): ?>
            <button class="btn btn-link sidebar-toggle me-2 p-1" onclick="toggleSidebar()" style="font-size: 22px; text-decoration: none;">
                <i class="bi bi-list"></i>
            </button>
            <?php endif; ?>
            <a class="navbar-brand" href="<?= base_url('/') ?>">
                <?= view('components/logo', ['size' => 'sm', 'link' => false]) ?>
            </a>
            <?php if (session()->get('business_name')): ?>
                <span class="business-pill"><i class="bi bi-buildings"></i> <?= esc(session()->get('business_name')) ?></span>
            <?php endif; ?>
            <!-- Per-page navbar extras (e.g. cashier's alarm control) render here -->
            <div class="navbar-extra d-flex align-items-center ms-2"><?= $this->renderSection('navbar_extra') ?></div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <a href="<?= base_url('admin/users') ?>" class="navbar-text ms-auto me-4 text-decoration-none" style="display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-person-circle"></i> <?= session()->get('username') ?> <span style="opacity: 0.7;">(<?= strtoupper(session()->get('role') ?? 'guest') ?>)</span>
                </a>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('auth/logout') ?>" title="Logout">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <?php if (session()->get('role') === 'superadmin'): ?>
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <nav class="sidebar" id="sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/businesses') ?>"><i class="bi bi-buildings"></i> Businesses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/audit-log') ?>"><i class="bi bi-journal-text"></i> Audit Log</a>
                </li>
            </ul>
        </nav>
        <?php elseif (session()->get('role') === 'admin'): ?>
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/items') ?>"><i class="bi bi-box"></i> Items</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/categories') ?>"><i class="bi bi-tags"></i> Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/suppliers') ?>"><i class="bi bi-truck"></i> Suppliers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/purchase-orders') ?>"><i class="bi bi-clipboard-check"></i> Purchase Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/expenses') ?>"><i class="bi bi-cash-stack"></i> Expenses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/books') ?>"><i class="bi bi-journal-bookmark"></i> Book of Accounts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/stock-movements') ?>"><i class="bi bi-arrow-left-right"></i> Stock Ledger</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/users') ?>"><i class="bi bi-people"></i> Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/barcodes') ?>"><i class="bi bi-upc-scan"></i> Barcodes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/payment-methods') ?>"><i class="bi bi-credit-card"></i> Payment Methods</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/audit-log') ?>"><i class="bi bi-journal-text"></i> Audit Log</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/settings') ?>"><i class="bi bi-gear"></i> Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('cashier/sales') ?>"><i class="bi bi-cash-coin"></i> Cashier</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('statistics') ?>"><i class="bi bi-graph-up"></i> Statistics</a>
                </li>
                <?php if (session()->get('impersonating')): ?>
                <li class="nav-item mt-3">
                    <a class="nav-link" href="<?= base_url('admin/businesses/switch-back') ?>"><i class="bi bi-box-arrow-right"></i> Exit to Businesses</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="<?= in_array(session()->get('role'), ['admin', 'superadmin'], true) ? 'main-content' : '' ?> container-fluid">
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Auto-init any table marked up for DataTables (search + sort + pagination).
    // Tables render NO rows at all when their list is empty (no colspan
    // placeholder <tr> — see the "empty" state below) so DataTables never
    // tries to read data out of a merged cell; its own emptyTable message
    // covers the "no rows" case instead, customizable per table via
    // data-empty="...".
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && jQuery.fn.DataTable) {
            jQuery('.data-table').each(function () {
                jQuery(this).DataTable({
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search…',
                        emptyTable: this.dataset.empty || 'No records found',
                    },
                });
            });
        }
    });

    // Styled confirm dialog for destructive links, replacing the browser's native confirm().
    // Usage: <a href="..." onclick="return confirmDelete(event, 'Item name');">Delete</a>
    function confirmDelete(event, label, opts) {
        event.preventDefault();
        var url = event.currentTarget.href;
        var settings = Object.assign({
            title: 'Delete ' + (label || 'this') + '?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            confirmButtonColor: '#ff6b00',
            cancelButtonColor: '#5b6672',
        }, opts || {});

        if (window.Swal) {
            Swal.fire(settings).then(function (result) {
                if (result.isConfirmed) window.location.href = url;
            });
        } else if (window.confirm(settings.title)) {
            window.location.href = url;
        }
        return false;
    }
    </script>
    <script>
    function toggleSidebar() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        if (sidebar) {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        }
    }
    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
        }
    });

    // Mark the current page's sidebar link active
    (function () {
        var links = document.querySelectorAll('.sidebar .nav-link');
        var path = window.location.pathname.replace(/\/+$/, '');
        links.forEach(function (link) {
            var linkPath = link.pathname.replace(/\/+$/, '');
            if (linkPath && (path === linkPath || path.startsWith(linkPath + '/'))) {
                link.classList.add('active');
            }
        });
    })();

    // Count-up animation for stat numbers ([data-count] holds the target value)
    (function () {
        document.querySelectorAll('[data-count]').forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-count')) || 0;
            var duration = 800;
            var start = null;
            function step(ts) {
                if (!start) start = ts;
                var progress = Math.min((ts - start) / duration, 1);
                var value = target * (1 - Math.pow(1 - progress, 3));
                el.textContent = target % 1 === 0 ? Math.round(value).toLocaleString() : value.toFixed(2);
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    })();
    </script>
    <?= $this->renderSection('scripts') ?>
<script>
// Show/hide ("eye") button for every password box on admin pages.
document.querySelectorAll('input[type="password"]').forEach(function (input) {
    if (input.dataset.noEye !== undefined || input.closest('.password-wrapper, .input-group')) return;
    var group = document.createElement('div');
    group.className = 'input-group';
    input.parentNode.insertBefore(group, input);
    group.appendChild(input);
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-secondary';
    btn.setAttribute('aria-label', 'Show password');
    btn.title = 'Show password';
    btn.innerHTML = '<i class="bi bi-eye"></i>';
    btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
        btn.title = show ? 'Hide password' : 'Show password';
        btn.setAttribute('aria-label', btn.title);
    });
    group.appendChild(btn);
});
</script>
<?= view('components/url_mask') ?>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIR Compliance Monitoring - <?= esc($pos_system['name'] ?? 'Gmack') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #ff6b00 0%, #1c2126 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
            color: white !important;
        }
        .bir-header {
            background: linear-gradient(135deg, #d4410e 0%, #a83309 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .bir-header h1 {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .bir-header .badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
        }
        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
            border-left: 5px solid #d4410e;
        }
        .metric-card h6 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .metric-card h3 {
            color: #d4410e;
            font-weight: bold;
        }
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .table-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead {
            background-color: #f8f9fa;
            border-top: 2px solid #d4410e;
            border-bottom: 2px solid #d4410e;
        }
        .table thead th {
            color: #333;
            font-weight: 600;
            border-color: #dee2e6;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .badge-primary {
            background-color: #007bff;
        }
        footer {
            background-color: #333;
            color: white;
            padding: 2rem;
            text-align: center;
            margin-top: 2rem;
        }
        .export-btn {
            background-color: #d4410e;
            border-color: #d4410e;
            color: white;
            font-weight: 600;
        }
        .export-btn:hover {
            background-color: #a83309;
            border-color: #a83309;
            color: white;
        }
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .security-check {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
        }
        .security-check .check-mark {
            color: #28a745;
            font-weight: bold;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/') ?>">📊 <?= esc($pos_system['name'] ?? 'Gmack') ?></a>
            <span class="navbar-text text-white ms-auto">
                BIR Compliance Monitoring System
            </span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid py-5">
        <!-- Header -->
        <div class="bir-header">
            <h1>🔍 POS System BIR Compliance Report</h1>
            <p class="mb-2">Bureau of Internal Revenue - Real-time Monitoring Dashboard</p>
            <div>
                <span class="badge bg-success">✓ System Active</span>
                <span class="badge bg-info">Last Updated: <?= date('M d, Y H:i:s') ?></span>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3">📅 Generate Report by Date Range</h5>
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date:</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="<?= esc($date_from) ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date:</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="<?= esc($date_to) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Generate Report
                    </button>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <a href="<?= site_url('bir/exportCSV?date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to)) ?>" 
                       class="btn export-btn w-100">
                        <i class="bi bi-download"></i> Download CSV
                    </a>
                </div>
            </form>
        </div>

        <!-- System Information -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="info-card">
                    <h5 class="mb-3">🏢 POS System Information</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>System Name:</strong></td>
                            <td><?= esc($pos_system['name']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>System Version:</strong></td>
                            <td><?= esc($pos_system['version']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>System Type:</strong></td>
                            <td><?= esc($pos_system['type']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Installation Date:</strong></td>
                            <td><?= esc($pos_system['installation_date']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Last System Update:</strong></td>
                            <td><?= esc($pos_system['last_modified']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="info-card">
                    <h5 class="mb-3">🔒 System Security Features</h5>
                    <div class="security-check"><span class="check-mark">✓</span> User Authentication & Authorization</div>
                    <div class="security-check"><span class="check-mark">✓</span> Role-Based Access Control (Admin, Cashier)</div>
                    <div class="security-check"><span class="check-mark">✓</span> Secure Password Storage (Hashing)</div>
                    <div class="security-check"><span class="check-mark">✓</span> Session Management & Timeout</div>
                    <div class="security-check"><span class="check-mark">✓</span> CSRF Token Protection</div>
                    <div class="security-check"><span class="check-mark">✓</span> Complete Transaction Logging</div>
                    <div class="security-check"><span class="check-mark">✓</span> Database Encryption</div>
                    <div class="security-check"><span class="check-mark">✓</span> Audit Trail & Compliance</div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-lg-3">
                <div class="metric-card">
                    <h6>Total Transactions</h6>
                    <h3><?= number_format($total_sales) ?></h3>
                    <small class="text-muted">Transactions from <?= esc($date_from) ?> to <?= esc($date_to) ?></small>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="metric-card">
                    <h6>Total Sales Amount</h6>
                    <h3>₱<?= number_format($total_amount, 2) ?></h3>
                    <small class="text-muted">Gross revenue</small>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="metric-card">
                    <h6>Total Discounts Given</h6>
                    <h3>₱<?= number_format($total_discount, 2) ?></h3>
                    <small class="text-muted">Approved discount amount</small>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="metric-card">
                    <h6>Net Sales Amount</h6>
                    <h3>₱<?= number_format($total_net_amount, 2) ?></h3>
                    <small class="text-muted">After discounts</small>
                </div>
            </div>
        </div>

        <!-- Sales by Payment Method -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="table-section">
                    <h5 class="mb-3">💳 Sales by Payment Method</h5>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th class="text-end">Transaction Count</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_by_method as $method => $data): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?= $method === 'cash' ? 'success' : 'info' ?>">
                                        <?= esc(ucfirst($method)) ?>
                                    </span>
                                </td>
                                <td class="text-end"><strong><?= number_format($data['count']) ?></strong></td>
                                <td class="text-end">₱<?= number_format($data['total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-active">
                                <td><strong>TOTAL</strong></td>
                                <td class="text-end"><strong><?= number_format($total_sales) ?></strong></td>
                                <td class="text-end"><strong>₱<?= number_format($total_net_amount, 2) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sales by Cashier -->
            <div class="col-lg-6">
                <div class="table-section">
                    <h5 class="mb-3">👨‍💼 Sales by Cashier</h5>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cashier Name</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_by_cashier as $cashier => $data): ?>
                            <tr>
                                <td><strong><?= esc($cashier) ?></strong></td>
                                <td class="text-end"><?= number_format($data['count']) ?></td>
                                <td class="text-end">₱<?= number_format($data['total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Registered Users -->
        <div class="table-section mb-4">
            <h5 class="mb-3">👥 Registered System Users</h5>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registered_users as $user): ?>
                    <tr>
                        <td><code><?= esc($user['id']) ?></code></td>
                        <td><?= esc($user['username']) ?></td>
                        <td><?= esc($user['email']) ?></td>
                        <td><?= esc($user['name'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'warning' ?>">
                                <?= esc(ucfirst($user['role'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= esc(ucfirst($user['status'])) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Complete Transaction Log -->
        <div class="table-section mb-4">
            <h5 class="mb-3">📝 Complete Transaction Log (<?= number_format($total_sales) ?> Records)</h5>
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Reference No</th>
                        <th>Date & Time</th>
                        <th>Cashier</th>
                        <th>Total</th>
                        <th>Discount</th>
                        <th>Net Amount</th>
                        <th>Payment</th>
                        <th>Amount Paid</th>
                        <th>Change</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trans): ?>
                    <?php $cashier = current(array_filter($registered_users, function($u) use($trans) { return $u['id'] === $trans['cashier_id']; })) ?: ['username' => 'Unknown']; ?>
                    <tr>
                        <td><code><?= esc($trans['reference_no']) ?></code></td>
                        <td><?= date('M d, Y H:i', strtotime($trans['created_at'])) ?></td>
                        <td><?= esc($cashier['username']) ?></td>
                        <td>₱<?= number_format($trans['total_amount'], 2) ?></td>
                        <td>₱<?= number_format($trans['discount'], 2) ?></td>
                        <td><strong>₱<?= number_format($trans['net_amount'], 2) ?></strong></td>
                        <td><?= esc(ucfirst($trans['payment_method'])) ?></td>
                        <td>₱<?= number_format($trans['amount_received'] ?? 0, 2) ?></td>
                        <td>₱<?= number_format($trans['change_amount'] ?? 0, 2) ?></td>
                        <td>
                            <span class="status-badge badge-<?= $trans['status'] === 'completed' ? 'success' : 'warning' ?>">
                                <?= esc(ucfirst($trans['status'])) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($transactions)): ?>
            <div class="alert alert-info">
                No transactions found for the selected date range.
            </div>
            <?php endif; ?>
        </div>

        <!-- Important Notice -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-warning border-3" style="border-left: 5px solid #d4410e;">
                    <h5>📌 Important Notice</h5>
                    <p class="mb-0">This is an official BIR compliance monitoring dashboard. All records shown are automatically generated from the POS system database and represent actual transaction data. For official submissions to BIR, please ensure proper authorization and signatures from management.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p class="mb-0">&copy; <?= date('Y') ?> <?= esc($pos_system['name'] ?? 'Gmack') ?> | BIR Compliance Monitoring System v1.0.0</p>
        <small>Generated: <?= date('F d, Y H:i:s') ?></small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

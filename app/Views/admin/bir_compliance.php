<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h3>📋 BIR Compliance Report</h3>
                    <small>Bureau of Internal Revenue - POS System Monitoring</small>
                </div>
            </div>
        </div>
    </div>

    <!-- POS System Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>POS System Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>System Name:</strong></td>
                            <td><?= esc($pos_system['name']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Version:</strong></td>
                            <td><?= esc($pos_system['version']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Type:</strong></td>
                            <td><?= esc($pos_system['type']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>URL:</strong></td>
                            <td><?= esc($pos_system['location']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Installation Date:</strong></td>
                            <td><?= esc($pos_system['installation_date']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Last Modified:</strong></td>
                            <td><?= esc($pos_system['last_modified']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5>System Security Features</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li>✅ User Authentication & Authorization</li>
                        <li>✅ Role-Based Access Control (Admin, Cashier)</li>
                        <li>✅ Password Hashing & Security</li>
                        <li>✅ Session Management</li>
                        <li>✅ CSRF Token Protection</li>
                        <li>✅ Transaction Logging</li>
                        <li>✅ Database Integrity</li>
                        <li>✅ System Audit Trail</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Filter Report by Date Range</h5>
                </div>
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From:</label>
                            <input type="date" id="date_from" name="date_from" class="form-control" value="<?= esc($date_from) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To:</label>
                            <input type="date" id="date_to" name="date_to" class="form-control" value="<?= esc($date_to) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Filter Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Transactions</h6>
                    <h3 class="text-primary"><?= number_format($total_sales) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Amount</h6>
                    <h3 class="text-success">₱<?= number_format($total_amount, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Discounts</h6>
                    <h3 class="text-danger">₱<?= number_format($total_discount, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Net Amount</h6>
                    <h3 class="text-info">₱<?= number_format($total_net_amount, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales by Payment Method -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>Sales by Payment Method</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Count</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_by_method as $method => $data): ?>
                            <tr>
                                <td><strong><?= esc(ucfirst($method)) ?></strong></td>
                                <td><?= number_format($data['count']) ?></td>
                                <td>₱<?= number_format($data['total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sales by Cashier -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>Sales by Cashier</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Cashier</th>
                                <th>Transactions</th>
                                <th>Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_by_cashier as $cashier => $data): ?>
                            <tr>
                                <td><strong><?= esc($cashier) ?></strong></td>
                                <td><?= number_format($data['count']) ?></td>
                                <td>₱<?= number_format($data['total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Registered Users -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5>Registered System Users</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registered_users as $user): ?>
                            <tr>
                                <td><?= esc($user['id']) ?></td>
                                <td><?= esc($user['username']) ?></td>
                                <td><?= esc($user['email']) ?></td>
                                <td><?= esc($user['name'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
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
            </div>
        </div>
    </div>

    <!-- Transaction Log -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Complete Transaction Log (<?= number_format($total_sales) ?> records)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Ref No</th>
                                    <th>Date & Time</th>
                                    <th>Cashier</th>
                                    <th>Total</th>
                                    <th>Discount</th>
                                    <th>Net Amount</th>
                                    <th>Payment</th>
                                    <th>Paid</th>
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
                                        <span class="badge bg-<?= $trans['status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= esc(ucfirst($trans['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Export Reports</h5>
                </div>
                <div class="card-body">
                    <p>Download compliance reports for BIR submission:</p>
                    <a href="<?= site_url('admin/birCompliance/exportCSV?date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to)) ?>"
                       class="btn btn-success">
                        📥 Export as CSV
                    </a>
                    <a href="<?= site_url('admin/birCompliance/exportPDF?date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to)) ?>"
                       class="btn btn-primary" target="_blank">
                        🖨️ Export as PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <strong>Note:</strong> This report is generated automatically from the POS system and contains accurate records of all transactions. 
                For official BIR submissions, ensure all reports are authorized by management and signed accordingly.
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

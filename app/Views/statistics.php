<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<style>
    .chart-container { position: relative; height: 320px; margin-bottom: 20px; }
    .stat-card { border-radius: 10px; padding: 20px; color: white; text-align: center; margin-bottom: 15px; }
    /* layout.php separately defines .stat-value/.stat-label for a different
       (plain, light-background) card style with dark text — same class
       names, so its color wins here since it doesn't get overridden by
       specificity. Scoping to ".stat-card" wins on specificity instead of
       source order, so these gradient cards reliably get white text.
       Several of the gradients (bg-primary/info-custom) also go through
       bright yellow (#ffc400) at one end, where white text needs a shadow
       to stay legible even once the color itself is correct. */
    .stat-card .stat-value { font-size: 32px; font-weight: bold; color: white; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.55); }
    .stat-card .stat-label { font-size: 14px; opacity: 0.95; margin-top: 5px; color: white; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5); }
    .bg-primary-custom { background: linear-gradient(135deg, #ffc400 0%, #ff6b00 100%); }
    .bg-success-custom { background: linear-gradient(135deg, #ff6b00 0%, #d95700 100%); }
    .bg-warning-custom { background: linear-gradient(135deg, #d95700 0%, #262d34 100%); }
    .bg-danger-custom { background: linear-gradient(135deg, #262d34 0%, #14181c 100%); }
    .bg-info-custom { background: linear-gradient(135deg, #ffc400 0%, #d95700 100%); }
    .inventory-table { font-size: 13px; }
    .period-btn { border-radius: 999px; font-weight: 600; }
    .period-btn.active { background: linear-gradient(135deg, #ffc400 0%, #ff6b00 32%, #d95700 68%, #14181c 100%); color: #fff; border-color: transparent; }
    .item-breakdown-card { border: 2px solid #d95700; }
    .item-breakdown-card .card-header { background: linear-gradient(135deg, #ffc400 0%, #ff6b00 32%, #d95700 68%, #14181c 100%); color: #fff; }

    /* Section tabs — split the page so each view only shows part of it */
    .stats-tabs {
        border-bottom: none;
        margin-bottom: 1.5rem;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        gap: 10px;
        background: #f1f2f5;
        padding: 6px;
        border-radius: 12px;
    }
    .stats-tabs .nav-link {
        color: #55525c;
        font-weight: 700;
        font-size: 0.95rem;
        border: none;
        border-radius: 8px;
        white-space: nowrap;
        padding: 10px 20px;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .stats-tabs .nav-link:hover {
        background: #ffffff;
        color: #d95700;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    .stats-tabs .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #ffc400 0%, #ff6b00 32%, #d95700 68%, #14181c 100%);
        box-shadow: 0 4px 14px rgba(38, 45, 52, 0.35);
    }
    .stats-tabs .nav-link .bi {
        font-size: 1.05rem;
    }

    @media (max-width: 768px) {
        .chart-container { height: 260px; }
        .stat-value { font-size: 22px; }
        .stat-label { font-size: 12px; }
        .stat-card { padding: 14px 10px; }
        .inventory-table { font-size: 12px; }
    }
    @media (max-width: 576px) {
        .chart-container { height: 200px; }
        .stat-value { font-size: 18px; }
    }
</style>

<div class="container-fluid">
    <h1 class="mb-4">Inventory & Sales Statistics</h1>

    <?php
        // Preserve the current cashier / payment / item filters when switching
        // between the quick period presets below.
        $presetCarry = array_filter([
            'cashier'        => $cashierFilter ?? '',
            'payment_method' => $paymentFilter ?? '',
            'item'           => $itemFilter ?? '',
        ]);
        $periodNow = $period ?? '';
    ?>

    <!-- Quick Period Presets -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="?<?= http_build_query($presetCarry) ?>" class="btn btn-outline-secondary btn-sm period-btn <?= $periodNow === '' ? 'active' : '' ?>">All Time</a>
        <a href="?<?= http_build_query(array_merge($presetCarry, ['period' => 'week'])) ?>" class="btn btn-outline-secondary btn-sm period-btn <?= $periodNow === 'week' ? 'active' : '' ?>"><i class="bi bi-calendar-week"></i> This Week</a>
        <a href="?<?= http_build_query(array_merge($presetCarry, ['period' => 'month'])) ?>" class="btn btn-outline-secondary btn-sm period-btn <?= $periodNow === 'month' ? 'active' : '' ?>"><i class="bi bi-calendar-month"></i> This Month</a>
        <a href="?<?= http_build_query(array_merge($presetCarry, ['period' => 'year'])) ?>" class="btn btn-outline-secondary btn-sm period-btn <?= $periodNow === 'year' ? 'active' : '' ?>"><i class="bi bi-calendar3"></i> This Year</a>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?= base_url('statistics') ?>" class="row align-items-end g-3">
                <div class="col-6 col-md-3">
                    <label for="date_from" class="form-label fw-semibold">Date From <span class="text-muted fw-normal">(custom)</span></label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= esc($dateFrom ?? '') ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label for="date_to" class="form-label fw-semibold">Date To <span class="text-muted fw-normal">(custom)</span></label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= esc($dateTo ?? '') ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label for="item" class="form-label fw-semibold">Item</label>
                    <select class="form-select" id="item" name="item">
                        <option value="">All Items</option>
                        <?php foreach (($soldItemOptions ?? []) as $opt): ?>
                            <option value="<?= esc($opt['code']) ?>" <?= ($itemFilter ?? '') === $opt['code'] ? 'selected' : '' ?>>📦 <?= esc($opt['name']) ?> (<?= esc($opt['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="cashier" class="form-label fw-semibold">Cashier</label>
                    <select class="form-select" id="cashier" name="cashier">
                        <option value="">All Cashiers</option>
                        <?php foreach (($allCashiers ?? []) as $cashier): ?>
                            <option value="<?= esc($cashier['username']) ?>" <?= ($cashierFilter ?? '') === $cashier['username'] ? 'selected' : '' ?>>👤 <?= esc($cashier['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="payment_method" class="form-label fw-semibold">Payment Method</label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value="">All Methods</option>
                        <?php foreach (($paymentMethods ?? []) as $pm): ?>
                            <option value="<?= esc($pm) ?>" <?= ($paymentFilter ?? '') === $pm ? 'selected' : '' ?>><?= esc(ucfirst($pm)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="<?= base_url('statistics') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Clear</a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($dateFrom || $dateTo || $cashierFilter || $itemFilter): ?>
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle"></i> Showing sales data
            <?php if ($periodNow === 'week'): ?>
                for <strong>this week</strong>
            <?php elseif ($periodNow === 'month'): ?>
                for <strong>this month</strong>
            <?php elseif ($periodNow === 'year'): ?>
                for <strong>this year</strong>
            <?php endif; ?>
            <?php if ($dateFrom && $dateTo): ?>
                (<strong><?= date('M d, Y', strtotime($dateFrom)) ?></strong> to <strong><?= date('M d, Y', strtotime($dateTo)) ?></strong>)
            <?php elseif ($dateFrom): ?>
                from <strong><?= date('M d, Y', strtotime($dateFrom)) ?></strong> onwards
            <?php elseif ($dateTo): ?>
                up to <strong><?= date('M d, Y', strtotime($dateTo)) ?></strong>
            <?php endif; ?>
            <?php if ($cashierFilter): ?>
                for cashier <strong><?= esc($cashierFilter) ?></strong>
            <?php endif; ?>
            <?php if ($itemFilter): ?>
                for item <strong><?= esc(($itemFilterDetails['name'] ?? $itemFilter)) ?></strong>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Section Tabs -->
    <ul class="nav stats-tabs" id="statsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-overview-btn" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab" aria-controls="tab-overview" aria-selected="true">
                <i class="bi bi-speedometer2"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-sales-btn" data-bs-toggle="tab" data-bs-target="#tab-sales" type="button" role="tab" aria-controls="tab-sales" aria-selected="false">
                <i class="bi bi-cash-coin"></i> Sales
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-inventory-btn" data-bs-toggle="tab" data-bs-target="#tab-inventory" type="button" role="tab" aria-controls="tab-inventory" aria-selected="false">
                <i class="bi bi-box-seam"></i> Inventory
            </button>
        </li>
    </ul>

    <div class="tab-content" id="statsTabContent">

    <!-- ============ OVERVIEW TAB ============ -->
    <div class="tab-pane fade show active" id="tab-overview" role="tabpanel" aria-labelledby="tab-overview-btn">

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card bg-primary-custom">
                <div class="stat-value"><?= $totalItems ?></div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-success-custom">
                <div class="stat-value">₱<?= number_format($totalValue, 0) ?></div>
                <div class="stat-label">Total Inventory Value</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-warning-custom">
                <div class="stat-value"><?= $lowStockCount ?></div>
                <div class="stat-label">Low Stock Items</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-danger-custom">
                <div class="stat-value"><?= $expiredCount + $expiringCount ?></div>
                <div class="stat-label">Expired / Expiring</div>
            </div>
        </div>
    </div>

    <!-- Sales Summary -->
    <div class="row mb-4">
        <div class="col-md-6 col-12">
            <div class="stat-card bg-info-custom">
                <div class="stat-value"><?= $totalSales ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
        <div class="col-md-6 col-12">
            <div class="stat-card" style="background: linear-gradient(135deg, #ff6b00 0%, #14181c 100%);">
                <div class="stat-value">₱<?= number_format($totalRevenue, 2) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 col-12 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Monthly Sales Revenue</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Inventory Status</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="inventoryStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Levels Chart -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Top Items by Stock Level</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 260px;">
                        <canvas id="stockLevelsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    <!-- ============ /OVERVIEW TAB ============ -->

    <!-- ============ INVENTORY TAB ============ -->
    <div class="tab-pane fade" id="tab-inventory" role="tabpanel" aria-labelledby="tab-inventory-btn">

    <!-- Detailed Tables -->
    <div class="row">
        <div class="col-lg-6 col-12 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">⚠️ Low Stock Items (Qty ≤ 10)</h5>
                </div>
                <div class="card-body" style="max-height: 340px; overflow-y: auto;">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover inventory-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Stock</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $lowStockItems = array_filter($allItems, function($item) {
                                return $item['quantity'] <= 10;
                            }); 
                            ?>
                            <?php foreach ($lowStockItems as $item): ?>
                                <tr>
                                    <td><code><?= esc($item['code']) ?></code></td>
                                    <td><?= esc($item['name']) ?></td>
                                    <td><span class="badge bg-warning"><?= $item['quantity'] ?></span></td>
                                    <td>₱<?= number_format($item['price'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($lowStockItems)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No low stock items</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📅 Expired / Expiring Items</h5>
                </div>
                <div class="card-body" style="max-height: 340px; overflow-y: auto;">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover inventory-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Expires</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $today = date('Y-m-d');
                            $expiringItems = array_filter($allItems, function($item) use ($today) {
                                if (!$item['expiration_date']) return false;
                                $expireDate = new DateTime($item['expiration_date']);
                                $todayDate = new DateTime($today);
                                $interval = $todayDate->diff($expireDate);
                                $daysUntilExpiry = $interval->format('%r%a');
                                return $daysUntilExpiry <= 30;
                            });
                            ?>
                            <?php foreach ($expiringItems as $item): ?>
                                <?php 
                                    $expireDate = new DateTime($item['expiration_date']);
                                    $todayDate = new DateTime($today);
                                    $interval = $todayDate->diff($expireDate);
                                    $daysUntilExpiry = $interval->format('%r%a');
                                    $isExpired = $daysUntilExpiry < 0;
                                ?>
                                <tr>
                                    <td><code><?= esc($item['code']) ?></code></td>
                                    <td><?= esc($item['name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($item['expiration_date'])) ?></td>
                                    <td>
                                        <?php if ($isExpired): ?>
                                            <span class="badge bg-danger">EXPIRED</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">EXPIRING IN <?= abs($daysUntilExpiry) ?> DAYS</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($expiringItems)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">All items are fresh</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    <!-- ============ /INVENTORY TAB ============ -->

    <!-- ============ SALES TAB ============ -->
    <div class="tab-pane fade" id="tab-sales" role="tabpanel" aria-labelledby="tab-sales-btn">

    <?php if ($itemFilter): ?>
    <!-- Selected Item: Breakdown by Cashier -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card item-breakdown-card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <div>
                        <h5 class="mb-0">🔎 <?= esc($itemFilterDetails['name'] ?? $itemFilter) ?> <span class="opacity-75">(<?= esc($itemFilterDetails['code'] ?? $itemFilter) ?>)</span></h5>
                        <small class="opacity-75">Who sold this item, and how much</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark"><?= array_sum(array_column($itemCashierBreakdown, 'total_qty')) ?> units sold</span>
                        <span class="badge bg-light text-dark">₱<?= number_format(array_sum(array_column($itemCashierBreakdown, 'total_amount')), 2) ?> total</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover inventory-table mb-0">
                        <thead>
                            <tr>
                                <th>👤 Cashier</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($itemCashierBreakdown)): ?>
                                <?php foreach ($itemCashierBreakdown as $row): ?>
                                    <tr>
                                        <td><?= esc($row['cashier_name'] ?? 'Unknown') ?></td>
                                        <td class="text-end"><?= $row['transaction_count'] ?></td>
                                        <td class="text-end"><span class="badge bg-success"><?= $row['total_qty'] ?></span></td>
                                        <td class="text-end">₱<?= number_format($row['total_amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No sales of this item found for the selected period</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Items Sold Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <div>
                        <h5 class="mb-0">🧾 Items Sold <?= ($dateFrom || $dateTo || $cashierFilter || $itemFilter) ? '(Filtered)' : '(All Time)' ?></h5>
                        <small class="text-muted">Every item sold in the selected period, with total quantity and total amount.</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary"><?= count($soldItems) ?> item(s)</span>
                        <span class="badge bg-info">₱<?= number_format(array_sum(array_column($soldItems, 'total_amount')), 2) ?> total</span>
                    </div>
                </div>
                <div class="card-body" style="max-height: 420px; overflow-y: auto;">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover inventory-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Item</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Total Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($soldItems)): ?>
                                <?php foreach ($soldItems as $i => $row): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><code><?= esc($row['code']) ?></code></td>
                                        <td><?= esc($row['name']) ?></td>
                                        <td class="text-end"><?= $row['transaction_count'] ?></td>
                                        <td class="text-end"><span class="badge bg-success"><?= $row['total_qty'] ?></span></td>
                                        <td class="text-end">₱<?= number_format($row['total_amount'], 2) ?></td>
                                        <td class="text-end">
                                            <a href="?<?= http_build_query(array_merge($presetCarry, ['period' => $periodNow, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'item' => $row['code']])) ?>#tab-sales" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-people"></i> By Cashier
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No sales data found for the selected period</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cashier Sales Overview -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <div>
                        <h5 class="mb-0">📊 Cashier Sales Overview <?= ($dateFrom || $dateTo || $cashierFilter) ? '(Filtered)' : '(All Time)' ?></h5>
                        <small class="text-muted">Combined view of sold items, cashier sales, and overall item performance.</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary"><?= count($salesWithCashier) ?> transaction(s)</span>
                        <span class="badge bg-success"><?= count($itemsByCashier) ?> cashier/item rows</span>
                        <span class="badge bg-info">₱<?= number_format(array_sum(array_column($itemsByCashier, 'total_amount')), 2) ?> total</span>
                    </div>
                </div>
                <div class="card-body" style="max-height: 480px; overflow-y: auto;">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover inventory-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>👤 Cashier</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Payment Method</th>
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($itemsByCashier)): ?>
                                <?php $currentCashier = null; ?>
                                <?php $rowNumber = 1; ?>
                                <?php foreach ($itemsByCashier as $item): ?>
                                    <?php if ($currentCashier !== $item['cashier_name']): ?>
                                        <?php $currentCashier = $item['cashier_name']; ?>
                                        <tr class="table-secondary">
                                            <td colspan="6" class="fw-bold">Cashier: <?= esc($currentCashier ?: 'Unknown') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><?= $rowNumber++ ?></td>
                                        <td><?= esc($item['cashier_name'] ?? 'Unknown') ?></td>
                                        <td><code><?= esc($item['code']) ?></code></td>
                                        <td><?= esc($item['item_name']) ?></td>
                                        <td>
                                            <?php if (strtolower($item['payment_method'] ?? '') === 'cash'): ?>
                                                <span class="badge bg-success">💵 Cash</span>
                                            <?php elseif (!empty($item['payment_method'])): ?>
                                                <span class="badge bg-info">📱 <?= esc(ucfirst($item['payment_method'])) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Unknown</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><span class="badge bg-success"><?= $item['total_qty'] ?></span></td>
                                        <td class="text-end">₱<?= number_format($item['total_amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No sales data found for the selected period</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    <!-- ============ /SALES TAB ============ -->

    </div>
    <!-- /tab-content -->
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Monthly Sales Chart
    const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
    const monthlySalesData = <?= json_encode($monthlySalesData) ?>;
    
    const months = Object.keys(monthlySalesData);
    const revenues = Object.values(monthlySalesData);
    
    new Chart(monthlySalesCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Revenue (₱)',
                data: revenues,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.5,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(v) { return '₱' + v.toLocaleString(); } } }
            }
        }
    });

    // Inventory Status Pie Chart
    const inventoryStatusCtx = document.getElementById('inventoryStatusChart').getContext('2d');
    new Chart(inventoryStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Normal Stock', 'Low Stock', 'Expired', 'Expiring Soon'],
            datasets: [{
                data: [
                    <?= $totalItems - $lowStockCount - $expiredCount - $expiringCount ?>,
                    <?= $lowStockCount ?>,
                    <?= $expiredCount ?>,
                    <?= $expiringCount ?>
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#ff9800'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Stock Levels Bar Chart
    const stockLevelsCtx = document.getElementById('stockLevelsChart').getContext('2d');
    const stockLevelsData = <?= json_encode($stockLevels) ?>;
    
    const itemCodes = stockLevelsData.map(item => item.code);
    const quantities = stockLevelsData.map(item => item.quantity);
    
    new Chart(stockLevelsCtx, {
        type: 'bar',
        data: {
            labels: itemCodes,
            datasets: [{
                label: 'Stock Quantity',
                data: quantities,
                backgroundColor: '#667eea',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });

    // Remember which tab was active across filter/page reloads, and jump
    // straight to a tab when the URL (or a link) points at one via #hash.
    (function () {
        var STORAGE_KEY = 'gmackStatsActiveTab';
        var hashId = (window.location.hash || '').replace('#', '');
        var wantedId = document.getElementById(hashId) ? hashId : localStorage.getItem(STORAGE_KEY);
        if (wantedId) {
            var btn = document.getElementById(wantedId + '-btn');
            if (btn) {
                bootstrap.Tab.getOrCreateInstance(btn).show();
            }
        }

        document.querySelectorAll('#statsTab button[data-bs-toggle="tab"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', function (e) {
                localStorage.setItem(STORAGE_KEY, e.target.dataset.bsTarget.replace('#', ''));
            });
        });
    })();
</script>

<?= $this->endSection() ?>

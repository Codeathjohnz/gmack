<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<style>
    .expense-total-card {
        background: var(--gradient-brand);
        color: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
    }
    .expense-total-card .stat-value { font-size: 32px; font-weight: bold; color: white; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4); }
    .expense-total-card .stat-label { font-size: 14px; opacity: 0.95; margin-top: 5px; color: white; }
    .period-btn { border-radius: 999px; font-weight: 600; }
    .period-btn.active { background: var(--gradient-brand); color: #fff; border-color: transparent; }
</style>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h2 class="mb-0"><i class="bi bi-cash-stack"></i> Expenses</h2>
        <a href="<?= base_url('admin/expenses/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Record Expense
        </a>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
        $presetCarry = array_filter(['category' => $categoryFilter ?? '']);
    ?>

    <!-- Quick Period Presets -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="?<?= http_build_query(array_merge($presetCarry, ['period' => 'week'])) ?>" class="btn btn-outline-secondary btn-sm period-btn <?= $period === 'week' ? 'active' : '' ?>"><i class="bi bi-calendar-week"></i> This Week</a>
        <a href="?<?= http_build_query(array_merge($presetCarry, ['period' => 'month'])) ?>" class="btn btn-outline-secondary btn-sm period-btn <?= $period === 'month' ? 'active' : '' ?>"><i class="bi bi-calendar-month"></i> This Month</a>
        <a href="?<?= http_build_query(array_merge($presetCarry, ['period' => 'year'])) ?>" class="btn btn-outline-secondary btn-sm period-btn <?= $period === 'year' ? 'active' : '' ?>"><i class="bi bi-calendar3"></i> This Year</a>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" action="<?= base_url('admin/expenses') ?>" class="row align-items-end g-3">
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= esc($dateFrom) ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= esc($dateTo) ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="<?= base_url('admin/expenses') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="expense-total-card">
                <div class="stat-value">₱<?= number_format($totalAmount, 2) ?></div>
                <div class="stat-label">TOTAL EXPENSES (<?= date('M d', strtotime($dateFrom)) ?> – <?= date('M d, Y', strtotime($dateTo)) ?>)</div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Breakdown by Category</h6>
                </div>
                <div class="card-body" style="max-height: 140px; overflow-y: auto;">
                    <?php if (!empty($byCategory)): ?>
                        <?php foreach ($byCategory as $row): ?>
                            <div class="d-flex justify-content-between border-bottom py-1">
                                <span><?= esc($row['category']) ?> <small class="text-muted">(<?= $row['count'] ?>)</small></span>
                                <strong>₱<?= number_format($row['total'], 2) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No expenses in this period yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover data-table" data-empty="No expenses recorded for this period.">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($expenses)): ?>
                        <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($exp['expense_date'])) ?></td>
                                <td><span class="badge bg-secondary"><?= esc($exp['category']) ?></span></td>
                                <td><?= esc($exp['description'] ?? '—') ?></td>
                                <td class="text-end fw-semibold">₱<?= number_format($exp['amount'], 2) ?></td>
                                <td>
                                    <a href="<?= base_url('admin/expenses/edit/' . $exp['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="<?= base_url('admin/expenses/delete/' . $exp['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete(event, '<?= esc($exp['category'], 'js') ?> expense');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<?php
    $qs = fn (array $extra = []) => http_build_query(array_merge(
        $period === 'custom' ? ['date_from' => $dateFrom, 'date_to' => $dateTo] : ['period' => $period],
        ['book' => $book],
        $extra
    ));
    $peso = fn ($v) => '₱' . number_format((float) $v, 2);
    $titles = [
        'receipts'      => 'Cash Receipts Book',
        'disbursements' => 'Cash Disbursements Book',
        'ledger'        => 'General Ledger',
    ];
?>

<style>
    .book-tabs .nav-link { font-weight: 600; color: var(--steel-700, #2b3238); }
    .book-tabs .nav-link.active { background: var(--gradient-brand); color: #fff; border-color: transparent; }
    .period-btn { border-radius: 999px; font-weight: 600; }
    .period-btn.active { background: var(--gradient-brand); color: #fff; border-color: transparent; }
    .book-table td.num, .book-table th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .book-table tfoot td { font-weight: 700; border-top: 2px solid #1c2126; }
    .print-header { display: none; }
    @media print {
        .sidebar, .navbar, .no-print, .impersonation-banner { display: none !important; }
        .main-content, main, .container-fluid { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .print-header { display: block; margin-bottom: 12px; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
        <h2 class="mb-0"><i class="bi bi-journal-bookmark"></i> Book of Accounts</h2>
        <a href="<?= base_url('admin/books/quarterly') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-calendar3-range"></i> Quarterly Report
        </a>
    </div>

    <div class="print-header">
        <h4 class="mb-0"><?= esc($business['name'] ?? '') ?></h4>
        <?php if (!empty($business['address'])): ?><div><?= esc($business['address']) ?></div><?php endif; ?>
        <h5 class="mt-2 mb-0"><?= $titles[$book] ?></h5>
        <div><?= date('F d, Y', strtotime($dateFrom)) ?> – <?= date('F d, Y', strtotime($dateTo)) ?></div>
    </div>

    <!-- Book tabs -->
    <ul class="nav nav-pills book-tabs gap-2 mb-3 no-print">
        <?php foreach ($titles as $key => $label): ?>
            <li class="nav-item">
                <a class="nav-link border <?= $book === $key ? 'active' : '' ?>" href="?<?= $qs(['book' => $key]) ?>"><?= $label ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Period -->
    <div class="card mb-3 no-print">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php foreach (['all' => 'All Records', 'month' => 'This Month', 'quarter' => 'This Quarter', 'year' => 'This Year'] as $p => $label): ?>
                    <a href="?<?= http_build_query(['period' => $p, 'book' => $book]) ?>" class="btn btn-outline-secondary btn-sm period-btn <?= $period === $p ? 'active' : '' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
            <form method="get" class="row align-items-end g-3">
                <input type="hidden" name="book" value="<?= esc($book) ?>">
                <div class="col-6 col-md-4">
                    <label for="date_from" class="form-label fw-semibold">Date From</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="<?= esc($dateFrom) ?>">
                </div>
                <div class="col-6 col-md-4">
                    <label for="date_to" class="form-label fw-semibold">Date To</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="<?= esc($dateTo) ?>">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Apply</button>
                    <a href="<?= base_url('admin/books/export') ?>?<?= $qs(['type' => $book]) ?>" class="btn btn-success" title="Download CSV"><i class="bi bi-filetype-csv"></i></a>
                    <button type="button" class="btn btn-secondary" onclick="window.print()" title="Print"><i class="bi bi-printer"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row g-3 mb-3 no-print">
        <div class="col-sm-4"><div class="card stat-card"><div class="card-body">
            <div class="stat-label">Cash Received (Net Sales)</div>
            <div class="fs-4 fw-bold"><?= $peso($receipts['net']) ?></div>
        </div></div></div>
        <div class="col-sm-4"><div class="card stat-card"><div class="card-body">
            <div class="stat-label">Cash Disbursed</div>
            <div class="fs-4 fw-bold"><?= $peso($disbursements['total']) ?></div>
        </div></div></div>
        <div class="col-sm-4"><div class="card stat-card"><div class="card-body">
            <div class="stat-label">Net Cash Flow</div>
            <div class="fs-4 fw-bold <?= $ledger['cashBalance'] < 0 ? 'text-danger' : 'text-success' ?>"><?= $peso($ledger['cashBalance']) ?></div>
        </div></div></div>
    </div>

    <div class="card">
        <div class="card-header no-print"><strong><?= $titles[$book] ?></strong>
            <span class="text-muted small">· <?= date('M d, Y', strtotime($dateFrom)) ?> – <?= date('M d, Y', strtotime($dateTo)) ?></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <?php if ($book === 'receipts'): ?>
                <table class="table table-sm book-table">
                    <thead><tr><th>Date</th><th>OR From</th><th>OR To</th><th class="num">Transactions</th><th class="num">Gross Sales</th><th class="num">Discounts</th><th class="num">Net Sales (Cash Received)</th></tr></thead>
                    <tbody>
                    <?php if (empty($receipts['rows'])): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No completed sales in this period.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($receipts['rows'] as $r): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($r['date'])) ?></td>
                            <td><?= esc($r['or_from']) ?></td>
                            <td><?= esc($r['or_to']) ?></td>
                            <td class="num"><?= number_format($r['transactions']) ?></td>
                            <td class="num"><?= $peso($r['gross']) ?></td>
                            <td class="num"><?= $peso($r['discount']) ?></td>
                            <td class="num"><?= $peso($r['net']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td colspan="3">Total</td><td class="num"><?= number_format($receipts['transactions']) ?></td><td class="num"><?= $peso($receipts['gross']) ?></td><td class="num"><?= $peso($receipts['discount']) ?></td><td class="num"><?= $peso($receipts['net']) ?></td></tr></tfoot>
                </table>

            <?php elseif ($book === 'disbursements'): ?>
                <table class="table table-sm book-table">
                    <thead><tr><th>Date</th><th>Reference</th><th>Payee / Particulars</th><th>Account</th><th class="num">Amount</th></tr></thead>
                    <tbody>
                    <?php if (empty($disbursements['rows'])): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No expenses or received purchase orders in this period.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($disbursements['rows'] as $d): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($d['date'])) ?></td>
                            <td><?= esc($d['reference']) ?></td>
                            <td><?= esc($d['particulars']) ?></td>
                            <td><?= esc($d['account']) ?></td>
                            <td class="num"><?= $peso($d['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td colspan="4">Total</td><td class="num"><?= $peso($disbursements['total']) ?></td></tr></tfoot>
                </table>

            <?php else: ?>
                <table class="table table-sm book-table">
                    <thead><tr><th>Account</th><th class="num">Debit</th><th class="num">Credit</th></tr></thead>
                    <tbody>
                    <?php foreach ($ledger['accounts'] as $a): ?>
                        <tr>
                            <td><?= esc($a['account']) ?></td>
                            <td class="num"><?= $a['debit'] ? $peso($a['debit']) : '' ?></td>
                            <td class="num"><?= $a['credit'] ? $peso($a['credit']) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td>Total</td><td class="num"><?= $peso($ledger['totalDebit']) ?></td><td class="num"><?= $peso($ledger['totalCredit']) ?></td></tr></tfoot>
                </table>
                <p class="small text-muted mb-0">Cash basis. Debits equal credits: cash received from sales and paid out for purchases and expenses, with sales, discounts, purchases, and each expense category as separate accounts.</p>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <p class="small text-muted mt-3 no-print">Built from completed sales, recorded expenses, and received purchase orders. Have your accountant review these books before using them for BIR filing.</p>
</div>

<?= $this->endSection() ?>

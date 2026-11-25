<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<?php
    $peso = fn ($v) => '₱' . number_format((float) $v, 2);
    $t = $report['totals'];
?>

<style>
    .book-table td.num, .book-table th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .book-table tfoot td { font-weight: 700; border-top: 2px solid #1c2126; }
    .tax-box { border: 2px dashed #ffc400; border-radius: 10px; background: #fffbea; }
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
        <h2 class="mb-0"><i class="bi bi-calendar3-range"></i> Quarterly Report</h2>
        <a href="<?= base_url('admin/books') ?>" class="btn btn-outline-secondary"><i class="bi bi-journal-bookmark"></i> Book of Accounts</a>
    </div>

    <div class="print-header">
        <h4 class="mb-0"><?= esc($business['name'] ?? '') ?></h4>
        <?php if (!empty($business['address'])): ?><div><?= esc($business['address']) ?></div><?php endif; ?>
        <h5 class="mt-2 mb-0">Quarterly Report · Q<?= $quarter ?> <?= $year ?></h5>
        <div><?= esc($report['periodLabel']) ?></div>
    </div>

    <div class="card mb-3 no-print">
        <div class="card-body">
            <form method="get" class="row align-items-end g-3">
                <div class="col-6 col-md-3">
                    <label for="year" class="form-label fw-semibold">Year</label>
                    <select id="year" name="year" class="form-select">
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="quarter" class="form-label fw-semibold">Quarter</label>
                    <select id="quarter" name="quarter" class="form-select">
                        <?php foreach ([1 => 'Q1 (Jan–Mar)', 2 => 'Q2 (Apr–Jun)', 3 => 'Q3 (Jul–Sep)', 4 => 'Q4 (Oct–Dec)'] as $q => $label): ?>
                            <option value="<?= $q ?>" <?= $q === $quarter ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Show</button>
                    <a href="<?= base_url('admin/books/export') ?>?<?= http_build_query(['type' => 'quarterly', 'year' => $year, 'quarter' => $quarter]) ?>" class="btn btn-success"><i class="bi bi-filetype-csv"></i> CSV</a>
                    <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="card-body">
            <div class="stat-label">Net Sales</div><div class="fs-4 fw-bold"><?= $peso($t['net']) ?></div>
        </div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="card-body">
            <div class="stat-label">Purchases</div><div class="fs-4 fw-bold"><?= $peso($t['purchases']) ?></div>
        </div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="card-body">
            <div class="stat-label">Expenses</div><div class="fs-4 fw-bold"><?= $peso($t['expenses']) ?></div>
        </div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="card stat-card"><div class="card-body">
            <div class="stat-label">Net Income (cash basis)</div>
            <div class="fs-4 fw-bold <?= $t['income'] < 0 ? 'text-danger' : 'text-success' ?>"><?= $peso($t['income']) ?></div>
        </div></div></div>
    </div>

    <div class="card mb-3">
        <div class="card-header no-print"><strong>Q<?= $quarter ?> <?= $year ?></strong> <span class="text-muted small">· <?= esc($report['periodLabel']) ?></span></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm book-table">
                    <thead><tr><th>Month</th><th class="num">Transactions</th><th class="num">Gross Sales</th><th class="num">Discounts</th><th class="num">Net Sales</th><th class="num">Purchases</th><th class="num">Expenses</th><th class="num">Net Income</th></tr></thead>
                    <tbody>
                    <?php foreach ($report['months'] as $m): ?>
                        <tr>
                            <td><?= esc($m['label']) ?></td>
                            <td class="num"><?= number_format($m['transactions']) ?></td>
                            <td class="num"><?= $peso($m['gross']) ?></td>
                            <td class="num"><?= $peso($m['discount']) ?></td>
                            <td class="num"><?= $peso($m['net']) ?></td>
                            <td class="num"><?= $peso($m['purchases']) ?></td>
                            <td class="num"><?= $peso($m['expenses']) ?></td>
                            <td class="num <?= $m['income'] < 0 ? 'text-danger' : '' ?>"><?= $peso($m['income']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr>
                        <td>Total</td><td class="num"><?= number_format($t['transactions']) ?></td><td class="num"><?= $peso($t['gross']) ?></td><td class="num"><?= $peso($t['discount']) ?></td>
                        <td class="num"><?= $peso($t['net']) ?></td><td class="num"><?= $peso($t['purchases']) ?></td><td class="num"><?= $peso($t['expenses']) ?></td><td class="num"><?= $peso($t['income']) ?></td>
                    </tr></tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="tax-box p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="fw-bold">Estimated percentage tax (<?= rtrim(rtrim(number_format($taxRate * 100, 2), '0'), '.') ?>% of net sales)</div>
                <div class="small text-muted">Reference for BIR Form 2551Q (quarterly percentage tax) and 1701Q (quarterly income tax). Applies to non-VAT businesses. An accountant should review these figures before filing.</div>
            </div>
            <div class="fs-3 fw-bold"><?= $peso($report['percentageTax']) ?></div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

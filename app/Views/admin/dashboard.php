<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-1">Dashboard</h2>
            <p class="text-muted mb-4"><i class="bi bi-buildings"></i> <?= esc($business['name'] ?? session()->get('business_name') ?? 'Your business') ?></p>
        </div>
    </div>

    <?php
        $alertMeta = [
            'expired'  => ['icon' => 'bi-calendar-x-fill',           'color' => '#721c24'],
            'out'      => ['icon' => 'bi-x-circle-fill',             'color' => '#dc3545'],
            'critical' => ['icon' => 'bi-exclamation-triangle-fill', 'color' => '#ff6b00'],
            'expiring' => ['icon' => 'bi-hourglass-split',           'color' => '#d95700'],
            'low'      => ['icon' => 'bi-exclamation-circle-fill',   'color' => '#ffc400'],
        ];
    ?>
    <div class="row mb-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-bell-fill"></i> Notifications</h5>
                    <?php if (!empty($alerts)): ?>
                        <span class="badge bg-danger"><?= (int) $alerts_total ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body" style="max-height: 320px; overflow-y: auto;">
                    <?php if (empty($alerts)): ?>
                        <p class="text-success mb-0"><i class="bi bi-check-circle"></i> All good — no low stock or expired items right now.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($alerts as $alert): ?>
                                <?php $meta = $alertMeta[$alert['type']]; ?>
                                <a href="<?= base_url('admin/items/edit/' . $alert['item']['id']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2" style="border-left: 4px solid <?= $meta['color'] ?>;">
                                    <i class="bi <?= $meta['icon'] ?>" style="color: <?= $meta['color'] ?>; font-size: 1.1rem;"></i>
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold"><?= esc($alert['item']['name']) ?></span>
                                        <span class="text-muted">· <?= esc($alert['message']) ?></span>
                                    </div>
                                    <code><?= esc($alert['item']['code']) ?></code>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($alerts_total > count($alerts)): ?>
                            <p class="text-muted mt-2 mb-0"><?= $alerts_total - count($alerts) ?> more item(s) need attention — see <a href="<?= base_url('admin/items') ?>">Items</a>.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Items</div>
                            <div class="stat-value" data-count="<?= (int) $total_items ?>">0</div>
                        </div>
                        <i class="bi bi-box-seam" style="font-size:1.8rem;color:var(--safety-orange);opacity:.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Active Items</div>
                            <div class="stat-value" data-count="<?= (int) $active_items ?>">0</div>
                        </div>
                        <i class="bi bi-check-circle" style="font-size:1.8rem;color:var(--blueprint-blue);opacity:.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value" data-count="<?= (int) $total_users ?>">0</div>
                        </div>
                        <i class="bi bi-people" style="font-size:1.8rem;color:var(--caution-yellow);opacity:.7;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2 g-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Sales — Last 7 Days</h5>
                    <canvas id="salesTrendChart" height="110"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Stock Health</h5>
                    <canvas id="stockHealthChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php
    $rankLists = [
        'top'   => ['items' => $top_products,   'badge' => 'bg-primary'],
        'least' => ['items' => $least_products, 'badge' => 'bg-secondary'],
    ];
    ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 mb-2 gap-2">
        <h5 class="mb-0" id="rankHeading"><i class="bi bi-trophy"></i> Top Selling Products <small class="text-muted fw-normal">(Last 30 Days)</small></h5>
        <div class="btn-group btn-group-sm" role="group" aria-label="Ranking view">
            <input type="radio" class="btn-check" name="rankMode" id="rankTop" value="top" autocomplete="off" checked>
            <label class="btn btn-outline-primary" for="rankTop"><i class="bi bi-graph-up-arrow"></i> Most Sold</label>
            <input type="radio" class="btn-check" name="rankMode" id="rankLeast" value="least" autocomplete="off">
            <label class="btn btn-outline-primary" for="rankLeast"><i class="bi bi-graph-down-arrow"></i> Least Sold</label>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title" id="rankChartTitle">Units Sold</h5>
                    <?php if (!empty($top_products) || !empty($least_products)): ?>
                        <canvas id="topProductsChart" height="180"></canvas>
                    <?php else: ?>
                        <p class="text-muted mb-0 mt-3">No items or sales yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Product Ranking</h5>
                    <?php foreach ($rankLists as $mode => $list): ?>
                        <div class="rank-list" data-mode="<?= $mode ?>"<?= $mode === 'top' ? '' : ' style="display:none"' ?>>
                            <?php if (!empty($list['items'])): ?>
                                <ol class="list-group list-group-numbered">
                                    <?php foreach ($list['items'] as $p): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-semibold"><?= esc($p['name']) ?></div>
                                                <small class="text-muted">
                                                    <?php if ((float) $p['total_qty'] <= 0): ?>
                                                        <span class="text-danger fw-semibold">No sales</span>
                                                    <?php else: ?>
                                                        <?= (int) $p['total_qty'] ?> sold · <?= (int) $p['transaction_count'] ?> transaction<?= $p['transaction_count'] == 1 ? '' : 's' ?>
                                                    <?php endif; ?>
                                                    <?php if (isset($p['stock'])): ?> · <?= (int) $p['stock'] ?> in stock<?php endif; ?>
                                                </small>
                                            </div>
                                            <span class="badge <?= $list['badge'] ?> rounded-pill">₱<?= number_format($p['total_amount'], 2) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                                <?php if ($mode === 'least' && !empty($unsold_count)): ?>
                                    <div class="alert alert-warning small mt-3 mb-0 py-2">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong><?= number_format($unsold_count) ?></strong> active item<?= $unsold_count == 1 ? '' : 's' ?> had no sales in the last 30 days. The ones with the most stock are listed first.
                                        <a href="<?= base_url('admin/items') ?>" class="alert-link">View items</a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0 mt-3"><?= $mode === 'top' ? 'Nothing sold yet — the ranking fills in once sales come through.' : 'No active items yet.' ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <a href="<?= base_url('admin/items') ?>" class="btn btn-primary btn-sm mb-2">
                        <i class="bi bi-plus-circle"></i> Manage Items
                    </a>
                    <a href="<?= base_url('admin/purchase-orders/create') ?>" class="btn btn-secondary btn-sm mb-2 text-white">
                        <i class="bi bi-clipboard-check"></i> New Purchase Order
                    </a>
                    <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary btn-sm mb-2 text-white">
                        <i class="bi bi-people"></i> Manage Users
                    </a>
                    <a href="<?= base_url('admin/birCompliance') ?>" class="btn btn-secondary btn-sm mb-2 text-white">
                        <i class="bi bi-file-text"></i> BIR Compliance Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('salesTrendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($sales_trend_labels) ?>,
        datasets: [{
            label: 'Net Sales (₱)',
            data: <?= json_encode($sales_trend_values) ?>,
            borderColor: '#ff6b00',
            backgroundColor: 'rgba(255, 107, 0, 0.12)',
            tension: 0.35,
            fill: true,
            pointBackgroundColor: '#ff6b00',
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } },
    }
});

new Chart(document.getElementById('stockHealthChart'), {
    type: 'doughnut',
    data: {
        labels: ['Healthy', 'Low', 'Critical', 'Out of Stock'],
        datasets: [{
            data: [
                <?= (int) $stock_health['healthy'] ?>,
                <?= (int) $stock_health['low'] ?>,
                <?= (int) $stock_health['critical'] ?>,
                <?= (int) $stock_health['out'] ?>
            ],
            backgroundColor: ['#198754', '#ffc400', '#ff6b00', '#dc3545'],
            borderWidth: 0,
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } },
    }
});

<?php if (!empty($top_products) || !empty($least_products)): ?>
(function () {
    var rankData = {
        top: {
            labels: <?= json_encode(array_column($top_products, 'name')) ?>,
            values: <?= json_encode(array_map('intval', array_column($top_products, 'total_qty'))) ?>,
            color: '#ff6b00',
            heading: '<i class="bi bi-trophy"></i> Top Selling Products <small class="text-muted fw-normal">(Last 30 Days)</small>'
        },
        least: {
            labels: <?= json_encode(array_column($least_products, 'name')) ?>,
            values: <?= json_encode(array_map('intval', array_column($least_products, 'total_qty'))) ?>,
            color: '#6c757d',
            heading: '<i class="bi bi-graph-down-arrow"></i> Least Selling Products <small class="text-muted fw-normal">(Last 30 Days)</small>'
        }
    };
    var chart = new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: rankData.top.labels,
            datasets: [{ label: 'Units Sold', data: rankData.top.values, backgroundColor: rankData.top.color, borderRadius: 4, minBarLength: 3 }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
        }
    });
    function show(mode) {
        var d = rankData[mode];
        chart.data.labels = d.labels;
        chart.data.datasets[0].data = d.values;
        chart.data.datasets[0].backgroundColor = d.color;
        chart.update();
        document.getElementById('rankHeading').innerHTML = d.heading;
        document.querySelectorAll('.rank-list').forEach(function (el) {
            el.style.display = el.dataset.mode === mode ? '' : 'none';
        });
        try { localStorage.setItem('gmackRankMode', mode); } catch (e) {}
    }
    document.querySelectorAll('input[name="rankMode"]').forEach(function (r) {
        r.addEventListener('change', function () { if (r.checked) show(r.value); });
    });
    var saved = null;
    try { saved = localStorage.getItem('gmackRankMode'); } catch (e) {}
    if (saved === 'least') { document.getElementById('rankLeast').checked = true; show('least'); }
})();
<?php endif; ?>
</script>

<?= $this->endSection() ?>

<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<?php
$typeBadge = ['restock' => 'bg-success', 'sale' => 'bg-primary', 'adjustment' => 'bg-warning text-dark'];
$typeIcon  = ['restock' => 'bi-box-arrow-in-down', 'sale' => 'bi-cart-check', 'adjustment' => 'bi-arrow-left-right'];
?>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-1">
        <a href="<?= base_url('admin/items') ?>" class="btn btn-link p-0" style="font-size:1.4rem;"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0">Stock History — <?= esc($item['name']) ?></h2>
    </div>
    <p class="text-muted mb-3">Current quantity: <strong><?= number_format($item['quantity'], 2) ?> <?= ($item['unit'] ?? 'pcs') === 'kilo' ? 'kg' : 'pcs' ?></strong></p>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover data-table" data-empty="No stock history for this item yet.">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Reason / Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movements)): ?>
                        <?php foreach ($movements as $m): ?>
                            <tr>
                                <td class="text-nowrap text-muted small"><?= $m['created_at'] ? date('M j, Y g:ia', strtotime($m['created_at'])) : '—' ?></td>
                                <td><span class="badge <?= $typeBadge[$m['movement_type']] ?? 'bg-secondary' ?>"><i class="bi <?= $typeIcon[$m['movement_type']] ?? '' ?>"></i> <?= ucfirst($m['movement_type']) ?></span></td>
                                <td class="fw-bold <?= $m['quantity'] > 0 ? 'text-success' : 'text-danger' ?>"><?= $m['quantity'] > 0 ? '+' : '' ?><?= number_format($m['quantity'], 2) ?></td>
                                <td class="text-muted small"><?= esc($m['reason'] ?? $m['reference'] ?? '—') ?></td>
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

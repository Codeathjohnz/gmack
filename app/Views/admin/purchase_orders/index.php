<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<?php
$statusBadge = [
    'draft'     => 'bg-secondary',
    'pending'   => 'bg-warning text-dark',
    'approved'  => 'bg-info text-dark',
    'received'  => 'bg-success',
    'cancelled' => 'bg-danger',
];
?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h2 class="mb-0"><i class="bi bi-clipboard-check"></i> Purchase Orders</h2>
        <a href="<?= base_url('admin/purchase-orders/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Purchase Order
        </a>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= esc(session('error')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover data-table" data-empty="No purchase orders yet — restock from a supplier to create one.">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pos)): ?>
                        <?php foreach ($pos as $po): ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($po['po_number']) ?></td>
                                <td><?= esc($po['supplier_name']) ?></td>
                                <td><span class="badge <?= $statusBadge[$po['status']] ?? 'bg-secondary' ?>"><?= ucfirst($po['status']) ?></span></td>
                                <td>₱<?= number_format($po['total_amount'], 2) ?></td>
                                <td class="text-muted small"><?= $po['created_at'] ? date('M j, Y', strtotime($po['created_at'])) : '—' ?></td>
                                <td>
                                    <a href="<?= base_url('admin/purchase-orders/view/' . $po['id']) ?>" class="btn btn-secondary btn-sm text-white">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if (in_array($po['status'], \App\Controllers\Admin\PurchaseOrders::DELETABLE_STATUSES, true)): ?>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deletePO(<?= (int) $po['id'] ?>, <?= esc(json_encode($po['po_number']), 'attr') ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
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

<?= $this->include('admin/purchase_orders/_delete') ?>

<?= $this->endSection() ?>

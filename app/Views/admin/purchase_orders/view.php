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
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="<?= base_url('admin/purchase-orders') ?>" class="btn btn-link p-0" style="font-size:1.4rem;"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0"><?= esc($po['po_number']) ?></h2>
        <span class="badge <?= $statusBadge[$po['status']] ?? 'bg-secondary' ?> ms-2" style="font-size:0.85rem;"><?= ucfirst($po['status']) ?></span>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= esc(session('error')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <div class="stat-label">Supplier</div>
                <div class="fw-bold fs-5"><?= esc($po['supplier']['name'] ?? 'Unknown') ?></div>
                <?php if (!empty($po['supplier']['phone'])): ?><div class="text-muted small"><?= esc($po['supplier']['phone']) ?></div><?php endif; ?>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <div class="stat-label">Total Amount</div>
                <div class="fw-bold fs-5">₱<?= number_format($po['total_amount'], 2) ?></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card h-100"><div class="card-body">
                <div class="stat-label">Created</div>
                <div class="fw-bold fs-5"><?= $po['created_at'] ? date('M j, Y', strtotime($po['created_at'])) : '—' ?></div>
                <?php if (!empty($po['received_at'])): ?><div class="text-success small">Received <?= date('M j, Y g:ia', strtotime($po['received_at'])) ?></div><?php endif; ?>
            </div></div>
        </div>
    </div>

    <?php if (!empty($po['notes'])): ?>
        <div class="alert alert-secondary"><strong>Notes:</strong> <?= esc($po['notes']) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-list-ul"></i> Line Items</div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Linked to Inventory?</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $line): ?>
                        <tr>
                            <td><?= esc($line['item_name']) ?></td>
                            <td><?= $line['item_id'] ? '<span class="badge bg-success">Yes — will restock</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                            <td><?= number_format($line['quantity'], 2) ?></td>
                            <td>₱<?= number_format($line['unit_cost'], 2) ?></td>
                            <td>₱<?= number_format($line['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <?php if ($po['status'] === 'pending'): ?>
            <a href="<?= base_url('admin/purchase-orders/approve/' . $po['id']) ?>" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Approve
            </a>
            <a href="<?= base_url('admin/purchase-orders/cancel/' . $po['id']) ?>" class="btn btn-outline-danger" onclick="return confirmDelete(event, '<?= esc($po['po_number'], 'js') ?>', { title: 'Cancel this purchase order?', icon: 'warning', confirmButtonText: 'Cancel PO' });">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        <?php elseif ($po['status'] === 'approved'): ?>
            <a href="<?= base_url('admin/purchase-orders/receive/' . $po['id']) ?>" class="btn btn-primary" onclick="return confirmDelete(event, '<?= esc($po['po_number'], 'js') ?>', { title: 'Mark as received?', text: 'This will add the quantities to your inventory.', icon: 'question', confirmButtonText: 'Receive & Update Stock' });">
                <i class="bi bi-box-arrow-in-down"></i> Receive &amp; Update Stock
            </a>
            <a href="<?= base_url('admin/purchase-orders/cancel/' . $po['id']) ?>" class="btn btn-outline-danger" onclick="return confirmDelete(event, '<?= esc($po['po_number'], 'js') ?>', { title: 'Cancel this purchase order?', icon: 'warning', confirmButtonText: 'Cancel PO' });">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        <?php elseif ($po['status'] === 'received'): ?>
            <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill"></i> Received — inventory updated</span>
        <?php elseif ($po['status'] === 'cancelled'): ?>
            <span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill"></i> Cancelled</span>
        <?php endif; ?>
        <?php if (in_array($po['status'], \App\Controllers\Admin\PurchaseOrders::DELETABLE_STATUSES, true)): ?>
            <button type="button" class="btn btn-danger" onclick="deletePO(<?= (int) $po['id'] ?>, <?= esc(json_encode($po['po_number']), 'attr') ?>)">
                <i class="bi bi-trash"></i> Delete
            </button>
        <?php endif; ?>
    </div>
</div>

<?= $this->include('admin/purchase_orders/_delete') ?>

<?= $this->endSection() ?>

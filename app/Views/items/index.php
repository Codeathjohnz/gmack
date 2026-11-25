<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-header h1 {
        margin: 0;
        color: var(--primary);
        font-weight: 600;
    }

    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
        padding: 20px;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        font-weight: 500;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-warning {
        background-color: #f39c12;
        border-color: #f39c12;
    }

    .btn-danger {
        background-color: #e74c3c;
        border-color: #e74c3c;
    }

    .table {
        background-color: white;
    }

    .table thead th {
        background-color: #ecf0f1;
        border-bottom: 2px solid #bdc3c7;
        font-weight: 600;
        color: #2c3e50;
    }

    .table tbody tr {
        border-bottom: 1px solid #ecf0f1;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 12px;
        padding: 5px 10px;
    }

    .alert {
        border: none;
        border-radius: 6px;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(212, 65, 14, 0.25);
    }

    .form-label {
        font-weight: 500;
        color: #2c3e50;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .empty-state h3 {
        color: #666;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
        }

        .page-header button {
            width: 100%;
        }
    }
</style>

<!-- Alert Messages -->
<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?= session('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ❌ <?= session('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Items Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Items</h5>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <h3>No Items Found</h3>
                <p>Start by adding your first item to the inventory.</p>
                <a href="<?= base_url('items/add') ?>" class="btn btn-primary mt-3">Add First Item</a>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?= esc($item['code']) ?></strong>
                            </td>
                            <td><?= esc($item['name']) ?></td>
                            <td>
                                <small class="text-muted"><?= esc(substr($item['description'] ?? '', 0, 40)) ?><?= strlen($item['description'] ?? '') > 40 ? '...' : '' ?></small>
                            </td>
                            <td>
                                <strong class="text-success">₱<?= number_format($item['price'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="badge <?= $item['quantity'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $item['quantity'] ?> units
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $item['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= base_url('items/edit/' . $item['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="<?= base_url('items/delete/' . $item['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

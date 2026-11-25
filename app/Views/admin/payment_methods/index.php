<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h2 class="mb-0"><i class="bi bi-credit-card"></i> Payment Methods</h2>
        <a href="<?= base_url('admin/payment-methods/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Payment Method
        </a>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($methods)): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-credit-card" style="font-size: 48px;"></i>
                        <p class="mt-3">No payment methods configured yet.<br>Add your first payment method to get started.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($methods as $method): ?>
                <div class="col-lg-4 col-md-6 col-12 mb-3">
                    <div class="card h-100 <?= $method['status'] !== 'active' ? 'opacity-50' : '' ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi <?= esc($method['icon'] ?? 'bi-wallet2') ?>"></i>
                                <?= esc($method['name']) ?>
                            </h5>
                            <span class="badge <?= $method['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= ucfirst(esc($method['status'])) ?>
                            </span>
                        </div>
                        <div class="card-body text-center">
                            <?php if (!empty($method['qr_code'])): ?>
                                <img src="<?= esc(base_url($method['qr_code']), 'attr') ?>" alt="QR Code" class="img-fluid mb-3" style="max-width: 180px; border-radius: 8px; border: 2px solid #dee2e6;">
                            <?php else: ?>
                                <div class="mb-3 p-4 bg-light rounded" style="display: inline-block;">
                                    <i class="bi bi-qr-code" style="font-size: 60px; color: #ccc;"></i>
                                    <br><small class="text-muted">No QR Code</small>
                                </div>
                            <?php endif; ?>

                            <div class="text-start">
                                <p class="mb-1"><strong>Type:</strong> <?= ucfirst(esc($method['type'])) ?></p>
                                <?php if (!empty($method['account_name'])): ?>
                                    <p class="mb-1"><strong>Account Name:</strong> <?= esc($method['account_name']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($method['account_number'])): ?>
                                    <p class="mb-1"><strong>Account/Number:</strong> <code class="fs-6"><?= esc($method['account_number']) ?></code></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer d-flex gap-2">
                            <a href="<?= base_url('admin/payment-methods/edit/' . $method['id']) ?>" class="btn btn-warning btn-sm flex-fill">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="<?= base_url('admin/payment-methods/delete/' . $method['id']) ?>" class="btn btn-danger btn-sm flex-fill" onclick="return confirmDelete(event, '<?= esc($method['name'], 'js') ?>');">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

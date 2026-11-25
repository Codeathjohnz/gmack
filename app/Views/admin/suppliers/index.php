<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h2 class="mb-0"><i class="bi bi-truck"></i> Suppliers</h2>
        <a href="<?= base_url('admin/suppliers/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Supplier
        </a>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover data-table" data-empty="No suppliers yet — add the vendors you restock from.">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($suppliers)): ?>
                        <?php foreach ($suppliers as $s): ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($s['name']) ?></td>
                                <td><?= esc($s['contact_person'] ?? '—') ?></td>
                                <td><?= esc($s['phone'] ?? '—') ?></td>
                                <td><?= esc($s['email'] ?? '—') ?></td>
                                <td>
                                    <span class="badge <?= $s['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($s['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('admin/suppliers/edit/' . $s['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="<?= base_url('admin/suppliers/delete/' . $s['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete(event, '<?= esc($s['name'], 'js') ?>');">Delete</a>
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

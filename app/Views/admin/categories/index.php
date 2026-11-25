<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h2 class="mb-0"><i class="bi bi-tags"></i> Manage Categories</h2>
        <a href="<?= base_url('admin/categories/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Category
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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover data-table" data-empty="No categories found">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Color</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $i => $cat): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <span class="d-inline-block rounded-circle" style="width: 24px; height: 24px; background-color: <?= esc($cat['color'] ?? '#6c757d') ?>;"></span>
                                </td>
                                <td class="fw-semibold"><?= esc($cat['name']) ?></td>
                                <td class="text-muted"><?= esc($cat['description'] ?? '—') ?></td>
                                <td>
                                    <span class="badge <?= $cat['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst(esc($cat['status'])) ?>
                                    </span>
                                </td>
                                <td><?= $cat['created_at'] ? date('M d, Y', strtotime($cat['created_at'])) : '—' ?></td>
                                <td>
                                    <a href="<?= base_url('admin/categories/edit/' . $cat['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="<?= base_url('admin/categories/delete/' . $cat['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete(event, '<?= esc($cat['name'], 'js') ?>');">Delete</a>
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

<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Edit Category</h2>
        </div>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= site_url('admin/categories/edit/' . $category['id']) ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Category Name *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= esc($category['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= esc($category['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="color" class="form-label">Color</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" class="form-control form-control-color" id="color" name="color" value="<?= esc($category['color'] ?? '#6c757d') ?>" title="Choose category color">
                        <span class="text-muted" id="colorHex"><?= esc($category['color'] ?? '#6c757d') ?></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?= $category['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $category['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                    <a href="<?= base_url('admin/categories') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('color').addEventListener('input', function() {
    document.getElementById('colorHex').textContent = this.value;
});
</script>
<?= $this->endSection() ?>

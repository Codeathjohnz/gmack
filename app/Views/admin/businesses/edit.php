<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="<?= base_url('admin/businesses') ?>" class="btn btn-link p-0" style="font-size:1.4rem;"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0">Edit Business</h2>
    </div>

    <div class="card" style="max-width:560px;">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/businesses/edit/' . $business['id']) ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Business Name</label>
                    <input type="text" name="name" class="form-control" value="<?= esc($business['name']) ?>" required minlength="2">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="<?= esc($business['address'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= esc($business['phone'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $business['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $business['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-circle"></i> Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

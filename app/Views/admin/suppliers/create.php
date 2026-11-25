<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="<?= base_url('admin/suppliers') ?>" class="btn btn-link p-0" style="font-size:1.4rem;"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0">Add Supplier</h2>
    </div>

    <div class="card" style="max-width:600px;">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/suppliers/create') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Supplier / Company Name</label>
                    <input type="text" name="name" class="form-control" required minlength="2" value="<?= old('name') ?>">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" value="<?= old('contact_person') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= old('phone') ?>">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= old('email') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= old('address') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Add Supplier</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

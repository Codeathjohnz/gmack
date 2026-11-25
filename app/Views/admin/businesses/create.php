<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="<?= base_url('admin/businesses') ?>" class="btn btn-link p-0" style="font-size:1.4rem;"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0">Add Business</h2>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
    <?php endif; ?>

    <div class="card" style="max-width:560px;">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/businesses/create') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Business / Company Name</label>
                    <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required minlength="2" placeholder="e.g. Ace Hardware & Supply">
                </div>
                <hr>
                <p class="text-muted small mb-2">Its first admin account:</p>
                <div class="mb-3">
                    <label class="form-label">Admin Username</label>
                    <input type="text" name="username" class="form-control" value="<?= old('username') ?>" required minlength="3">
                </div>
                <div class="mb-3">
                    <label class="form-label">Admin Email</label>
                    <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Admin Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Create Business</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h2 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Payment Method</h2>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= site_url('admin/payment-methods/edit/' . $method['id']) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Payment Method Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= esc($method['name']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="ewallet" <?= $method['type'] === 'ewallet' ? 'selected' : '' ?>>E-Wallet</option>
                            <option value="bank" <?= $method['type'] === 'bank' ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="cash" <?= $method['type'] === 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="card" <?= $method['type'] === 'card' ? 'selected' : '' ?>>Card</option>
                            <option value="other" <?= $method['type'] === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="account_name" class="form-label">Account Name</label>
                        <input type="text" class="form-control" id="account_name" name="account_name" value="<?= esc($method['account_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="account_number" class="form-label">Account / Phone Number</label>
                        <input type="text" class="form-control" id="account_number" name="account_number" value="<?= esc($method['account_number'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="icon" class="form-label">Icon</label>
                        <select class="form-select" id="icon" name="icon">
                            <option value="bi-wallet2" <?= ($method['icon'] ?? '') === 'bi-wallet2' ? 'selected' : '' ?>>💳 Wallet</option>
                            <option value="bi-phone" <?= ($method['icon'] ?? '') === 'bi-phone' ? 'selected' : '' ?>>📱 Phone</option>
                            <option value="bi-bank" <?= ($method['icon'] ?? '') === 'bi-bank' ? 'selected' : '' ?>>🏦 Bank</option>
                            <option value="bi-cash-coin" <?= ($method['icon'] ?? '') === 'bi-cash-coin' ? 'selected' : '' ?>>💵 Cash</option>
                            <option value="bi-credit-card" <?= ($method['icon'] ?? '') === 'bi-credit-card' ? 'selected' : '' ?>>💳 Card</option>
                            <option value="bi-qr-code" <?= ($method['icon'] ?? '') === 'bi-qr-code' ? 'selected' : '' ?>>📷 QR Code</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?= $method['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $method['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="qr_code" class="form-label">QR Code Image</label>
                    <input type="file" class="form-control" id="qr_code" name="qr_code" accept="image/*" onchange="previewQR(this)">
                    <small class="text-muted">Upload a new QR code to replace the current one.</small>
                </div>

                <?php if (!empty($method['qr_code'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Current QR Code</label>
                        <div class="text-center p-3 bg-light rounded">
                            <img src="<?= esc(base_url($method['qr_code']), 'attr') ?>" alt="Current QR" style="max-width: 250px; border-radius: 8px; border: 2px solid #dee2e6;">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-3" id="qrPreviewContainer" style="display: none;">
                    <label class="form-label">New QR Code Preview</label>
                    <div class="text-center p-3 bg-light rounded">
                        <img id="qrPreview" src="" alt="QR Preview" style="max-width: 250px; border-radius: 8px; border: 2px solid #dee2e6;">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Payment Method</button>
                    <a href="<?= base_url('admin/payment-methods') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function previewQR(input) {
    const container = document.getElementById('qrPreviewContainer');
    const preview = document.getElementById('qrPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        container.style.display = 'none';
    }
}
</script>
<?= $this->endSection() ?>

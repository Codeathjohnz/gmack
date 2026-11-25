<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Edit Item</h2>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
        $lowThreshold = (int) ($item['low_stock_threshold'] ?? 10);
        $critThreshold = $lowThreshold / 2;
        $qty = (float) ($item['quantity'] ?? 0);
        $unitLabel = ($item['unit'] ?? 'pcs') === 'kilo' ? 'kg' : 'unit(s)';
    ?>
    <?php if ($qty <= $critThreshold): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Critical stock level!</strong>&nbsp;This item has only <?= $qty ?> <?= $unitLabel ?> remaining.
        </div>
    <?php elseif ($qty <= $lowThreshold): ?>
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <strong>Low stock warning!</strong>&nbsp;This item has only <?= $qty ?> <?= $unitLabel ?> remaining.
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= site_url('admin/items/edit/' . $item['id']) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Item Code *</label>
                        <input type="text" class="form-control" id="code" name="code" value="<?= esc($item['code']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Item Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= esc($item['name']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= esc($item['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">-- No Category --</option>
                        <?php foreach (($categories ?? []) as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($item['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= esc($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Item Image</label>
                    <?php if (!empty($item['image'])): ?>
                        <div class="mb-2">
                            <img src="<?= esc(base_url($item['image']), 'attr') ?>" alt="Current image" style="max-height: 120px; border-radius: 8px; border: 1px solid #dee2e6;">
                            <div class="form-text">Current image</div>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="form-text text-muted">Upload a new image to replace the current one (JPG, PNG, GIF, WEBP, max 2MB)</small>
                    <div id="imagePreview" class="mt-2" style="display:none;">
                        <img id="previewImg" src="" alt="Preview" style="max-height: 150px; border-radius: 8px; border: 1px solid #dee2e6;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="price" class="form-label">Price *</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?= esc($item['price']) ?>" required>
                        </div>
                        <small class="form-text text-muted" id="price-unit-hint"><?= ($item['unit'] ?? 'pcs') === 'kilo' ? 'per kilo' : 'per piece' ?></small>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="unit" class="form-label">Unit *</label>
                        <select class="form-select" id="unit" name="unit">
                            <option value="pcs" <?= ($item['unit'] ?? 'pcs') === 'pcs' ? 'selected' : '' ?>>Pieces (pcs)</option>
                            <option value="kilo" <?= ($item['unit'] ?? '') === 'kilo' ? 'selected' : '' ?>>Kilo (kg)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="quantity" class="form-label">Quantity *</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" step="<?= ($item['unit'] ?? 'pcs') === 'kilo' ? '0.01' : '1' ?>" value="<?= esc($item['quantity']) ?>" required>
                            <span class="input-group-text" id="quantity-unit-suffix"><?= ($item['unit'] ?? 'pcs') === 'kilo' ? 'kg' : 'pcs' ?></span>
                        </div>
                        <div id="qty-warning" class="form-text" style="display:none;"></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="low_stock_threshold" class="form-label">Low Stock Alert At</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" min="1" value="<?= esc($item['low_stock_threshold'] ?? 10) ?>" placeholder="10">
                            <span class="input-group-text" id="threshold-unit-suffix"><?= ($item['unit'] ?? 'pcs') === 'kilo' ? 'kg' : 'pcs' ?></span>
                        </div>
                        <small class="form-text text-muted">Alert when quantity drops to this level</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="expiration_date" class="form-label">Expiration Date</label>
                        <input type="date" class="form-control" id="expiration_date" name="expiration_date" value="<?= esc($item['expiration_date'] ?? '') ?>">
                        <small class="form-text text-muted">Leave blank for items that don't expire (tools, hardware, etc.). The POS screen flashes and sounds an alarm once an item passes this date.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $item['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Update Item</button>
                    <a href="<?= base_url('admin/items') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const qtyInput = document.getElementById('quantity');
    const thresholdInput = document.getElementById('low_stock_threshold');
    const qtyWarn = document.getElementById('qty-warning');
    const unitSelect = document.getElementById('unit');
    const quantityUnitSuffix = document.getElementById('quantity-unit-suffix');
    const thresholdUnitSuffix = document.getElementById('threshold-unit-suffix');
    const priceUnitHint = document.getElementById('price-unit-hint');

    // Switch quantity between whole-number (pcs) and fractional (kilo) input
    function applyUnit() {
        const isKilo = unitSelect.value === 'kilo';
        qtyInput.step = isKilo ? '0.01' : '1';
        quantityUnitSuffix.textContent = isKilo ? 'kg' : 'pcs';
        thresholdUnitSuffix.textContent = isKilo ? 'kg' : 'pcs';
        priceUnitHint.textContent = isKilo ? 'per kilo' : 'per piece';
    }
    unitSelect.addEventListener('change', applyUnit);
    applyUnit();

    function checkQty() {
        const val = parseFloat(qtyInput.value) || 0;
        const low = parseFloat(thresholdInput.value) || 10;
        const critical = low / 2;

        if (val <= 0) {
            qtyWarn.style.display = 'block';
            qtyWarn.className = 'form-text text-danger fw-bold';
            qtyWarn.innerHTML = '<i class="bi bi-x-circle-fill"></i> Out of stock!';
        } else if (val <= critical) {
            qtyWarn.style.display = 'block';
            qtyWarn.className = 'form-text text-danger fw-bold';
            qtyWarn.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Critical stock level! (' + critical + ' or less)';
        } else if (val <= low) {
            qtyWarn.style.display = 'block';
            qtyWarn.className = 'form-text text-warning fw-bold';
            qtyWarn.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Low stock warning (' + low + ' or less)';
        } else {
            qtyWarn.style.display = 'none';
        }
    }
    qtyInput.addEventListener('input', checkQty);
    thresholdInput.addEventListener('input', checkQty);
    checkQty();

    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');
        const img = document.getElementById('previewImg');
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('Image must be less than 2MB');
                e.target.value = '';
                preview.style.display = 'none';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(ev) { img.src = ev.target.result; preview.style.display = 'block'; };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
</script>

<?= $this->endSection() ?>

<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<style>
    .item-card {
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .item-card:hover {
        border-color: #d4410e;
        background-color: #fff5f2;
    }
    .item-card.selected {
        border-color: #d4410e;
        background-color: #fff0eb;
        box-shadow: 0 0 0 3px rgba(212, 65, 14, 0.2);
    }
    .item-card .form-check-input:checked {
        background-color: #d4410e;
        border-color: #d4410e;
    }
    .barcode-preview {
        text-align: center;
        padding: 15px;
        background: white;
        border: 1px dashed #ccc;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .barcode-preview svg {
        max-width: 100%;
    }
    .search-box {
        position: relative;
    }
    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }
    .search-box input {
        padding-left: 36px;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-upc-scan"></i> Barcode Generator</h1>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('admin/barcodes/generate') ?>" id="barcodeForm">
        <div class="row">
            <!-- Left: Item Selection -->
            <div class="col-lg-8 col-md-7 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Select Items</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Clear</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="search-box mb-3">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" id="itemSearch" placeholder="Search items by name or code...">
                        </div>

                        <div class="row" id="itemsList">
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <div class="col-lg-6 col-md-6 col-sm-12 item-entry" data-name="<?= esc(strtolower($item['name'])) ?>" data-code="<?= esc(strtolower($item['code'])) ?>">
                                        <label class="item-card d-flex align-items-center gap-3" for="item_<?= $item['id'] ?>">
                                            <input class="form-check-input item-checkbox" type="checkbox" 
                                                   name="item_ids[]" value="<?= $item['id'] ?>" 
                                                   id="item_<?= $item['id'] ?>"
                                                   data-code="<?= esc($item['code']) ?>"
                                                   data-name="<?= esc($item['name']) ?>"
                                                   data-price="<?= number_format($item['price'], 2) ?>">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold"><?= esc($item['name']) ?></div>
                                                <small class="text-muted">
                                                    <code><?= esc($item['code']) ?></code> &middot; ₱<?= number_format($item['price'], 2) ?> &middot; Stock: <?= $item['quantity'] ?>
                                                </small>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center text-muted py-4">
                                    <i class="bi bi-box-seam" style="font-size: 48px;"></i>
                                    <p class="mt-2">No active items found. Add items first.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Settings & Preview -->
            <div class="col-lg-4 col-md-5 mb-3">
                <div class="card sticky-top">
                    <div class="card-header">
                        <h5 class="mb-0">Barcode Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Barcode Format</label>
                            <select class="form-select" name="barcode_format" id="barcodeFormat">
                                <option value="CODE128" selected>CODE 128 (Recommended)</option>
                                <option value="EAN13">EAN-13</option>
                                <option value="UPC">UPC</option>
                                <option value="CODE39">CODE 39</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Copies per Item</label>
                            <input type="number" class="form-control" name="quantity" id="barcodeQty" value="1" min="1" max="50">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Selected: <span id="selectedCount" class="text-primary">0</span> item(s)</label>
                        </div>

                        <!-- Preview -->
                        <div id="previewArea">
                            <label class="form-label fw-semibold">Preview</label>
                            <div id="barcodePreview" class="barcode-preview">
                                <p class="text-muted mb-0"><i class="bi bi-upc-scan" style="font-size: 40px;"></i><br>Select an item to preview</p>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3" id="generateBtn" disabled>
                            <i class="bi bi-printer"></i> Generate & Print Barcodes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const generateBtn = document.getElementById('generateBtn');
    const previewArea = document.getElementById('barcodePreview');
    const searchInput = document.getElementById('itemSearch');

    function updateState() {
        const checked = document.querySelectorAll('.item-checkbox:checked');
        selectedCount.textContent = checked.length;
        generateBtn.disabled = checked.length === 0;

        // Update card styles
        checkboxes.forEach(cb => {
            cb.closest('.item-card').classList.toggle('selected', cb.checked);
        });

        // Update preview with last checked item
        if (checked.length > 0) {
            const last = checked[checked.length - 1];
            const code = last.dataset.code;
            const name = last.dataset.name;
            const price = last.dataset.price;
            previewArea.innerHTML = `
                <svg id="previewBarcode"></svg>
                <div class="mt-2"><strong>${name}</strong></div>
                <div class="text-muted">₱${price}</div>
            `;
            try {
                JsBarcode("#previewBarcode", code, {
                    format: document.getElementById('barcodeFormat').value,
                    width: 2,
                    height: 60,
                    displayValue: true,
                    fontSize: 14,
                    margin: 5
                });
            } catch (e) {
                previewArea.innerHTML = `<p class="text-danger">Invalid barcode format for code: ${code}</p>`;
            }
        } else {
            previewArea.innerHTML = '<p class="text-muted mb-0"><i class="bi bi-upc-scan" style="font-size: 40px;"></i><br>Select an item to preview</p>';
        }
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateState));

    document.getElementById('barcodeFormat').addEventListener('change', updateState);

    document.getElementById('selectAll').addEventListener('click', function() {
        document.querySelectorAll('.item-entry:not([style*="display: none"]) .item-checkbox').forEach(cb => cb.checked = true);
        updateState();
    });

    document.getElementById('deselectAll').addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = false);
        updateState();
    });

    // Search filter
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.item-entry').forEach(entry => {
            const name = entry.dataset.name;
            const code = entry.dataset.code;
            entry.style.display = (name.includes(query) || code.includes(query)) ? '' : 'none';
        });
    });
});
</script>

<?= $this->endSection() ?>

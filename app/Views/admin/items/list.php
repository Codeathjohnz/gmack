<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<style>
    .stock-badge { font-size: 11px; padding: 3px 8px; }
    .stock-critical { background-color: #dc3545 !important; animation: pulse-critical 1.5s infinite; }
    .stock-low { background-color: #fd7e14 !important; }
    .stock-ok { background-color: #198754 !important; }
    .stock-out { background-color: #6c757d !important; }
    @keyframes pulse-critical {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    .low-stock-alert {
        border-left: 4px solid #dc3545;
        background: #fff5f5;
        border-radius: 6px;
        padding: 8px 14px;
        margin-bottom: 10px;
    }
    .low-stock-alert .alert-icon { font-size: 18px; margin-right: 8px; }
    .low-stock-summary {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .low-stock-summary .summary-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }
    tr.row-critical { background-color: #fff5f5 !important; }
    tr.row-low { background-color: #fff8f0 !important; }
    tr.row-critical:hover { background-color: #ffe8e8 !important; }
    tr.row-low:hover { background-color: #fff0e0 !important; }
    tr[data-category-color] { border-left: 6px solid var(--cat-color, transparent); }
    tr.cat-colored { transition: background-color 0.3s, border-color 0.3s; }
    tr.cat-colored td { background-color: inherit !important; }
    .item-thumb {
        width: 36px;
        height: 36px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .item-thumb:hover { transform: scale(1.1); box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
    #itemsTable { font-size: 13px; }
    #itemsTable td, #itemsTable th { padding: 6px 8px; vertical-align: middle; }
    #itemsTable thead th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px; }
    /* Keep long item names from stretching the whole table wide — a compact
       table means less (or no) horizontal scroll on typical screens. */
    #itemsTable td:nth-child(4) { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    #itemsTable td:nth-child(4):hover { white-space: normal; overflow: visible; }
    .discount-input { text-align: center; }
    .discount-preview { line-height: 1.3; }
    .discount-applied { animation: discountFlash 0.5s ease; }
    @keyframes discountFlash { 0%,100% { background: transparent; } 50% { background: #d4edda; } }
    .no-image {
        width: 36px;
        height: 36px;
        border-radius: 4px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #aaa;
        font-size: 16px;
    }
</style>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-1">
        <h5 class="mb-0 fw-bold">Manage Items</h5>
        <div class="d-flex gap-2">
            <a href="<?= base_url('admin/items/import') ?>" class="btn btn-secondary btn-sm text-white">
                <i class="bi bi-file-earmark-arrow-up"></i> Bulk Import
            </a>
            <a href="<?= base_url('admin/items/create') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Item
            </a>
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

    <!-- Low Stock Alert Banner -->
    <?php if (($critical_stock_count ?? 0) > 0 || ($low_stock_count ?? 0) > 0): ?>
        <div class="low-stock-alert">
            <div class="d-flex align-items-center mb-2">
                <span class="alert-icon"><i class="bi bi-exclamation-triangle-fill text-danger"></i></span>
                <strong>Stock Alert</strong>
            </div>
            <div class="low-stock-summary">
                <?php if (($critical_stock_count ?? 0) > 0): ?>
                    <div class="summary-item text-danger">
                        <i class="bi bi-x-circle-fill"></i>
                        <?= $critical_stock_count ?> item(s) critically low
                    </div>
                <?php endif; ?>
                <?php if (($low_stock_count ?? 0) > 0): ?>
                    <div class="summary-item text-warning">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <?= $low_stock_count ?> item(s) low stock
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <!-- Filters: a real GET form — the server does the filtering/sorting/paging now,
                 so the browser never has to hold more than one page's worth of rows. -->
            <form method="get" action="<?= base_url('admin/items') ?>" id="filterForm" class="row g-2 mb-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                        <input type="text" class="form-control" name="search" id="searchFilter" placeholder="Search by name or code..." style="padding-left: 36px;" value="<?= esc($search ?? '') ?>">
                    </div>
                </div>
                <div class="col-6 col-sm-3 col-md-2">
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-6 col-sm-3 col-md-2">
                    <select class="form-select" name="stock" id="stockFilter">
                        <option value="">All Stock</option>
                        <option value="in-stock" <?= ($stock ?? '') === 'in-stock' ? 'selected' : '' ?>>In Stock</option>
                        <option value="low" <?= ($stock ?? '') === 'low' ? 'selected' : '' ?>>Low Stock</option>
                        <option value="critical" <?= ($stock ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                        <option value="out" <?= ($stock ?? '') === 'out' ? 'selected' : '' ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="col-6 col-sm-3 col-md-2">
                    <select class="form-select" name="sort" id="sortFilter">
                        <option value="name-asc" <?= ($sort ?? 'name-asc') === 'name-asc' ? 'selected' : '' ?>>Name A-Z</option>
                        <option value="name-desc" <?= ($sort ?? '') === 'name-desc' ? 'selected' : '' ?>>Name Z-A</option>
                        <option value="price-asc" <?= ($sort ?? '') === 'price-asc' ? 'selected' : '' ?>>Price Low-High</option>
                        <option value="price-desc" <?= ($sort ?? '') === 'price-desc' ? 'selected' : '' ?>>Price High-Low</option>
                        <option value="qty-asc" <?= ($sort ?? '') === 'qty-asc' ? 'selected' : '' ?>>Qty Low-High</option>
                        <option value="qty-desc" <?= ($sort ?? '') === 'qty-desc' ? 'selected' : '' ?>>Qty High-Low</option>
                    </select>
                </div>
                <div class="col-6 col-sm-3 col-md-2">
                    <a href="<?= base_url('admin/items') ?>" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Clear</a>
                </div>
            </form>
            <div class="mb-1 text-muted" style="font-size:11px;">
                <?php if (!empty($totalItems)): ?>
                    Showing <?= number_format($showingStart) ?>–<?= number_format($showingEnd) ?> of <?= number_format($totalItems) ?> item(s)
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" id="bulkActionBar" style="display:none !important; background: var(--concrete-100);">
                <strong id="bulkSelectedCount">0 selected</strong>
                <button type="button" class="btn btn-danger btn-sm ms-auto" id="bulkDeleteBtn">
                    <i class="bi bi-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="bulkClearBtn">Clear</button>
            </div>

            <form method="post" action="<?= base_url('admin/items/bulk-delete') ?>" id="bulkDeleteForm">
                <?= csrf_field() ?>
                <div class="table-responsive">
                <table class="table table-hover" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width:32px;"><input type="checkbox" id="selectAllItems" title="Select all on this page"></th>
                            <th>Image</th>
                            <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Stock</th>
                        <th style="width:44px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">No items found</td>
                        </tr>
                    <?php else: ?>
                        <?php
                            // Build category color map once
                            $catColorMap = [];
                            foreach ($categories as $cat) {
                                $catColorMap[$cat['id']] = $cat['color'] ?? '#6c757d';
                            }
                        ?>
                        <?php foreach ($items as $item): ?>
                            <?php
                                $qty = (float) $item['quantity'];
                                $itemIsKilo = ($item['unit'] ?? 'pcs') === 'kilo';
                                $qtyDisplay = $itemIsKilo ? number_format($qty, 2) . ' kg' : number_format($qty, 0) . ' pcs';
                                $lowThreshold = (int) ($item['low_stock_threshold'] ?? 10);
                                $critThreshold = $lowThreshold / 2;

                                if ($qty <= 0) {
                                    $stockClass = 'stock-out';
                                    $stockLabel = 'Out of Stock';
                                    $stockIcon = 'bi-x-circle-fill';
                                    $rowClass = 'row-critical';
                                } elseif ($qty <= $critThreshold) {
                                    $stockClass = 'stock-critical';
                                    $stockLabel = 'Critical';
                                    $stockIcon = 'bi-exclamation-triangle-fill';
                                    $rowClass = 'row-critical';
                                } elseif ($qty <= $lowThreshold) {
                                    $stockClass = 'stock-low';
                                    $stockLabel = 'Low Stock';
                                    $stockIcon = 'bi-exclamation-circle-fill';
                                    $rowClass = 'row-low';
                                } else {
                                    $stockClass = 'stock-ok';
                                    $stockLabel = 'In Stock';
                                    $stockIcon = 'bi-check-circle-fill';
                                    $rowClass = '';
                                }
                                $itemCatColor = isset($item['category_id'], $catColorMap[$item['category_id']]) ? $catColorMap[$item['category_id']] : '';
                                // Convert hex to rgba for background tint
                                $itemCatBg = '';
                                if ($itemCatColor) {
                                    $r = hexdec(substr($itemCatColor, 1, 2));
                                    $g = hexdec(substr($itemCatColor, 3, 2));
                                    $b = hexdec(substr($itemCatColor, 5, 2));
                                    $itemCatBg = "rgba($r, $g, $b, 0.35)";
                                }
                            ?>
                            <tr class="<?= $rowClass ?> <?= $itemCatColor ? 'cat-colored' : '' ?>"
                                data-category-color="<?= esc($itemCatColor) ?>"
                                style="<?= $itemCatColor ? '--cat-color: ' . esc($itemCatColor) . '; background-color: ' . $itemCatBg . ';' : '' ?>"
                                data-name="<?= esc(strtolower($item['name'])) ?>"
                                data-code="<?= esc(strtolower($item['code'])) ?>"
                                data-status="<?= esc($item['status']) ?>"
                                data-stock="<?= $qty <= 0 ? 'out' : ($qty <= $critThreshold ? 'critical' : ($qty <= $lowThreshold ? 'low' : 'in-stock')) ?>"
                                data-price="<?= $item['price'] ?>"
                                data-qty="<?= $qty ?>">
                                <td><input type="checkbox" class="item-checkbox" name="item_ids[]" value="<?= $item['id'] ?>" form="bulkDeleteForm"></td>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?= esc(base_url($item['image']), 'attr') ?>" alt="<?= esc($item['name']) ?>" class="item-thumb" onclick="viewImage(this.src, '<?= esc($item['name']) ?>')">
                                    <?php else: ?>
                                        <div class="no-image"><i class="bi bi-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($item['code']) ?></td>
                                <td>
                                    <?= esc($item['name']) ?>
                                    <?php if ($item['status'] !== 'active'): ?>
                                        <span class="badge bg-secondary" style="font-size:9px;vertical-align:middle;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm category-select" data-item-id="<?= $item['id'] ?>" style="min-width: 90px; font-size: 12px; padding: 2px 4px;">
                                        <option value="">-- None --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" 
                                                style="color: <?= esc($cat['color'] ?? '#6c757d') ?>; font-weight: 600;"
                                                <?= ($item['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                                <?= esc($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="price-cell">
                                    <?php
                                        $discount = (float) ($item['discount'] ?? 0);
                                        $originalPrice = (float) $item['price'];
                                        $discountedPrice = $originalPrice * (1 - $discount / 100);
                                    ?>
                                    <?php if ($discount > 0): ?>
                                        <span class="text-decoration-line-through text-muted small">₱<?= number_format($originalPrice, 2) ?></span><br>
                                        <span class="fw-bold text-success">₱<?= number_format($discountedPrice, 2) ?></span>
                                    <?php else: ?>
                                        ₱<?= number_format($originalPrice, 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="input-group input-group-sm" style="width: 100px;">
                                            <input type="number" class="form-control discount-input" 
                                                data-item-id="<?= $item['id'] ?>" 
                                                data-original-price="<?= $originalPrice ?>"
                                                value="<?= $discount ?>" 
                                                min="0" max="100" step="0.5" 
                                                style="width: 50px; font-size: 12px; padding: 2px 4px;">
                                            <span class="input-group-text" style="font-size: 11px; padding: 2px 5px;">%</span>
                                        </div>
                                        <button class="btn btn-sm btn-outline-success discount-apply-btn" data-item-id="<?= $item['id'] ?>" title="Apply Discount">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </div>
                                    <div class="discount-preview small mt-1" id="discount-preview-<?= $item['id'] ?>" style="display: none;">
                                        <span class="text-muted">Preview:</span>
                                        <span class="text-decoration-line-through text-muted"></span>
                                        → <span class="fw-bold text-success"></span>
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <span class="badge stock-badge <?= $stockClass ?>">
                                        <i class="bi <?= $stockIcon ?>"></i> <?= $qtyDisplay ?>
                                    </span>
                                    <div class="text-muted" style="font-size:10px;"><?= $stockLabel ?></div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm py-0 px-2" type="button" data-bs-toggle="dropdown" data-bs-strategy="fixed" aria-expanded="false" title="Actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" style="font-size:13px;">
                                            <li><a class="dropdown-item" href="<?= base_url('admin/items/edit/' . $item['id']) ?>"><i class="bi bi-pencil-square"></i> Edit</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="openAdjustModal('<?= $item['id'] ?>', '<?= esc($item['name'], 'js') ?>', <?= (float) $item['quantity'] ?>); return false;"><i class="bi bi-arrow-left-right"></i> Adjust Stock</a></li>
                                            <li><a class="dropdown-item" href="<?= base_url('admin/stock-movements/item/' . $item['id']) ?>"><i class="bi bi-clock-history"></i> Stock History</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="<?= base_url('admin/items/delete/' . $item['id']) ?>" onclick="return confirmDelete(event, '<?= esc($item['name'], 'js') ?>');"><i class="bi bi-trash"></i> Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
            </form>

            <?php
                $pageCount = isset($pager) ? $pager->getPageCount('items') : 1;
                $currentPage = isset($pager) ? $pager->getCurrentPage('items') : 1;
                $pageUrl = function (int $p) use ($search, $status, $stock, $sort) {
                    // CodeIgniter's Pager reads the page number from a
                    // group-qualified query key — "page_items" for the
                    // 'items' group paginate() uses below, not plain "page".
                    // The link previously used "page", which the pager never
                    // read, so Next/page-number links changed the URL but
                    // always re-served page 1.
                    return base_url('admin/items') . '?' . http_build_query(array_filter([
                        'search'     => $search ?? '',
                        'status'     => $status ?? '',
                        'stock'      => $stock ?? '',
                        'sort'       => $sort ?? '',
                        'page_items' => $p,
                    ], fn($v) => $v !== ''));
                };
            ?>
            <?php if ($pageCount > 1): ?>
                <nav aria-label="Items pagination" class="mt-3">
                    <ul class="pagination justify-content-center flex-wrap mb-0">
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $pageUrl(max(1, $currentPage - 1)) ?>">&laquo; Prev</a>
                        </li>
                        <?php for ($p = 1; $p <= $pageCount; $p++): ?>
                            <?php if ($p === 1 || $p === $pageCount || abs($p - $currentPage) <= 2): ?>
                                <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $pageUrl($p) ?>"><?= $p ?></a>
                                </li>
                            <?php elseif ($p === 2 || $p === $pageCount - 1): ?>
                                <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <li class="page-item <?= $currentPage >= $pageCount ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $pageUrl(min($pageCount, $currentPage + 1)) ?>">Next &raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="<?= base_url('admin/stock-movements/adjust') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" id="adjustItemId">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right"></i> Adjust Stock — <span id="adjustItemName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Current quantity: <strong id="adjustCurrentQty"></strong>. Use this to correct counts for damage, loss, or a recount — every adjustment is logged.</p>
                <div class="mb-3">
                    <label class="form-label">Adjustment</label>
                    <div class="input-group">
                        <span class="input-group-text">±</span>
                        <input type="number" step="any" name="quantity_delta" class="form-control" placeholder="e.g. -5 or 20" required>
                    </div>
                    <small class="text-muted">Positive adds stock, negative removes it.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <select class="form-select mb-2" id="adjustReasonSelect">
                        <option value="Recount">Recount / stock take</option>
                        <option value="Damaged">Damaged / spoiled</option>
                        <option value="Lost">Lost / stolen</option>
                        <option value="Correction">Data-entry correction</option>
                        <option value="Other">Other (specify)</option>
                    </select>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Details (required)" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Adjustment</button>
            </div>
        </form>
    </div>
</div>

<!-- Image Zoom Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Item Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="modalImage" src="" alt="" style="max-width: 100%; max-height: 70vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>

<script>
function openAdjustModal(itemId, itemName, currentQty) {
    document.getElementById('adjustItemId').value = itemId;
    document.getElementById('adjustItemName').textContent = itemName;
    document.getElementById('adjustCurrentQty').textContent = currentQty;
    new bootstrap.Modal(document.getElementById('adjustStockModal')).show();
}
document.getElementById('adjustReasonSelect')?.addEventListener('change', function () {
    var textarea = this.closest('.mb-3').querySelector('textarea[name="reason"]');
    if (this.value !== 'Other' && !textarea.value) {
        textarea.value = this.options[this.selectedIndex].text;
    }
});

function viewImage(src, name) {
    document.getElementById('modalImage').src = src;
    document.getElementById('modalImage').alt = name;
    document.getElementById('imageModalLabel').textContent = name;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

// Helper: hex to rgba string
function hexToRgba(hex, alpha) {
    const r = parseInt(hex.substring(1,3), 16);
    const g = parseInt(hex.substring(3,5), 16);
    const b = parseInt(hex.substring(5,7), 16);
    return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
}

// Category color map for JS
const categoryColors = <?= json_encode($catColorMap ?? []) ?>;

// Discount preview on input change
document.querySelectorAll('.discount-input').forEach(function(input) {
    input.addEventListener('input', function() {
        const itemId = this.dataset.itemId;
        const originalPrice = parseFloat(this.dataset.originalPrice);
        let discount = parseFloat(this.value) || 0;
        if (discount < 0) discount = 0;
        if (discount > 100) discount = 100;
        
        const preview = document.getElementById('discount-preview-' + itemId);
        if (discount > 0 && discount !== parseFloat(this.defaultValue)) {
            const discountedPrice = originalPrice * (1 - discount / 100);
            preview.style.display = 'block';
            preview.querySelector('.text-decoration-line-through').textContent = '₱' + originalPrice.toFixed(2);
            preview.querySelector('.fw-bold').textContent = '₱' + discountedPrice.toFixed(2);
        } else {
            preview.style.display = 'none';
        }
    });
});

// Apply discount button
document.querySelectorAll('.discount-apply-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        const input = document.querySelector('.discount-input[data-item-id="' + itemId + '"]');
        let discount = parseFloat(input.value) || 0;
        if (discount < 0 || discount > 100) {
            alert('Discount must be between 0% and 100%');
            return;
        }
        
        btn.disabled = true;
        input.disabled = true;

        fetch('<?= base_url('admin/items/update-discount') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'item_id=' + encodeURIComponent(itemId) + '&discount=' + encodeURIComponent(discount) + '&<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>')
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            input.disabled = false;
            if (data.success) {
                // Update the price cell
                const row = input.closest('tr');
                const priceCell = row.querySelector('.price-cell');
                if (data.discount > 0) {
                    priceCell.innerHTML = '<span class="text-decoration-line-through text-muted small">₱' + data.original_price.toFixed(2) + '</span><br><span class="fw-bold text-success">₱' + data.discounted_price.toFixed(2) + '</span>';
                } else {
                    priceCell.innerHTML = '₱' + data.original_price.toFixed(2);
                }
                // Update default value and hide preview
                input.defaultValue = discount;
                document.getElementById('discount-preview-' + itemId).style.display = 'none';
                // Flash success
                btn.classList.replace('btn-outline-success', 'btn-success');
                row.classList.add('discount-applied');
                setTimeout(() => {
                    btn.classList.replace('btn-success', 'btn-outline-success');
                    row.classList.remove('discount-applied');
                }, 1500);
            } else {
                alert(data.error || 'Failed to update discount');
            }
        })
        .catch(() => {
            btn.disabled = false;
            input.disabled = false;
            alert('Failed to update discount');
        });
    });
});

// Category inline update
document.querySelectorAll('.category-select').forEach(function(select) {
    select.addEventListener('change', function() {
        const itemId = this.dataset.itemId;
        const categoryId = this.value;
        const el = this;
        const row = el.closest('tr');
        el.disabled = true;

        // Update row color immediately
        const color = categoryId ? (categoryColors[categoryId] || '') : '';
        if (color) {
            row.style.setProperty('--cat-color', color);
            row.style.backgroundColor = hexToRgba(color, 0.35);
            row.dataset.categoryColor = color;
            row.classList.add('cat-colored');
        } else {
            row.style.removeProperty('--cat-color');
            row.style.backgroundColor = '';
            row.dataset.categoryColor = '';
            row.classList.remove('cat-colored');
        }

        fetch('<?= base_url('admin/items/update-category') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'item_id=' + encodeURIComponent(itemId) + '&category_id=' + encodeURIComponent(categoryId) + '&<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>')
        })
        .then(r => r.json())
        .then(data => {
            el.disabled = false;
            if (data.success) {
                el.classList.add('border-success');
                setTimeout(() => el.classList.remove('border-success'), 1500);
            } else {
                alert('Failed to update category');
            }
        })
        .catch(() => {
            el.disabled = false;
            alert('Failed to update category');
        });
    });
});

// ---- Filters: search debounces then submits; dropdowns submit immediately ----
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchFilter');
    let debounceTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => form.submit(), 500);
    });

    ['statusFilter', 'stockFilter', 'sortFilter'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', () => form.submit());
    });
});

// ---- Bulk selection & delete ----
(function () {
    const selectAll = document.getElementById('selectAllItems');
    const bar = document.getElementById('bulkActionBar');
    const countLabel = document.getElementById('bulkSelectedCount');
    const deleteBtn = document.getElementById('bulkDeleteBtn');
    const clearBtn = document.getElementById('bulkClearBtn');
    const form = document.getElementById('bulkDeleteForm');

    function checkboxes() {
        return Array.from(document.querySelectorAll('.item-checkbox'));
    }
    function checkedBoxes() {
        return checkboxes().filter(cb => cb.checked);
    }

    function refresh() {
        const checked = checkedBoxes();
        if (checked.length > 0) {
            bar.style.setProperty('display', 'flex', 'important');
            countLabel.textContent = checked.length + ' selected';
        } else {
            bar.style.setProperty('display', 'none', 'important');
        }

        const all = checkboxes();
        selectAll.checked = all.length > 0 && all.every(cb => cb.checked);
        selectAll.indeterminate = all.some(cb => cb.checked) && !selectAll.checked;
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('item-checkbox')) refresh();
    });

    selectAll.addEventListener('change', function () {
        checkboxes().forEach(cb => { cb.checked = selectAll.checked; });
        refresh();
    });

    clearBtn.addEventListener('click', function () {
        checkboxes().forEach(cb => { cb.checked = false; });
        refresh();
    });

    deleteBtn.addEventListener('click', function () {
        const checked = checkedBoxes();
        if (checked.length === 0) return;

        const settings = {
            title: 'Delete ' + checked.length + ' item(s)?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete them',
            confirmButtonColor: '#ff6b00',
            cancelButtonColor: '#5b6672',
        };

        if (window.Swal) {
            Swal.fire(settings).then(function (result) {
                if (result.isConfirmed) form.submit();
            });
        } else if (window.confirm(settings.title)) {
            form.submit();
        }
    });

    refresh();
})();
</script>

<?= $this->endSection() ?>

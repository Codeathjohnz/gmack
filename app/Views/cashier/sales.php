<?= $this->extend('layout') ?>

<!-- Alarm control renders inside the shared header (see layout.php's
     navbar_extra slot) instead of floating over the page -->
<?= $this->section('navbar_extra') ?>
<button id="stopSoundBtn" class="stop-sound-btn" onclick="toggleAlarm()">
    <i class="bi bi-volume-mute-fill" id="stopSoundBtnIcon"></i> <span id="stopSoundBtnLabel">Stop Sound</span>
</button>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .search-container { margin-bottom: 20px; }
    .voice-search-btn { 
        background: linear-gradient(135deg, #ffc400 0%, #ff6b00 32%, #d95700 68%, #14181c 100%);
        border: none;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: transform 0.2s;
    }
    .voice-search-btn:hover { transform: scale(1.05); }
    .voice-search-btn.listening { background: #ff6b6b; animation: pulse 1s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
        to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }
    
    /* Expired Item Animations */
    .expired-item {
        animation: expiredFlash 0.6s infinite !important;
    }
    
    @keyframes expiredFlash {
        0%, 100% {
            background-color: #ffe6e6;
            border-left: 5px solid #ff6b6b;
        }
        50% {
            background-color: #ffcccc;
            border-left: 5px solid #ff1744;
        }
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes expiredPulse {
        0%, 100% { color: #ff1744; text-shadow: 0 0 10px rgba(255, 23, 68, 0.5); }
        50% { color: #ff6b6b; text-shadow: 0 0 20px rgba(255, 107, 107, 0.8); }
    }
    
    .expired-badge {
        background-color: #ff1744;
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
        animation: expiredPulse 1s infinite;
    }
    
    .expiring-soon-badge { 
        background-color: #ffa94d;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .items-table { font-size: 14px; }
    .items-table tbody tr { cursor: pointer; transition: background-color 0.2s; }
    .items-table tbody tr:hover { background-color: #f8f9fa; }
    
    /* Alarm Control Panel */
    .alarm-panel {
        display: none;
    }
    
    /* Stop Alarm Button — lives inline in the shared navbar (see the
       navbar_extra section above) instead of floating over the page. */
    .stop-sound-btn {
        display: none;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #ff1744 0%, #c41c3b 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 700;
        font-size: 13px;
        box-shadow: 0 3px 10px rgba(255, 23, 68, 0.35);
        animation: pulse 1.4s ease-in-out infinite;
        transition: transform 0.2s, box-shadow 0.2s;
        white-space: nowrap;
    }

    .stop-sound-btn:hover {
        background: linear-gradient(135deg, #c41c3b 0%, #ff1744 100%);
        transform: scale(1.05);
        box-shadow: 0 4px 14px rgba(255, 23, 68, 0.5);
    }

    .stop-sound-btn.active {
        display: inline-flex;
    }
    
    /* Mobile Sticky Checkout Bar — hidden on desktop, shown (and toggled by JS
       once the cart has items) as a fixed bar on phones/tablets so checkout
       is always one tap away after searching/selecting an item. */
    .mobile-checkout-bar {
        display: none;
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1045;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 14px;
        padding-bottom: calc(10px + env(safe-area-inset-bottom));
        background: #fff;
        border-top: 1px solid #e0e0e0;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.15);
    }
    .mobile-checkout-bar.show { display: flex; }
    .mobile-checkout-info { display: flex; flex-direction: column; line-height: 1.2; flex-shrink: 0; }
    .mobile-checkout-count { font-size: 11px; color: #666; font-weight: 600; }
    .mobile-checkout-total { font-size: 18px; font-weight: 800; color: #14181c; }
    .mobile-checkout-btn { white-space: nowrap; font-weight: 700; padding: 10px 16px; }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .stop-sound-btn {
            padding: 7px 12px;
            font-size: 11px;
        }

        .search-container {
            margin-bottom: 15px;
        }

        .voice-search-btn {
            padding: 8px 12px;
            font-size: 14px;
        }

        .card-body {
            padding: 12px;
        }

        .items-table {
            font-size: 12px;
        }

        .items-table th, .items-table td {
            padding: 8px 6px;
        }

        /* Cart no longer needs to stick — it stacks below the item list on
           phones, and the floating checkout bar handles quick access instead. */
        #cartSection .sticky-top {
            position: static !important;
        }

        /* Leave room at the bottom so the fixed checkout bar never covers content */
        body {
            padding-bottom: 64px;
        }
    }

    @media (max-width: 480px) {
        .container-fluid {
            margin-top: 10px;
        }

        h2 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        /* Icon-only on very small screens to keep the navbar from crowding */
        .stop-sound-btn {
            padding: 7px 10px;
        }
        .stop-sound-btn #stopSoundBtnLabel {
            display: none;
        }

        .items-table {
            font-size: 11px;
        }

        .items-table th, .items-table td {
            padding: 6px 4px;
        }

        .col-lg-8, .col-lg-4, .col-md-5, .col-md-7 {
            margin-bottom: 10px;
        }

        .mobile-checkout-total { font-size: 16px; }
        .mobile-checkout-btn { font-size: 13px; padding: 9px 12px; }
    }
</style>

<div class="container-fluid mt-4">
    <h2 class="mb-4">Point of Sale - Cashier</h2>

    <div class="row mt-3">
        <!-- Items List -->
        <div class="col-lg-8 col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-3">Available Items</h5>
                    
                    <!-- Search Bar with Voice Search -->
                    <div class="search-container">
                        <div class="input-group">
                            <input 
                                type="text" 
                                id="searchInput" 
                                class="form-control" 
                                placeholder="Search items by name, code, or description..."
                                onkeyup="filterItems()"
                            >
                            <button class="btn btn-primary voice-search-btn" id="voiceSearchBtn" onclick="startVoiceSearch()" title="Click to use voice search">
                                Mic
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">Tip: Click the Mic button to search by voice</small>
                    </div>
                </div>

                <div class="card-body" style="max-height: 500px; overflow-y: auto; overflow-x: auto; padding: 0;">
                    <div style="min-width: 100%; display: inline-block;">
                    <table class="table table-hover items-table" id="itemsTable" style="margin-bottom: 0;">
                        <thead class="table-light">
                            <tr>
                                <th class="d-none d-md-table-cell" style="width: 15%">Code</th>
                                <th style="width: 40%">Name</th>
                                <th style="width: 15%">Stock</th>
                                <th style="width: 15%">Price</th>
                                <th class="d-none d-md-table-cell" style="width: 15%">Expires</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <span class="text-muted">No items available</span>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                        $today = date('Y-m-d');
                                        $expiration = $item['expiration_date'] ?? null;
                                        $daysUntilExpiry = null;
                                        $isExpired = false;
                                        $expiringBadge = '';
                                        $rowClass = '';

                                        if ($expiration) {
                                            $expireDate = new DateTime($expiration);
                                            $todayDate = new DateTime($today);
                                            $interval = $todayDate->diff($expireDate);
                                            $daysUntilExpiry = $interval->format('%r%a');
                                            $isExpired = $daysUntilExpiry < 0;

                                            if ($isExpired) {
                                                $expiringBadge = '<span class="expired-badge">⚠️ EXPIRED</span>';
                                                $rowClass = 'expired-item';
                                            } elseif ($daysUntilExpiry <= 30) {
                                                $expiringBadge = '<span class="expiring-soon-badge">⏰ EXPIRING SOON</span>';
                                            }
                                        }
                                    ?>
                                    <?php
                                        $itemDiscount = (float) ($item['discount'] ?? 0);
                                        $itemOriginalPrice = (float) $item['price'];
                                        $itemSellingPrice = $itemOriginalPrice * (1 - $itemDiscount / 100);
                                        $itemLowThreshold = (float) ($item['low_stock_threshold'] ?? 10);
                                        $isLowStock = (float) $item['quantity'] <= $itemLowThreshold;
                                        $itemUnitLabel = ($item['unit'] ?? 'pcs') === 'kilo' ? 'kg' : '';
                                    ?>
                                    <tr class="item-row <?= $rowClass ?>" onclick="addToCart(<?= $item['id'] ?>, '<?= esc($item['code']) ?>', '<?= esc($item['name']) ?>', <?= round($itemSellingPrice, 2) ?>, <?= $item['quantity'] ?>, <?= $itemDiscount ?>, <?= $itemOriginalPrice ?>)" data-code="<?= strtolower($item['code']) ?>" data-name="<?= strtolower($item['name']) ?>" data-description="<?= strtolower($item['description'] ?? '') ?>" data-expired="<?= $isExpired ? 'true' : 'false' ?>" data-low-stock="<?= $isLowStock ? 'true' : 'false' ?>">
                                        <td class="d-none d-md-table-cell"><code><?= esc($item['code']) ?></code></td>
                                        <td>
                                            <?= esc($item['name']) ?>
                                            <div class="d-md-none text-muted" style="font-size: 11px;">
                                                <code><?= esc($item['code']) ?></code>
                                                <?php if ($expiringBadge): ?> · <?= $expiringBadge ?><?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($isLowStock): ?>
                                                <span class="badge bg-warning">⚠️ <?= $item['quantity'] ?> <?= $itemUnitLabel ?> LOW</span>
                                            <?php else: ?>
                                                <?= $item['quantity'] ?> <?= $itemUnitLabel ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($itemDiscount > 0): ?>
                                                <span class="text-decoration-line-through text-muted" style="font-size:11px;">₱<?= number_format($itemOriginalPrice, 2) ?></span><br>
                                                <strong class="text-success">₱<?= number_format($itemSellingPrice, 2) ?></strong>
                                                <span class="badge bg-danger" style="font-size:9px;">-<?= $itemDiscount ?>%</span>
                                            <?php else: ?>
                                                <strong>₱<?= number_format($itemOriginalPrice, 2) ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <?php if ($expiringBadge): ?>
                                                <?= $expiringBadge ?>
                                                <br><small><?= $expiration ? date('M d, Y', strtotime($expiration)) : 'N/A' ?></small>
                                            <?php else: ?>
                                                <small><?= $expiration ? date('M d, Y', strtotime($expiration)) : 'No expiry' ?></small>
                                            <?php endif; ?>
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

        <!-- Shopping Cart -->
        <div class="col-lg-4 col-md-5 mt-3 mt-md-0" id="cartSection">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header" style="background: linear-gradient(135deg, #ffc400 0%, #ff6b00 32%, #d95700 68%, #14181c 100%); color: white;">
                    <h5 class="mb-0">🛒 Shopping Cart</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm" id="cartTable">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartBody">
                        </tbody>
                    </table>
                </div>
                <div class="card-footer" id="checkoutSection">
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="subtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <span id="discountDisplay" class="text-danger">-₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-top pt-2">
                            <span><strong>Total:</strong></span>
                            <span id="total" class="h5 text-primary"><strong>₱0.00</strong></span>
                        </div>
                        <div class="mb-3">
                            <label for="paymentMethod" class="form-label">Payment Method:</label>
                            <select id="paymentMethod" class="form-select form-select-sm" onchange="updatePaymentFields()">
                                <option value="cash" data-type="cash">💵 Cash</option>
                                <?php if (!empty($paymentMethods)): ?>
                                    <?php foreach ($paymentMethods as $pm): ?>
                                        <option value="<?= esc($pm['name']) ?>" 
                                                data-type="<?= esc($pm['type']) ?>"
                                                data-account-name="<?= esc($pm['account_name'] ?? '') ?>"
                                                data-account-number="<?= esc($pm['account_number'] ?? '') ?>"
                                                data-qr="<?= !empty($pm['qr_code']) ? esc(base_url($pm['qr_code']), 'attr') : '' ?>"
                                                data-icon="<?= esc($pm['icon'] ?? 'bi-wallet2') ?>">
                                            <i class="bi <?= esc($pm['icon'] ?? 'bi-wallet2') ?>"></i> <?= esc($pm['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <!-- Payment Method Details (QR code, account info) -->
                        <div id="paymentMethodDetails" class="mb-3" style="display: none;">
                            <div class="text-center p-3 bg-light border rounded">
                                <div id="pmQrCode" style="display: none;">
                                    <img id="pmQrImage" src="" alt="QR Code" style="max-width: 200px; border-radius: 8px; border: 2px solid #dee2e6;" class="mb-2">
                                </div>
                                <div id="pmAccountInfo">
                                    <p class="mb-1 fw-bold" id="pmAccountName"></p>
                                    <p class="mb-0"><code class="fs-5" id="pmAccountNumber"></code></p>
                                </div>
                                <small class="text-muted mt-2 d-block">Show this to the customer for payment</small>
                            </div>
                            <div class="mt-2">
                                <label for="paymentReference" class="form-label fw-bold"><i class="bi bi-hash"></i> Reference Number:</label>
                                <input type="text" id="paymentReference" class="form-control form-control-sm" placeholder="Enter GCash/e-wallet reference number" maxlength="100">
                                <small class="text-muted">Record the transaction reference number for verification</small>
                            </div>
                        </div>
                        <!-- Cash Payment Fields -->
                        <div id="cashPaymentFields" class="mb-3">
                            <label for="paymentAmount" class="form-label">Payment Amount (₱):</label>
                            <input type="number" id="paymentAmount" class="form-control form-control-sm" placeholder="Enter amount received" step="0.01" min="0" onchange="calculateChange()" oninput="calculateChange()">
                        </div>
                        <!-- Change Display -->
                        <div id="changeDisplay" class="mb-3" style="display: none;">
                            <div class="d-flex justify-content-between p-2 bg-light border rounded">
                                <span><strong>Change:</strong></span>
                                <span id="changeAmount" class="h6 text-success"><strong>₱0.00</strong></span>
                            </div>
                        </div>
                        <button class="btn btn-success w-100" onclick="completeSale()">✓ Complete Sale</button>
                        <button class="btn btn-secondary w-100 mt-2" onclick="clearCart()">🗑️ Clear Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Sticky Checkout Bar — appears once items are added, so cashiers
     never have to hunt for checkout after searching/selecting an item -->
<div id="mobileCheckoutBar" class="mobile-checkout-bar">
    <div class="mobile-checkout-info">
        <span class="mobile-checkout-count" id="mobileCartCount">0 items</span>
        <span class="mobile-checkout-total" id="mobileCartTotal">₱0.00</span>
    </div>
    <button type="button" class="btn btn-success mobile-checkout-btn" onclick="goToCheckout()">
        🛒 View Cart / Checkout <i class="bi bi-arrow-down-circle"></i>
    </button>
</div>

<form id="saleForm" method="post" action="<?= site_url('cashier/sales/process') ?>" style="display: none;">
    <?= csrf_field() ?>
    <input type="hidden" id="totalAmount" name="total_amount">
    <input type="hidden" id="discountAmount" name="discount">
    <input type="hidden" id="netAmount" name="net_amount">
    <input type="hidden" id="paymentMethodInput" name="payment_method">
    <input type="hidden" id="paymentReferenceInput" name="payment_reference">
    <input type="hidden" id="amountReceivedInput" name="amount_received">
    <input type="hidden" id="changeAmountInput" name="change_amount">
    <textarea id="itemsData" name="items" style="display: none;"></textarea>
</form>

<?= view('components/alarm_tones') ?>

<script>
const ALARM_SOUND = <?= json_encode($alarmSound ?? 'classic') ?>;
const ALARM_CUSTOM_URL = <?= json_encode($alarmCustomUrl ?? null) ?>;
let cart = [];
let alarmInterval = null;
let isAlarmActive = false;
let audioCtx = null;

// Browsers refuse to play audio until the page has had a real user
// interaction (click/tap/keypress) — a hard browser rule, not something
// code can bypass. Create/resume the shared AudioContext on the very first
// interaction anywhere on the page so a pending alarm can actually be heard.
function unlockAudioContext() {
    if (!audioCtx) {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return;
        audioCtx = new Ctx();
    }
    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
}
['click', 'keydown', 'touchstart'].forEach(function (evt) {
    document.addEventListener(evt, unlockAudioContext, { once: true, passive: true });
});

// On load: if previously muted, update button text
if (localStorage.getItem('alarmMuted')) {
    const label = document.getElementById('stopSoundBtnLabel');
    const icon = document.getElementById('stopSoundBtnIcon');
    if (label) label.textContent = 'Unmute Sound';
    if (icon) icon.className = 'bi bi-volume-up-fill';
}

// ============ BARCODE SCANNER SUPPORT ============
// Build item lookup from server data
const itemLookup = {};
<?php if (!empty($items)): ?>
    <?php foreach ($items as $item): ?>
        itemLookup['<?= strtolower(esc($item['code'])) ?>'] = {
            id: <?= (int)$item['id'] ?>,
            code: '<?= esc($item['code']) ?>',
            name: '<?= esc($item['name']) ?>',
            price: <?= round((float)$item['price'] * (1 - ((float)($item['discount'] ?? 0)) / 100), 2) ?>,
            originalPrice: <?= (float)$item['price'] ?>,
            discount: <?= (float)($item['discount'] ?? 0) ?>,
            stock: <?= (int)$item['quantity'] ?>
        };
    <?php endforeach; ?>
<?php endif; ?>

// Barcode scanner detection: scanners type fast + press Enter
let scanBuffer = '';
let scanTimeout = null;

document.addEventListener('keydown', function(e) {
    // Ignore if user is typing in an input/textarea (except Enter to submit scan)
    const tag = document.activeElement.tagName;
    const isInput = (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT');

    if (e.key === 'Enter') {
        // If we have a scan buffer, process it as barcode
        if (scanBuffer.length >= 3) {
            e.preventDefault();
            e.stopPropagation();
            processBarcodeInput(scanBuffer.trim());
            scanBuffer = '';
            clearTimeout(scanTimeout);
            // Blur any focused input to prevent form submission
            if (isInput) document.activeElement.blur();
            return;
        }
        scanBuffer = '';

        // Enter key completes sale (unless typing in specific input fields)
        const activeEl = document.activeElement;
        const activeId = activeEl.id || '';
        const activeTag = activeEl.tagName;
        const skipIds = ['searchInput', 'voiceSearch', 'paymentReference', 'paymentAmount'];
        const isCartQty = activeEl.closest && activeEl.closest('#cartBody');
        const isSelect = activeTag === 'SELECT';
        if (!skipIds.includes(activeId) && !isCartQty && !isSelect && cart.length > 0) {
            e.preventDefault();
            completeSale();
        }
        return;
    }

    // Only capture printable characters
    if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
        // If typing into search or other fields normally (slow), don't hijack
        // Scanner types extremely fast (< 50ms between keys)
        if (scanBuffer.length === 0) {
            scanBuffer = e.key;
        } else {
            scanBuffer += e.key;
        }

        clearTimeout(scanTimeout);
        // If no new character in 100ms, reset buffer (manual typing is slower)
        scanTimeout = setTimeout(function() {
            scanBuffer = '';
        }, 100);
    }
});

function processBarcodeInput(code) {
    const normalizedCode = code.toLowerCase().trim();
    const item = itemLookup[normalizedCode];

    if (item) {
        addToCart(item.id, item.code, item.name, item.price, item.stock, item.discount, item.originalPrice);
        showScanFeedback(item.name, true);
    } else {
        showScanFeedback(code, false);
    }
}

function showScanFeedback(text, success) {
    // Remove existing feedback
    const existing = document.getElementById('scanFeedback');
    if (existing) existing.remove();

    const div = document.createElement('div');
    div.id = 'scanFeedback';
    div.style.cssText = 'position: fixed; top: 80px; left: 50%; transform: translateX(-50%); z-index: 9999; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); animation: fadeInDown 0.3s ease;';
    
    if (success) {
        div.style.background = '#d4edda';
        div.style.color = '#155724';
        div.style.border = '1px solid #c3e6cb';
        div.innerHTML = '✅ Added: <strong>' + text + '</strong>';
    } else {
        div.style.background = '#f8d7da';
        div.style.color = '#721c24';
        div.style.border = '1px solid #f5c6cb';
        div.innerHTML = '❌ Item not found: <strong>' + text + '</strong>';
    }

    document.body.appendChild(div);
    setTimeout(function() {
        div.style.transition = 'opacity 0.5s';
        div.style.opacity = '0';
        setTimeout(function() { div.remove(); }, 500);
    }, 2000);
}
// ============ END BARCODE SCANNER ============

// Plays whichever alarm tone was chosen in Settings (defined in the shared
// components/alarm_tones partial, so admin's Preview button and this POS
// alarm always agree on what each option sounds like).
function playAlarmSound() {
    // Not unlocked yet (no user interaction on the page so far) — the
    // unlock listener will let this start firing the moment the user
    // clicks/taps anywhere, since the alarm interval keeps calling this.
    if (!audioCtx) return;

    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }

    playAlarmChoice(audioCtx, ALARM_SOUND, ALARM_CUSTOM_URL);
}

function checkStockAlerts() {
    const expiredCount = document.querySelectorAll('.item-row[data-expired="true"]').length;
    const lowStockCount = document.querySelectorAll('.item-row[data-low-stock="true"]').length;
    const stopBtn = document.getElementById('stopSoundBtn');

    if (expiredCount > 0 || lowStockCount > 0) {
        // Show the stop button as a visual indicator, but only play sound if not muted
        stopBtn.classList.add('active');

        const reasons = [];
        if (expiredCount > 0) reasons.push(expiredCount + ' expired item' + (expiredCount === 1 ? '' : 's'));
        if (lowStockCount > 0) reasons.push(lowStockCount + ' low stock item' + (lowStockCount === 1 ? '' : 's'));
        stopBtn.title = reasons.join(' · ');

        if (!isAlarmActive && !localStorage.getItem('alarmMuted')) {
            startAlarm();
        }
    } else {
        if (isAlarmActive) {
            stopAlarm();
        }
        stopBtn.classList.remove('active');
        stopBtn.removeAttribute('title');
    }
}

function startAlarm() {
    isAlarmActive = true;
    
    // Play alarm sound every 600ms
    playAlarmSound();
    alarmInterval = setInterval(() => {
        playAlarmSound();
    }, 600);
}

function stopAlarm() {
    isAlarmActive = false;
    if (alarmInterval) {
        clearInterval(alarmInterval);
        alarmInterval = null;
    }
    // Remember mute across refreshes
    localStorage.setItem('alarmMuted', '1');
    document.getElementById('stopSoundBtnLabel').textContent = 'Unmute Sound';
    document.getElementById('stopSoundBtnIcon').className = 'bi bi-volume-up-fill';
}

function toggleAlarm() {
    if (localStorage.getItem('alarmMuted')) {
        // Unmute — clear mute flag and re-check for expired/low-stock items
        localStorage.removeItem('alarmMuted');
        document.getElementById('stopSoundBtnLabel').textContent = 'Stop Sound';
        document.getElementById('stopSoundBtnIcon').className = 'bi bi-volume-mute-fill';
        checkStockAlerts();
    } else {
        // Mute
        stopAlarm();
    }
}

function filterItems() {
    const rawSearch = document.getElementById('searchInput').value.toLowerCase().trim();
    // Strip any punctuation for cleaner matching
    const searchTerm = rawSearch.replace(/[.,!?;:'"]+/g, ' ').replace(/\s+/g, ' ').trim();
    const searchWords = searchTerm.split(' ').filter(w => w.length > 0);
    const rows = document.querySelectorAll('#itemsBody .item-row');
    
    rows.forEach(row => {
        const code = row.dataset.code;
        const name = row.dataset.name;
        const description = row.dataset.description;
        const combined = code + ' ' + name + ' ' + description;
        
        let matches;
        if (searchWords.length === 0) {
            matches = true;
        } else {
            // Show item if ANY word appears in the combined text
            matches = searchWords.some(word => combined.includes(word));
        }
        
        row.style.display = matches ? '' : 'none';
    });

    checkStockAlerts();
}

function startVoiceSearch() {
    const btn = document.getElementById('voiceSearchBtn');
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
        alert('Voice search not supported in your browser');
        return;
    }
    
    const recognition = new SpeechRecognition();
    recognition.lang = 'en-US';
    
    btn.classList.add('listening');
    btn.textContent = 'Listening...';
    
    recognition.onresult = (event) => {
        let transcript = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
            transcript += event.results[i][0].transcript;
        }
        
        // Strip punctuation added by speech recognition (periods, commas, etc.)
        transcript = transcript.replace(/[.,!?;:]+$/g, '').replace(/[.,!?;:]/g, ' ').trim();
        
        document.getElementById('searchInput').value = transcript;
        filterItems();
        
        btn.classList.remove('listening');
        btn.textContent = 'Mic';
    };
    
    recognition.onerror = (event) => {
        btn.classList.remove('listening');
        btn.textContent = 'Mic';
    };
    
    recognition.onend = () => {
        btn.classList.remove('listening');
        btn.textContent = 'Mic';
    };
    
    recognition.start();
}

function addToCart(id, code, name, price, stock, discount, originalPrice) {
    discount = discount || 0;
    originalPrice = originalPrice || price;
    const existingItem = cart.find(item => item.item_id === id);
    
    if (existingItem) {
        if (existingItem.quantity < stock) {
            existingItem.quantity++;
        } else {
            alert('Cannot add more items. Stock limit reached.');
        }
    } else {
        cart.push({ item_id: id, code, name, price, quantity: 1, stock, discount, originalPrice });
    }
    
    renderCart();
}

function renderCart() {
    const cartBody = document.getElementById('cartBody');
    cartBody.innerHTML = '';
    
    if (cart.length === 0) {
        cartBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Cart is empty</td></tr>';
        calculateTotal();
        return;
    }
    
    cart.forEach(item => {
        const discountBadge = item.discount > 0 ? `<span class="badge bg-danger" style="font-size:9px;">-${item.discount}%</span>` : '';
        const row = `
            <tr>
                <td>${item.name} ${discountBadge}</td>
                <td style="width: 80px;">
                    <input type="number" class="form-control text-center fw-bold" value="${item.quantity}" min="1" max="${item.stock}" 
                           style="width: 70px; padding: 6px 4px; font-size: 16px;"
                           onchange="updateCartQuantity(${item.item_id}, this.value)">
                </td>
                <td>₱${(item.price * item.quantity).toFixed(2)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.item_id})">✕</button>
                </td>
            </tr>
        `;
        cartBody.innerHTML += row;
    });
    
    calculateTotal();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.item_id !== id);
    renderCart();
}

function updateCartQuantity(id, quantity) {
    const item = cart.find(item => item.item_id === id);
    if (item) {
        if (quantity > item.stock) {
            alert('Cannot exceed available stock');
            return;
        }
        item.quantity = quantity;
        renderCart();
    }
}

function calculateTotal() {
    const grossTotal = cart.reduce((sum, item) => sum + (item.originalPrice * item.quantity), 0);
    const netTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const totalDiscount = grossTotal - netTotal;

    document.getElementById('subtotal').textContent = '₱' + grossTotal.toFixed(2);
    document.getElementById('discountDisplay').textContent = '-₱' + totalDiscount.toFixed(2);
    document.getElementById('total').innerHTML = '<strong>₱' + netTotal.toFixed(2) + '</strong>';

    updateMobileCheckoutBar(netTotal);
}

// Keep the mobile floating checkout bar in sync with the cart, and only
// show it once there's something to check out.
function updateMobileCheckoutBar(netTotal) {
    const bar = document.getElementById('mobileCheckoutBar');
    if (!bar) return;

    const itemCount = cart.reduce((sum, item) => sum + Number(item.quantity), 0);

    if (itemCount > 0) {
        document.getElementById('mobileCartCount').textContent = itemCount + (itemCount === 1 ? ' item' : ' items');
        document.getElementById('mobileCartTotal').textContent = '₱' + netTotal.toFixed(2);
        bar.classList.add('show');
    } else {
        bar.classList.remove('show');
    }
}

// Jump straight to the checkout/payment area — used by the mobile bar so
// cashiers can proceed to check out right after searching/selecting an item.
function goToCheckout() {
    const target = document.getElementById('checkoutSection') || document.getElementById('cartSection');
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function completeSale() {
    if (cart.length === 0) {
        alert('Cart is empty. Add items to complete a sale.');
        return;
    }
    
    const grossTotal = cart.reduce((sum, item) => sum + (item.originalPrice * item.quantity), 0);
    const netTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const totalDiscount = grossTotal - netTotal;
    
    document.getElementById('totalAmount').value = grossTotal.toFixed(2);
    document.getElementById('discountAmount').value = totalDiscount.toFixed(2);
    document.getElementById('netAmount').value = netTotal.toFixed(2);
    document.getElementById('paymentMethodInput').value = document.getElementById('paymentMethod').value;
    const paymentAmountInput = document.getElementById('paymentAmount');
    const paymentReceived = parseFloat(paymentAmountInput ? paymentAmountInput.value : 0) || 0;
    const effectiveAmountReceived = paymentReceived > 0 ? paymentReceived : netTotal;
    const changeAmount = effectiveAmountReceived - netTotal;

    document.getElementById('paymentReferenceInput').value = (document.getElementById('paymentReference') ? document.getElementById('paymentReference').value : '').trim();
    document.getElementById('amountReceivedInput').value = effectiveAmountReceived.toFixed(2);
    document.getElementById('changeAmountInput').value = changeAmount.toFixed(2);
    document.getElementById('itemsData').value = JSON.stringify(cart);
    
    document.getElementById('saleForm').submit();
}

function clearCart() {
    if (confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        renderCart();
    }
}

// Initialize alarm check on page load
document.addEventListener('DOMContentLoaded', function() {
    checkStockAlerts();
    updatePaymentFields();
});

function updatePaymentFields() {
    const select = document.getElementById('paymentMethod');
    const option = select.options[select.selectedIndex];
    const paymentType = option.dataset.type || 'cash';
    const cashPaymentFields = document.getElementById('cashPaymentFields');
    const changeDisplay = document.getElementById('changeDisplay');
    const pmDetails = document.getElementById('paymentMethodDetails');
    const pmQrCode = document.getElementById('pmQrCode');
    const pmQrImage = document.getElementById('pmQrImage');
    const pmAccountName = document.getElementById('pmAccountName');
    const pmAccountNumber = document.getElementById('pmAccountNumber');
    const pmAccountInfo = document.getElementById('pmAccountInfo');

    // Show/hide cash fields
    if (select.value === 'cash') {
        cashPaymentFields.style.display = 'block';
        changeDisplay.style.display = 'block';
        pmDetails.style.display = 'none';
    } else {
        cashPaymentFields.style.display = 'none';
        changeDisplay.style.display = 'none';
        document.getElementById('paymentAmount').value = '';

        // Show payment method details (QR, account info)
        const qr = option.dataset.qr || '';
        const accName = option.dataset.accountName || '';
        const accNumber = option.dataset.accountNumber || '';

        if (qr || accName || accNumber) {
            pmDetails.style.display = 'block';
            
            if (qr) {
                pmQrCode.style.display = 'block';
                pmQrImage.src = qr;
            } else {
                pmQrCode.style.display = 'none';
            }

            if (accName || accNumber) {
                pmAccountInfo.style.display = 'block';
                pmAccountName.textContent = accName;
                pmAccountNumber.textContent = accNumber;
            } else {
                pmAccountInfo.style.display = 'none';
            }
        } else {
            pmDetails.style.display = 'none';
        }
    }
}

function calculateChange() {
    const totalText = document.getElementById('total').innerHTML;
    const totalAmount = parseFloat(totalText.replace(/₱|<[^>]*>/g, '').trim());
    const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const change = paymentAmount - totalAmount;
    
    const changeAmountEl = document.getElementById('changeAmount');
    if (change < 0) {
        changeAmountEl.innerHTML = '<strong style="color: #ff1744;">₱' + Math.abs(change).toFixed(2) + ' SHORT</strong>';
    } else {
        changeAmountEl.innerHTML = '<strong>₱' + change.toFixed(2) + '</strong>';
    }
}
</script>

<?= $this->endSection() ?>

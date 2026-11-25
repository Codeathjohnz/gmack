<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="<?= base_url('admin/purchase-orders') ?>" class="btn btn-link p-0" style="font-size:1.4rem;"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0">New Purchase Order</h2>
    </div>

    <?php if (empty($suppliers)): ?>
        <div class="alert alert-warning">
            You don't have any active suppliers yet. <a href="<?= base_url('admin/suppliers/create') ?>">Add one first</a>.
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/purchase-orders/create') ?>" id="poForm">
                <?= csrf_field() ?>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">-- Select supplier --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Notes (optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Rush order for weekend restock">
                    </div>
                </div>

                <label class="form-label fw-bold">Line Items</label>
                <p class="text-muted small">Pick an existing item to auto-restock it on receiving, or type a new item name to just track the cost.</p>

                <div class="table-responsive">
                <table class="table table-sm align-middle" id="lineItemsTable">
                    <thead>
                        <tr>
                            <th style="width:32%;">Item</th>
                            <th style="width:16%;">Quantity</th>
                            <th style="width:18%;">Unit Cost (₱)</th>
                            <th style="width:18%;">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="lineItemsBody"></tbody>
                </table>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="addLineBtn">
                    <i class="bi bi-plus-lg"></i> Add Line
                </button>

                <div class="text-end mb-3">
                    <strong>Total: ₱<span id="poTotal">0.00</span></strong>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-clipboard-check"></i> Create Purchase Order</button>
            </form>
        </div>
    </div>
</div>

<datalist id="knownItems">
    <?php foreach ($items as $it): ?>
        <option data-id="<?= $it['id'] ?>" value="<?= esc($it['name']) ?>"></option>
    <?php endforeach; ?>
</datalist>

<script>
const KNOWN_ITEMS = <?= json_encode(array_map(fn($it) => ['id' => $it['id'], 'name' => $it['name']], $items)) ?>;
let lineIndex = 0;

function addLine() {
    const i = lineIndex++;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm line-name" name="item_name[${i}]" list="knownItems" placeholder="Item name" required>
            <input type="hidden" name="item_id[${i}]" class="line-item-id">
        </td>
        <td><input type="number" step="any" min="0.01" class="form-control form-control-sm line-qty" name="quantity[${i}]" required></td>
        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm line-cost" name="unit_cost[${i}]" required></td>
        <td class="line-subtotal">₱0.00</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-line"><i class="bi bi-trash"></i></button></td>
    `;
    document.getElementById('lineItemsBody').appendChild(tr);

    const nameInput = tr.querySelector('.line-name');
    const idInput = tr.querySelector('.line-item-id');
    const qtyInput = tr.querySelector('.line-qty');
    const costInput = tr.querySelector('.line-cost');
    const subtotalCell = tr.querySelector('.line-subtotal');

    nameInput.addEventListener('input', function () {
        const match = KNOWN_ITEMS.find(it => it.name.toLowerCase() === this.value.toLowerCase());
        idInput.value = match ? match.id : '';
    });

    function updateSubtotal() {
        const qty = parseFloat(qtyInput.value) || 0;
        const cost = parseFloat(costInput.value) || 0;
        const subtotal = qty * cost;
        subtotalCell.textContent = '₱' + subtotal.toFixed(2);
        updateTotal();
    }
    qtyInput.addEventListener('input', updateSubtotal);
    costInput.addEventListener('input', updateSubtotal);

    tr.querySelector('.remove-line').addEventListener('click', function () {
        tr.remove();
        updateTotal();
    });
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.line-qty').forEach(function (qtyInput, i) {
        const costInput = document.querySelectorAll('.line-cost')[i];
        total += (parseFloat(qtyInput.value) || 0) * (parseFloat(costInput.value) || 0);
    });
    document.getElementById('poTotal').textContent = total.toFixed(2);
}

document.getElementById('addLineBtn').addEventListener('click', addLine);
addLine(); // start with one row
</script>

<?= $this->endSection() ?>

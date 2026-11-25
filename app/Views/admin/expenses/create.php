<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="<?= base_url('admin/expenses') ?>" class="btn btn-link p-0" style="font-size:1.4rem;"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0">Record Expense</h2>
    </div>

    <div class="card" style="max-width:600px;">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/expenses/create') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" id="category" class="form-select" required onchange="toggleCustomCategory()">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat) ?>" <?= old('category') === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3" id="customCategoryField" style="display:none;">
                    <label class="form-label">Specify Category</label>
                    <input type="text" name="custom_category" id="custom_category" class="form-control" placeholder="e.g. Permits, Insurance" value="<?= old('custom_category') ?>">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required value="<?= old('amount') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date</label>
                        <input type="date" name="expense_date" class="form-control" required value="<?= old('expense_date') ?: date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Description <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" name="description" class="form-control" placeholder="e.g. September rent, Globe load for POS phone" value="<?= old('description') ?>">
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Record Expense</button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleCustomCategory() {
        const select = document.getElementById('category');
        const field = document.getElementById('customCategoryField');
        field.style.display = select.value === 'Other' ? 'block' : 'none';
    }
    toggleCustomCategory();
</script>

<?= $this->endSection() ?>

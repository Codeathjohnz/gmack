<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="<?= base_url('admin/items') ?>" class="btn btn-link p-0" style="font-size:1.4rem;"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0"><i class="bi bi-file-earmark-arrow-up"></i> Bulk Import Items</h2>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= esc(session('error')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (session()->has('import_skipped')): ?>
        <div class="alert alert-warning">
            <strong><i class="bi bi-exclamation-triangle"></i> <?= count(session('import_skipped')) ?> row(s) skipped:</strong>
            <ul class="mb-0 mt-2 small">
                <?php foreach (session('import_skipped') as $line): ?>
                    <li><?= esc($line) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-upload"></i> Upload a spreadsheet</div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('admin/items/import') ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="spreadsheet" class="form-label">.xlsx, .xls, or .csv file</label>
                            <input type="file" class="form-control" id="spreadsheet" name="spreadsheet" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload"></i> Import Items</button>
                        <a href="<?= base_url('admin/items/import-template') ?>" class="btn btn-secondary text-white">
                            <i class="bi bi-download"></i> Download Template
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><i class="bi bi-info-circle"></i> What it expects</div>
                <div class="card-body">
                    <p class="small text-muted">The first row must be a header. Column names are matched flexibly — case and extra spaces don't matter, and a few common synonyms are recognized for each field.</p>
                    <table class="table table-sm">
                        <thead><tr><th>Column</th><th>Recognized as</th></tr></thead>
                        <tbody class="small">
                            <tr><td><strong>Name</strong> <span class="text-danger">*required*</span></td><td>Name, Item, Product</td></tr>
                            <tr><td>Code</td><td>Code, SKU, Item Code — auto-generated if missing or a duplicate</td></tr>
                            <tr><td>Price</td><td>Price, Unit Cost. <strong>Never guessed</strong> — no price column, or a blank cell, imports the item at ₱0 for you to edit.</td></tr>
                            <tr><td>Quantity</td><td>Quantity, Qty, Available Stock/Quantity, Stock — this becomes the item's current quantity</td></tr>
                            <tr><td>Total / Original Qty</td><td>Total Stock, Total Quantity, Originally Stocked — a bookkeeping quantity, kept only as a note on the item, never used for price or current stock</td></tr>
                            <tr><td>Category</td><td>Category — matched to an existing category by name; left blank otherwise</td></tr>
                            <tr><td>Unit</td><td>Unit, UOM (pcs / kilo)</td></tr>
                            <tr><td>Description</td><td>Description, Notes</td></tr>
                        </tbody>
                    </table>
                    <p class="small text-muted mb-0">Rows without a name are skipped. Up to 2,000 rows per file.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h2 class="mb-0">Businesses</h2>
            <p class="text-muted mb-0">Every company running its own Gmack instance.</p>
        </div>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
    <?php endif; ?>
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success"><?= session('success') ?></div>
    <?php endif; ?>

    <div class="row mb-3 g-3">
        <div class="col-sm-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Total Businesses</div>
                    <div class="stat-value" data-count="<?= count($businesses) ?>">0</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Active</div>
                    <div class="stat-value" data-count="<?= count(array_filter($businesses, fn($b) => $b['status'] === 'active')) ?>">0</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Inactive</div>
                    <div class="stat-value" data-count="<?= count(array_filter($businesses, fn($b) => $b['status'] === 'inactive')) ?>">0</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover data-table" data-empty="No businesses yet">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Owner</th>
                        <th>Users</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($businesses)): ?>
                        <?php foreach ($businesses as $biz): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($biz['name']) ?></strong>
                                    <div class="text-muted small"><?= esc($biz['industry']) ?></div>
                                </td>
                                <td><?= $biz['owner'] ? esc($biz['owner']['username']) : '<span class="text-muted">—</span>' ?></td>
                                <td><?= (int) $biz['user_count'] ?></td>
                                <td><?= (int) $biz['item_count'] ?></td>
                                <td>
                                    <span class="badge <?= $biz['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($biz['status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= $biz['created_at'] ? date('M j, Y', strtotime($biz['created_at'])) : '—' ?></td>
                                <td class="d-flex gap-1 flex-wrap">
                                    <a href="<?= base_url('admin/businesses/switch/' . $biz['id']) ?>" class="btn btn-primary btn-sm" title="View this business's admin panel">
                                        <i class="bi bi-box-arrow-in-right"></i> Switch
                                    </a>
                                    <a href="<?= base_url('admin/businesses/edit/' . $biz['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="<?= base_url('admin/businesses/toggle/' . $biz['id']) ?>" class="btn btn-secondary btn-sm" onclick="return confirmDelete(event, '<?= esc($biz['name'], 'js') ?>', { title: '<?= $biz['status'] === 'active' ? 'Deactivate' : 'Activate' ?> this business?', text: '', icon: 'question', confirmButtonText: '<?= $biz['status'] === 'active' ? 'Deactivate' : 'Activate' ?>' });">
                                        <?= $biz['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="deleteBusiness(<?= (int) $biz['id'] ?>, <?= esc(json_encode($biz['name']), 'attr') ?>, <?= (int) $biz['user_count'] ?>, <?= (int) $biz['item_count'] ?>, <?= (int) $biz['sale_count'] ?>)">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
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

<form id="deleteBusinessForm" method="post" style="display:none">
    <?= csrf_field() ?>
    <input type="hidden" name="confirm_name" id="deleteBusinessName">
</form>

<script>
function deleteBusiness(id, name, users, items, sales) {
    var form = document.getElementById('deleteBusinessForm');
    form.action = '<?= base_url('admin/businesses/delete') ?>/' + id;

    var esc = function (s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; };
    var summary = 'This permanently deletes <b>' + esc(name) + '</b> and all of its data: '
        + users + ' user account(s), ' + items + ' item(s), ' + sales + ' sale(s), '
        + 'plus its categories, suppliers, purchase orders, expenses, stock history and settings.'
        + '<br><br>This cannot be undone. Type the business name to confirm:';

    if (!window.Swal) {
        var typed = window.prompt('Delete "' + name + '" and all of its data? This cannot be undone.\nType the business name to confirm:');
        if (typed === null) return;
        document.getElementById('deleteBusinessName').value = typed;
        form.submit();
        return;
    }

    Swal.fire({
        title: 'Delete this business?',
        html: summary,
        icon: 'warning',
        input: 'text',
        inputPlaceholder: name,
        showCancelButton: true,
        confirmButtonText: 'Delete permanently',
        confirmButtonColor: '#c62828',
        cancelButtonColor: '#5b6672',
        focusCancel: true,
        inputValidator: function (value) {
            if (value.trim() !== name) return 'The name does not match. Type it exactly as shown.';
        }
    }).then(function (result) {
        if (result.isConfirmed) {
            document.getElementById('deleteBusinessName').value = result.value.trim();
            form.submit();
        }
    });
}
</script>

<?= $this->endSection() ?>

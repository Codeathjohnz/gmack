<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="mb-3">
        <h2 class="mb-0"><i class="bi bi-journal-text"></i> Audit Log</h2>
        <p class="text-muted mb-0">
            <?= $isSuperAdmin ? 'Every account-changing action across the entire platform.' : 'Every account-changing action in your business — who did what, and when.' ?>
        </p>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover data-table" data-empty="No activity recorded yet.">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <?php if ($isSuperAdmin): ?><th>Business</th><?php endif; ?>
                        <th>Action</th>
                        <th>Detail</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-nowrap text-muted small"><?= $log['created_at'] ? date('M j, Y g:ia', strtotime($log['created_at'])) : '—' ?></td>
                                <td><?= esc($log['username']) ?></td>
                                <?php if ($isSuperAdmin): ?><td><?= esc($log['business_name']) ?></td><?php endif; ?>
                                <td><code><?= esc($log['action']) ?></code></td>
                                <td><?= esc($log['description']) ?></td>
                                <td class="text-muted small"><?= esc($log['ip_address'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

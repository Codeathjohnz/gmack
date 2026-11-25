<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Sale History</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Reference No</th>
                        <th>Date & Time</th>
                        <th>Total Amount</th>
                        <th>Discount</th>
                        <th>Net Amount</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No sales found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?= esc($sale['reference_no']) ?></td>
                                <td><?= date('M d, Y g:i A', strtotime($sale['created_at'])) ?></td>
                                <td>₱<?= number_format($sale['total_amount'], 2) ?></td>
                                <td>₱<?= number_format($sale['discount'], 2) ?></td>
                                <td><strong>₱<?= number_format($sale['net_amount'], 2) ?></strong></td>
                                <td><?= ucfirst($sale['payment_method']) ?></td>
                                <td>
                                    <a href="<?= base_url('cashier/sales/receipt/' . $sale['id']) ?>" class="btn btn-info btn-sm">View Receipt</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

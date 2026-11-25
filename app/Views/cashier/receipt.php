<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<script>
    // Auto-focus print dialog on receipt display
    window.addEventListener('load', function() {
        // Uncomment below to auto-print on receipt display
        // window.print();
    });
</script>

<style>
    .receipt-wrapper {
        max-width: 380px;
        margin: 1rem auto;
        background: #fff;
        padding: 1rem 1.2rem;
        font-family: 'Courier New', Courier, monospace;
        color: #000;
        border: 1px solid #ddd;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    .receipt-header,
    .receipt-footer {
        text-align: center;
        margin-bottom: 1rem;
    }
    .receipt-header h2,
    .receipt-header h3 {
        margin: 0;
        letter-spacing: 1px;
        font-size: 0.9rem;
    }
    .receipt-line {
        border-top: 1px dashed #333;
        margin: 0.8rem 0;
    }
    .receipt-info,
    .receipt-summary {
        width: 100%;
        margin-bottom: 0.8rem;
    }
    .receipt-info td,
    .receipt-summary td {
        padding: 0.2rem 0;
        font-size: 0.95rem;
    }
    .text-right {
        text-align: right;
    }
    .total-row td {
        font-weight: bold;
        font-size: 1rem;
    }
    .receipt-details {
        margin-top: 0.8rem;
    }
    .receipt-details p {
        margin: 0.2rem 0;
        font-size: 0.9rem;
    }
    .receipt-footer p {
        margin: 0.25rem 0;
        font-size: 0.85rem;
    }
    .receipt-note {
        font-size: 0.82rem;
        margin-top: 0.8rem;
    }
    .receipt-btn {
        width: 100%;
        margin-top: 1rem;
    }
    @media print {
        body, html {
            margin: 0;
            padding: 0;
            background: none;
        }
        .navbar, .sidebar, .btn-dark, a[href] {
            display: none !important;
        }
        .receipt-wrapper {
            max-width: 100%;
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
            page-break-after: avoid;
        }
        .container, .content {
            margin: 0;
            padding: 0;
        }
    }
</style>

<div class="receipt-wrapper">
    <div class="receipt-header">
        <h2>G-MACK HARDWARE AND SUPPLIES</h2>
        <div class="receipt-line"></div>
        <h3>SALES RECEIPT</h3>
        <div class="receipt-line"></div>
    </div>

    <table class="receipt-info">
        <tr>
            <td>REF NO:</td>
            <td class="text-right"><?= esc($sale['reference_no']) ?></td>
        </tr>
        <tr>
            <td>DATE:</td>
            <td class="text-right"><?= date('M d, Y H:i', strtotime($sale['created_at'])) ?></td>
        </tr>
        <tr>
            <td>CASHIER:</td>
            <td class="text-right"><?= esc($cashier['username'] ?? 'Unknown') ?></td>
        </tr>
        <tr>
            <td>PAYMENT:</td>
            <td class="text-right"><?= esc(ucfirst($sale['payment_method'])) ?></td>
        </tr>
        <?php if (!empty($sale['payment_reference'])): ?>
        <tr>
            <td>PAY REF:</td>
            <td class="text-right"><?= esc($sale['payment_reference']) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <div class="receipt-line"></div>

    <table class="receipt-summary">
        <thead>
            <tr>
                <td>ITEM</td>
                <td class="text-right">QTY</td>
                <td class="text-right">AMT</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= esc($item['name']) ?></td>
                    <td class="text-right"><?= $item['quantity'] ?></td>
                    <td class="text-right">₱<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="receipt-line"></div>

    <table class="receipt-summary">
        <tr class="total-row">
            <td>TOTAL</td>
            <td class="text-right">₱<?= number_format($sale['total_amount'], 2) ?></td>
        </tr>
        <?php if ($sale['discount'] > 0): ?>
        <tr>
            <td>DISCOUNT</td>
            <td class="text-right">-₱<?= number_format($sale['discount'], 2) ?></td>
        </tr>
        <?php endif; ?>
        <tr class="total-row">
            <td>NET AMOUNT</td>
            <td class="text-right">₱<?= number_format($sale['net_amount'], 2) ?></td>
        </tr>
        <?php if (isset($sale['amount_received'])): ?>
        <tr>
            <td>PAID</td>
            <td class="text-right">₱<?= number_format($sale['amount_received'], 2) ?></td>
        </tr>
        <tr class="total-row">
            <td style="color: green;">CHANGE</td>
            <td class="text-right" style="color: green;">₱<?= number_format($sale['change_amount'] ?? 0, 2) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <div class="receipt-footer">
        <div class="receipt-line"></div>
        <p>Thank you for shopping with us!</p>
        <p>Please keep this receipt for returns or exchanges.</p>
    </div>

    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
        <button type="button" class="btn btn-primary" style="flex: 1;" onclick="window.print()">Print Receipt</button>
        <a href="<?= site_url('cashier/sales') ?>" class="btn btn-dark" style="flex: 1;">New Sale</a>
    </div>
</div>

<?= $this->endSection() ?>

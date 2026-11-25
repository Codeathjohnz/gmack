<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcodes - Gmack</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: Arial, sans-serif; background: #fff; }

        /* Screen styles */
        @media screen {
            .print-controls {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: #333;
                color: white;
                padding: 12px 24px;
                display: flex;
                align-items: center;
                gap: 15px;
                z-index: 1000;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            }
            .print-controls button {
                padding: 8px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 600;
            }
            .btn-print { background: #d4410e; color: white; }
            .btn-print:hover { background: #a83309; }
            .btn-back { background: #6c757d; color: white; }
            .btn-back:hover { background: #5a6268; }
            .barcode-container { margin-top: 60px; padding: 20px; }
        }

        /* Barcode label styling */
        .barcode-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-start;
        }

        .barcode-label {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            width: 220px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .barcode-label svg {
            max-width: 100%;
        }

        .barcode-label .item-name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .barcode-label .item-price {
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
        }

        /* Print styles */
        @media print {
            .print-controls { display: none !important; }
            .barcode-container { margin: 0; padding: 5mm; }
            
            .barcode-grid {
                gap: 3mm;
            }

            .barcode-label {
                border: 1px solid #ccc;
                padding: 2mm;
                width: 50mm;
            }

            .barcode-label .item-name { font-size: 8pt; }
            .barcode-label .item-price { font-size: 10pt; }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button class="btn-print" onclick="window.print()">🖨️ Print Barcodes</button>
        <button class="btn-back" onclick="window.history.back()">← Back</button>
        <span><?= count($items) ?> item(s) × <?= $quantity ?> copies = <?= count($items) * $quantity ?> barcode(s)</span>
    </div>

    <div class="barcode-container">
        <div class="barcode-grid">
            <?php foreach ($items as $item): ?>
                <?php for ($i = 0; $i < $quantity; $i++): ?>
                    <div class="barcode-label">
                        <div class="item-name"><?= esc($item['name']) ?></div>
                        <svg class="barcode-svg" 
                             data-code="<?= esc($item['code']) ?>" 
                             data-format="<?= esc($barcodeFormat) ?>"></svg>
                        <div class="item-price">₱<?= number_format($item['price'], 2) ?></div>
                    </div>
                <?php endfor; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.barcode-svg').forEach(function(svg) {
                try {
                    JsBarcode(svg, svg.dataset.code, {
                        format: svg.dataset.format,
                        width: 1.5,
                        height: 50,
                        displayValue: true,
                        fontSize: 12,
                        margin: 3,
                        textMargin: 2
                    });
                } catch (e) {
                    svg.outerHTML = '<p style="color:red;font-size:10px;">Invalid: ' + svg.dataset.code + '</p>';
                }
            });
        });
    </script>
</body>
</html>

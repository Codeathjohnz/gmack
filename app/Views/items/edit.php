<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #999;
            font-size: 14px;
        }

        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 10px 15px;
            font-size: 14px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn-submit {
            width: 100%;
            background-color: #667eea;
            border-color: #667eea;
            color: white;
            font-weight: 500;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: #5568d3;
            border-color: #5568d3;
        }

        .btn-cancel {
            width: 100%;
            background-color: #ecf0f1;
            border-color: #ecf0f1;
            color: #2c3e50;
            font-weight: 500;
            padding: 12px;
            border-radius: 6px;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background-color: #bdc3c7;
            color: #2c3e50;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        @media (max-width: 600px) {
            .form-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2>Edit Item</h2>
            <p>Update the item details below</p>
        </div>

        <form method="post" action="<?= base_url('items/edit/' . $item['id']) ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="code" class="form-label">Item Code *</label>
                    <input type="text" class="form-control" id="code" name="code" value="<?= esc($item['code']) ?>" placeholder="e.g., PROD001" required>
                </div>
                <div class="form-group">
                    <label for="name" class="form-label">Item Name *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= esc($item['name']) ?>" placeholder="e.g., Laptop" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" placeholder="Optional description" rows="3" style="margin-bottom: 20px;"><?= esc($item['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price" class="form-label">Price (₱) *</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?= $item['price'] ?>" placeholder="0.00" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="quantity" class="form-label">Quantity *</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" value="<?= $item['quantity'] ?>" placeholder="0" required>
                </div>
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" style="margin-bottom: 20px;">
                    <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $item['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Update Item</button>
            <a href="<?= base_url('items') ?>" class="btn-cancel">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

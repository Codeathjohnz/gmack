<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ItemModel;
use App\Models\CategoryModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Items extends BaseController
{
    protected $itemModel;

    /**
     * Recognized spreadsheet header names -> item field, matched case/space
     * -insensitively so real-world exports (extra spaces, ALL CAPS, a
     * synonym) still map correctly without the admin having to reformat
     * their file first.
     */
    protected const IMPORT_COLUMN_ALIASES = [
        'code'          => ['code', 'code product', 'item code', 'sku', 'product code'],
        'name'          => ['name', 'item', 'item name', 'product', 'product name', 'description item'],
        'category'      => ['category', 'category name'],
        'price'         => ['price', 'unit price', 'unit cost', 'cost'],
        'quantity'      => ['quantity', 'qty', 'available stock', 'available quantity', 'stock', 'stock qty', 'on hand'],
        // A bookkeeping record of how much was originally stocked — a
        // quantity, not a value, so it's never used to guess a price. Kept
        // only as a reference note on the item.
        'original_quantity' => ['total stock', 'total quantity', 'original quantity', 'originally stocked', 'initial stock'],
        'unit'          => ['unit', 'uom'],
        'description'   => ['description', 'details', 'notes'],
        'low_stock_threshold' => ['low stock threshold', 'reorder point', 'low stock alert at'],
    ];

    protected const IMPORT_MAX_ROWS = 2000;

    /**
     * Low stock threshold - items at or below this quantity are flagged
     */
    protected const LOW_STOCK_THRESHOLD = 10;
    protected const CRITICAL_STOCK_THRESHOLD = 5;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
    }

    protected const ITEMS_PER_PAGE = 50;

    public function index()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $businessId = $this->currentBusinessId();

        $search = trim((string) $this->request->getGet('search'));
        $status = $this->request->getGet('status');
        $stock  = $this->request->getGet('stock');
        $sort   = $this->request->getGet('sort') ?: 'name-asc';

        // Server-side pagination: the page only ever builds ~50 rows of DOM,
        // no matter how large the catalog grows. A 400+ row all-at-once table
        // (each row carrying a category dropdown + a live discount input)
        // was heavy enough to freeze a browser tab on import-sized catalogs.
        $query = $this->itemModel->forBusiness($businessId);
        $this->applyItemFilters($query, $search, $status, $stock);
        $this->applyItemSort($query, $sort);

        $data['items'] = $query->paginate(self::ITEMS_PER_PAGE, 'items');
        $data['pager'] = $this->itemModel->pager;
        $data['search'] = $search;
        $data['status'] = $status;
        $data['stock'] = $stock;
        $data['sort'] = $sort;

        $pagerDetails = $data['pager']->getDetails('items');
        $data['totalItems'] = $pagerDetails['total'];
        $data['showingStart'] = $data['totalItems'] === 0 ? 0 : ($pagerDetails['currentPage'] - 1) * $pagerDetails['perPage'] + 1;
        $data['showingEnd'] = min($data['totalItems'], $pagerDetails['currentPage'] * $pagerDetails['perPage']);

        $categoryModel = new CategoryModel();
        $data['categories'] = $categoryModel->getActiveCategories($businessId);

        // Full-catalog low/critical counts for the alert banner — computed in
        // SQL, not by looping every row into PHP, so it stays correct
        // (and cheap) regardless of catalog size or which page is showing.
        $data['critical_stock_count'] = $this->itemModel->forBusiness($businessId)
            ->where('quantity <= (low_stock_threshold / 2)', null, false)
            ->countAllResults();
        $data['low_stock_count'] = $this->itemModel->forBusiness($businessId)
            ->where('quantity >', 0)
            ->where('quantity > (low_stock_threshold / 2)', null, false)
            ->where('quantity <= low_stock_threshold', null, false)
            ->countAllResults();

        return view('admin/items/list', $data);
    }

    private function applyItemFilters($query, string $search, ?string $status, ?string $stock): void
    {
        if ($search !== '') {
            $query->groupStart()
                  ->like('name', $search)
                  ->orLike('code', $search)
                  ->groupEnd();
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        switch ($stock) {
            case 'out':
                $query->where('quantity <=', 0);
                break;
            case 'critical':
                $query->where('quantity <= (low_stock_threshold / 2)', null, false);
                break;
            case 'low':
                $query->where('quantity >', 0)
                      ->where('quantity > (low_stock_threshold / 2)', null, false)
                      ->where('quantity <= low_stock_threshold', null, false);
                break;
            case 'in-stock':
                $query->where('quantity > low_stock_threshold', null, false);
                break;
        }
    }

    private function applyItemSort($query, string $sort): void
    {
        switch ($sort) {
            case 'name-desc':  $query->orderBy('name', 'DESC'); break;
            case 'price-asc':  $query->orderBy('price', 'ASC'); break;
            case 'price-desc': $query->orderBy('price', 'DESC'); break;
            case 'qty-asc':    $query->orderBy('quantity', 'ASC'); break;
            case 'qty-desc':   $query->orderBy('quantity', 'DESC'); break;
            case 'name-asc':
            default:           $query->orderBy('name', 'ASC'); break;
        }
    }

    public function create()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        if ($this->request->getMethod() === 'POST') {
            $businessId = $this->currentBusinessId();
            $code = trim((string) $this->request->getPost('code'));

            if ($code === '') {
                session()->setFlashdata('error', 'Item code is required.');
                session()->setFlashdata('old_input', $this->request->getPost());
                return redirect()->back()->withInput();
            }

            if ($this->itemModel->forBusiness($businessId)->where('code', $code)->first()) {
                session()->setFlashdata('error', 'Item code "' . $code . '" is already in use — pick another or click Auto-Generate.');
                session()->setFlashdata('old_input', $this->request->getPost());
                return redirect()->back()->withInput();
            }

            $data = [
                'business_id' => $businessId,
                'code' => $code,
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'category_id' => $this->request->getPost('category_id') ?: null,
                'price' => $this->request->getPost('price'),
                'quantity' => $this->request->getPost('quantity'),
                'unit' => in_array($this->request->getPost('unit'), ['pcs', 'kilo'], true) ? $this->request->getPost('unit') : 'pcs',
                'status' => $this->request->getPost('status') ?: 'active',
                'low_stock_threshold' => (int) ($this->request->getPost('low_stock_threshold') ?: 10),
                'expiration_date' => $this->request->getPost('expiration_date') ?: null,
                'created_by' => session()->get('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Handle image upload
            $image = $this->request->getFile('image');
            if ($image && $image->isValid() && !$image->hasMoved()) {
                $newName = $image->getRandomName();
                $image->move(FCPATH . 'uploads/items', $newName);
                $data['image'] = 'uploads/items/' . $newName;
            }

            if ($this->itemModel->save($data)) {
                $this->logAudit('item.created', 'Added item "' . $data['name'] . '"');
                session()->setFlashdata('success', 'Item added successfully');
                return redirect()->to('/admin/items');
            } else {
                $errors = $this->itemModel->errors();
                session()->setFlashdata('error', 'Failed to add item: ' . ($errors ? implode(', ', $errors) : 'Database error'));
                session()->setFlashdata('old_input', $this->request->getPost());
                return redirect()->back();
            }
        }

        $categoryModel = new CategoryModel();
        $viewData['categories'] = $categoryModel->getActiveCategories($this->currentBusinessId());
        return view('admin/items/create', $viewData);
    }

    public function edit($id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $data['item'] = $this->itemModel->forBusiness($this->currentBusinessId())->find($id);

        if (!$data['item']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }

        if ($this->request->getMethod() === 'POST') {
            $code = trim((string) $this->request->getPost('code'));

            if ($code === '') {
                session()->setFlashdata('error', 'Item code is required.');
                return redirect()->back()->withInput();
            }

            if ($code !== $data['item']['code']
                && $this->itemModel->forBusiness($this->currentBusinessId())->where('code', $code)->first()) {
                session()->setFlashdata('error', 'Item code "' . $code . '" is already used by another item.');
                return redirect()->back()->withInput();
            }

            $updateData = [
                'id' => $id,
                'code' => $code,
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'category_id' => $this->request->getPost('category_id') ?: null,
                'price' => $this->request->getPost('price'),
                'quantity' => $this->request->getPost('quantity'),
                'unit' => in_array($this->request->getPost('unit'), ['pcs', 'kilo'], true) ? $this->request->getPost('unit') : 'pcs',
                'status' => $this->request->getPost('status'),
                'low_stock_threshold' => (int) ($this->request->getPost('low_stock_threshold') ?: 10),
                'expiration_date' => $this->request->getPost('expiration_date') ?: null,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Handle image upload
            $image = $this->request->getFile('image');
            if ($image && $image->isValid() && !$image->hasMoved()) {
                // Delete old image if exists
                if (!empty($data['item']['image']) && file_exists(FCPATH . $data['item']['image'])) {
                    unlink(FCPATH . $data['item']['image']);
                }
                $newName = $image->getRandomName();
                $image->move(FCPATH . 'uploads/items', $newName);
                $updateData['image'] = 'uploads/items/' . $newName;
            }

            if ($this->itemModel->save($updateData)) {
                $this->logAudit('item.updated', 'Updated item "' . $updateData['name'] . '"');
                session()->setFlashdata('success', 'Item updated successfully');
                return redirect()->to('/admin/items');
            } else {
                $errors = $this->itemModel->errors();
                session()->setFlashdata('error', 'Failed to update item: ' . ($errors ? implode(', ', $errors) : 'Database error'));
                return redirect()->back();
            }
        }

        $categoryModel = new CategoryModel();
        $data['categories'] = $categoryModel->getActiveCategories($this->currentBusinessId());
        return view('admin/items/edit', $data);
    }

    public function delete($id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $existing = $this->itemModel->forBusiness($this->currentBusinessId())->find($id);
        if (!$existing) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }

        if ($this->itemModel->delete($id)) {
            $this->logAudit('item.deleted', 'Deleted item "' . $existing['name'] . '"');
            session()->setFlashdata('success', 'Item deleted successfully');
        } else {
            session()->setFlashdata('error', 'Failed to delete item');
        }

        return redirect()->to('/admin/items');
    }

    /**
     * Delete many items at once (checked from the Items list). Only deletes
     * IDs that actually belong to the current business — an id sent for
     * another tenant, or a garbage value, is silently dropped rather than
     * trusted.
     */
    public function bulkDelete()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $requestedIds = $this->request->getPost('item_ids') ?? [];
        $requestedIds = array_unique(array_filter(array_map('intval', (array) $requestedIds)));

        if (empty($requestedIds)) {
            session()->setFlashdata('error', 'No items were selected.');
            return redirect()->to('/admin/items');
        }

        $businessId = $this->currentBusinessId();
        $items = $this->itemModel->forBusiness($businessId)->whereIn('id', $requestedIds)->findAll();

        if (empty($items)) {
            session()->setFlashdata('error', 'None of the selected items could be found.');
            return redirect()->to('/admin/items');
        }

        $validIds = array_column($items, 'id');
        $this->itemModel->delete($validIds);

        $names = array_column($items, 'name');
        $summary = count($names) <= 5
            ? implode(', ', array_map(fn($n) => '"' . $n . '"', $names))
            : count($names) . ' items';
        $this->logAudit('item.bulk_deleted', 'Deleted ' . count($validIds) . ' item(s): ' . $summary);

        $skippedCount = count($requestedIds) - count($validIds);
        $message = 'Deleted ' . count($validIds) . ' item(s).';
        if ($skippedCount > 0) {
            $message .= ' ' . $skippedCount . ' selected id(s) could not be found and were skipped.';
        }
        session()->setFlashdata('success', $message);

        return redirect()->to('/admin/items');
    }

    public function generateCode()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        $maxAttempts = 10;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = 'ITM-' . strtoupper(bin2hex(random_bytes(4)));
            $existing = $this->itemModel->forBusiness($this->currentBusinessId())->where('code', $code)->first();
            if (!$existing) {
                return $this->response->setJSON(['code' => $code]);
            }
        }

        return $this->response->setJSON(['error' => 'Failed to generate unique code'])->setStatusCode(500);
    }

    public function updateCategory()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        $itemId = (int) $this->request->getPost('item_id');
        $categoryId = $this->request->getPost('category_id');

        $item = $this->itemModel->forBusiness($this->currentBusinessId())->find($itemId);
        if (!$item) {
            return $this->response->setJSON(['error' => 'Item not found'])->setStatusCode(404);
        }

        if ($categoryId && !(new CategoryModel())->forBusiness($this->currentBusinessId())->find($categoryId)) {
            return $this->response->setJSON(['error' => 'Category not found'])->setStatusCode(404);
        }

        $this->itemModel->update($itemId, [
            'category_id' => $categoryId ?: null,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    public function updateDiscount()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        $itemId = (int) $this->request->getPost('item_id');
        $discount = (float) $this->request->getPost('discount');

        if ($discount < 0 || $discount > 100) {
            return $this->response->setJSON(['error' => 'Discount must be between 0 and 100'])->setStatusCode(400);
        }

        $item = $this->itemModel->forBusiness($this->currentBusinessId())->find($itemId);
        if (!$item) {
            return $this->response->setJSON(['error' => 'Item not found'])->setStatusCode(404);
        }

        $this->itemModel->update($itemId, [
            'discount' => $discount,
        ]);

        $discountedPrice = $item['price'] * (1 - $discount / 100);

        return $this->response->setJSON([
            'success' => true,
            'discount' => $discount,
            'original_price' => (float) $item['price'],
            'discounted_price' => round($discountedPrice, 2),
        ]);
    }

    /**
     * Bulk import items from an uploaded Excel/CSV file.
     */
    public function import()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        if ($this->request->getMethod() === 'POST') {
            $file = $this->request->getFile('spreadsheet');

            if (!$file || !$file->isValid()) {
                session()->setFlashdata('error', 'Please choose a valid .xlsx, .xls, or .csv file.');
                return redirect()->back();
            }

            $ext = strtolower($file->getClientExtension());
            if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
                session()->setFlashdata('error', 'Unsupported file type. Please upload .xlsx, .xls, or .csv.');
                return redirect()->back();
            }

            try {
                $spreadsheet = IOFactory::load($file->getTempName());
                $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            } catch (\Throwable $e) {
                session()->setFlashdata('error', 'Could not read that file: ' . $e->getMessage());
                return redirect()->back();
            }

            if (count($rows) < 2) {
                session()->setFlashdata('error', 'That file has no data rows below the header.');
                return redirect()->back();
            }

            $headerRow = array_shift($rows);
            $columnMap = $this->mapImportColumns($headerRow);

            if (!isset($columnMap['name'])) {
                session()->setFlashdata('error', 'Could not find a name/item column in that file\'s header row.');
                return redirect()->back();
            }

            if (count($rows) > self::IMPORT_MAX_ROWS) {
                $rows = array_slice($rows, 0, self::IMPORT_MAX_ROWS);
            }

            $businessId = $this->currentBusinessId();
            $categoryModel = new CategoryModel();
            $existingCategories = [];
            foreach ($categoryModel->forBusiness($businessId)->findAll() as $cat) {
                $existingCategories[strtolower(trim($cat['name']))] = $cat['id'];
            }

            // Codes already in use — existing items plus every code assigned
            // so far within this same import batch — so duplicates within
            // the file itself are caught, not just against the database.
            $usedCodes = [];
            foreach ($this->itemModel->forBusiness($businessId)->findAll() as $existing) {
                $usedCodes[strtolower($existing['code'])] = true;
            }

            $created = 0;
            $skipped = [];
            $noPrice = 0;
            $codesGenerated = 0;
            $userId = session()->get('user_id');

            foreach ($rows as $i => $row) {
                $rowNum = $i + 2; // account for the removed header row, 1-indexed for humans
                $get = fn($field) => isset($columnMap[$field]) ? trim((string) ($row[$columnMap[$field]] ?? '')) : '';

                $name = $get('name');
                if ($name === '') {
                    continue; // a genuinely blank row — not worth reporting as "skipped"
                }

                $quantity = (float) str_replace(',', '', $get('quantity'));

                // Never guess a price. If the file has no price column (or the
                // cell is blank) the item is imported at ₱0 — edit it manually
                // once the real price is known.
                $price = (float) str_replace(',', '', $get('price'));
                if ($price <= 0) {
                    $noPrice++;
                }

                $code = $get('code');
                if ($code === '' || isset($usedCodes[strtolower($code)])) {
                    $code = $this->generateUniqueImportCode($businessId, $usedCodes);
                    $codesGenerated++;
                }
                $usedCodes[strtolower($code)] = true;

                $categoryId = null;
                $categoryName = strtolower($get('category'));
                if ($categoryName !== '' && isset($existingCategories[$categoryName])) {
                    $categoryId = $existingCategories[$categoryName];
                }

                $unit = strtolower($get('unit'));
                $unit = in_array($unit, ['pcs', 'kilo', 'kg'], true) ? ($unit === 'kg' ? 'kilo' : $unit) : 'pcs';

                $threshold = (int) $get('low_stock_threshold') ?: 10;

                // A "total quantity" / "originally stocked" column is a
                // bookkeeping quantity, not a value — never used for price.
                // Kept only as a plain reference note on the item.
                $description = $get('description');
                $originalQty = $get('original_quantity');
                if ($originalQty !== '' && is_numeric(str_replace(',', '', $originalQty))) {
                    $note = 'Originally stocked: ' . number_format((float) str_replace(',', '', $originalQty), 0) . ' (bookkeeper record)';
                    $description = $description !== '' ? $description . ' — ' . $note : $note;
                }

                $ok = $this->itemModel->insert([
                    'business_id' => $businessId,
                    'code'        => $code,
                    'name'        => $name,
                    'description' => $description !== '' ? $description : null,
                    'category_id' => $categoryId,
                    'price'       => $price,
                    'quantity'    => $quantity,
                    'unit'        => $unit,
                    'status'      => 'active',
                    'low_stock_threshold' => $threshold,
                    'created_by'  => $userId,
                    'created_at'  => date('Y-m-d H:i:s'),
                ], true);

                if ($ok) {
                    $created++;
                } else {
                    $skipped[] = "Row {$rowNum} (\"{$name}\"): " . implode(', ', $this->itemModel->errors() ?: ['unknown error']);
                }
            }

            $this->logAudit('item.bulk_imported', "Imported {$created} item(s) from \"{$file->getClientName()}\"" . ($skipped ? ' (' . count($skipped) . ' skipped)' : ''));

            $summary = "Imported {$created} item(s).";
            if ($codesGenerated) {
                $summary .= " {$codesGenerated} needed an auto-generated code.";
            }
            if ($noPrice) {
                $summary .= " {$noPrice} had no price in the file and were imported at ₱0 — edit them once you know the real price.";
            }
            session()->setFlashdata('success', $summary);
            if ($skipped) {
                session()->setFlashdata('import_skipped', $skipped);
            }

            return redirect()->to('/admin/items');
        }

        return view('admin/items/import');
    }

    /**
     * A blank starter workbook with the columns the importer understands.
     */
    public function importTemplate()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Items');

        $headers = ['Code', 'Name', 'Category', 'Price', 'Quantity', 'Unit', 'Low Stock Threshold', 'Description'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(
            ['ITM-0001', 'Portland Cement 40kg', 'Cement', 245.00, 100, 'pcs', 10, 'Standard grade'],
            null,
            'A2'
        );
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);

        $this->response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="sdpos-sad-item-import-template.xlsx"');

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $this->response->setBody($content);
    }

    /**
     * Match a header row's cell text to the fields we know how to import,
     * ignoring case, surrounding whitespace, and internal double-spacing.
     */
    private function mapImportColumns(array $headerRow): array
    {
        $normalized = array_map(function ($h) {
            return strtolower(trim(preg_replace('/\s+/', ' ', (string) $h)));
        }, $headerRow);

        $map = [];
        foreach (self::IMPORT_COLUMN_ALIASES as $field => $aliases) {
            foreach ($normalized as $colIndex => $header) {
                if (in_array($header, $aliases, true)) {
                    $map[$field] = $colIndex;
                    break;
                }
            }
        }

        return $map;
    }

    private function generateUniqueImportCode(int $businessId, array &$usedCodes): string
    {
        do {
            $code = 'ITM-' . strtoupper(bin2hex(random_bytes(4)));
        } while (isset($usedCodes[strtolower($code)]));

        return $code;
    }
}

<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ItemModel;
use App\Models\StockMovementModel;

class StockMovements extends BaseController
{
    protected $stockMovementModel;
    protected $itemModel;

    public function __construct()
    {
        $this->stockMovementModel = new StockMovementModel();
        $this->itemModel = new ItemModel();
    }

    protected function guard()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }
        return null;
    }

    /**
     * Global, business-scoped stock ledger.
     */
    public function index()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businessId = $this->currentBusinessId();
        $movements = $this->stockMovementModel->forBusiness($businessId)->orderBy('created_at', 'DESC')->findAll(500);

        $itemNames = [];
        foreach ($movements as &$m) {
            if (!isset($itemNames[$m['item_id']])) {
                $item = $this->itemModel->find($m['item_id']);
                $itemNames[$m['item_id']] = $item['name'] ?? 'Deleted item';
            }
            $m['item_name'] = $itemNames[$m['item_id']];
        }

        return view('admin/stock_movements/index', ['movements' => $movements]);
    }

    /**
     * One item's full stock history.
     */
    public function item($itemId)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businessId = $this->currentBusinessId();
        $item = $this->itemModel->forBusiness($businessId)->find($itemId);
        if (!$item) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }

        $movements = $this->stockMovementModel->getForItem($businessId, (int) $itemId);

        return view('admin/stock_movements/item', ['item' => $item, 'movements' => $movements]);
    }

    /**
     * Manual correction — damaged, lost, recount, etc. Always requires a reason.
     */
    public function adjust()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businessId = $this->currentBusinessId();
        $itemId = (int) $this->request->getPost('item_id');
        $delta  = (float) $this->request->getPost('quantity_delta');
        $reason = trim((string) $this->request->getPost('reason'));

        $item = $this->itemModel->forBusiness($businessId)->find($itemId);
        if (!$item) {
            session()->setFlashdata('error', 'Item not found.');
            return redirect()->to('/admin/items');
        }

        if ($delta == 0 || $reason === '') {
            session()->setFlashdata('error', 'Please provide a non-zero adjustment and a reason.');
            return redirect()->to('/admin/items');
        }

        $newQty = max(0, (float) $item['quantity'] + $delta);
        $this->itemModel->update($itemId, ['quantity' => $newQty]);

        $this->stockMovementModel->log($businessId, $itemId, 'adjustment', $delta, $reason, null, session()->get('user_id'));
        $this->logAudit('item.stock_adjusted', sprintf(
            '%s stock for "%s" by %s (%s)',
            $delta > 0 ? 'Increased' : 'Decreased',
            $item['name'],
            number_format(abs($delta), 2),
            $reason
        ));

        session()->setFlashdata('success', 'Stock adjusted for "' . $item['name'] . '".');
        return redirect()->to('/admin/items');
    }
}

<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ItemModel;
use App\Models\PurchaseOrderItemModel;
use App\Models\PurchaseOrderModel;
use App\Models\StockMovementModel;
use App\Models\SupplierModel;

class PurchaseOrders extends BaseController
{
    protected $poModel;
    protected $poItemModel;
    protected $supplierModel;
    protected $itemModel;

    public function __construct()
    {
        $this->poModel       = new PurchaseOrderModel();
        $this->poItemModel   = new PurchaseOrderItemModel();
        $this->supplierModel = new SupplierModel();
        $this->itemModel     = new ItemModel();
    }

    protected function guard()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }
        return null;
    }

    public function index()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businessId = $this->currentBusinessId();
        $pos = $this->poModel->forBusiness($businessId)->orderBy('created_at', 'DESC')->findAll();

        foreach ($pos as &$po) {
            $supplier = $this->supplierModel->find($po['supplier_id']);
            $po['supplier_name'] = $supplier['name'] ?? 'Unknown supplier';
        }

        return view('admin/purchase_orders/index', ['pos' => $pos]);
    }

    public function create()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businessId = $this->currentBusinessId();

        if ($this->request->getMethod() === 'POST') {
            $supplierId = (int) $this->request->getPost('supplier_id');
            $itemIds    = $this->request->getPost('item_id') ?? [];
            $itemNames  = $this->request->getPost('item_name') ?? [];
            $quantities = $this->request->getPost('quantity') ?? [];
            $unitCosts  = $this->request->getPost('unit_cost') ?? [];

            if (!$this->supplierModel->forBusiness($businessId)->find($supplierId)) {
                session()->setFlashdata('error', 'Please choose a valid supplier.');
                return redirect()->back()->withInput();
            }

            $lines = [];
            $total = 0.0;
            foreach ($itemNames as $i => $name) {
                $name = trim((string) $name);
                $qty  = (float) ($quantities[$i] ?? 0);
                $cost = (float) ($unitCosts[$i] ?? 0);
                if ($name === '' || $qty <= 0) {
                    continue;
                }

                $itemId = !empty($itemIds[$i]) ? (int) $itemIds[$i] : null;
                // Only trust an item_id that actually belongs to this business.
                if ($itemId && !$this->itemModel->forBusiness($businessId)->find($itemId)) {
                    $itemId = null;
                }

                $subtotal = $qty * $cost;
                $total += $subtotal;
                $lines[] = [
                    'item_id'    => $itemId,
                    'item_name'  => $name,
                    'quantity'   => $qty,
                    'unit_cost'  => $cost,
                    'subtotal'   => $subtotal,
                ];
            }

            if (empty($lines)) {
                session()->setFlashdata('error', 'Add at least one line item with a name and quantity.');
                return redirect()->back()->withInput();
            }

            $poNumber = $this->poModel->generatePoNumber($businessId);
            $poId = $this->poModel->insert([
                'business_id'  => $businessId,
                'supplier_id'  => $supplierId,
                'po_number'    => $poNumber,
                'status'       => 'pending',
                'total_amount' => $total,
                'notes'        => $this->request->getPost('notes'),
                'created_by'   => session()->get('user_id'),
                'created_at'   => date('Y-m-d H:i:s'),
            ], true);

            foreach ($lines as $line) {
                $line['purchase_order_id'] = $poId;
                $this->poItemModel->insert($line);
            }

            $this->logAudit('purchase_order.created', "Created purchase order {$poNumber} (" . count($lines) . ' item(s), ₱' . number_format($total, 2) . ')');
            session()->setFlashdata('success', "Purchase order {$poNumber} created and awaiting approval.");
            return redirect()->to('/admin/purchase-orders');
        }

        $data['suppliers'] = $this->supplierModel->getActiveSuppliers($businessId);
        $data['items']     = $this->itemModel->forBusiness($businessId)->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        return view('admin/purchase_orders/create', $data);
    }

    public function view($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businessId = $this->currentBusinessId();
        $po = $this->poModel->forBusiness($businessId)->find($id);
        if (!$po) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Purchase order not found');
        }

        $po['supplier'] = $this->supplierModel->find($po['supplier_id']);
        $lines = $this->poItemModel->forPurchaseOrder($id);

        return view('admin/purchase_orders/view', ['po' => $po, 'lines' => $lines]);
    }

    public function approve($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $po = $this->poModel->forBusiness($this->currentBusinessId())->find($id);
        if ($po && $po['status'] === 'pending') {
            $this->poModel->update($id, [
                'status'      => 'approved',
                'approved_by' => session()->get('user_id'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $this->logAudit('purchase_order.approved', "Approved purchase order {$po['po_number']}");
            session()->setFlashdata('success', "Purchase order {$po['po_number']} approved.");
        }

        return redirect()->to('/admin/purchase-orders/view/' . $id);
    }

    /**
     * Receiving is the key inventory event: every linked line item's stock
     * is increased and a stock_movements row is written for each.
     */
    public function receive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businessId = $this->currentBusinessId();
        $po = $this->poModel->forBusiness($businessId)->find($id);
        if (!$po || $po['status'] !== 'approved') {
            session()->setFlashdata('error', 'Only an approved purchase order can be received.');
            return redirect()->to('/admin/purchase-orders');
        }

        $lines = $this->poItemModel->forPurchaseOrder($id);
        $stockMovementModel = new StockMovementModel();
        $userId = session()->get('user_id');

        foreach ($lines as $line) {
            if (empty($line['item_id'])) {
                continue; // not linked to a catalogued item — nothing to restock automatically
            }

            $item = $this->itemModel->forBusiness($businessId)->find($line['item_id']);
            if (!$item) {
                continue;
            }

            $newQty = (float) $item['quantity'] + (float) $line['quantity'];
            $this->itemModel->update($line['item_id'], ['quantity' => $newQty]);

            $stockMovementModel->log(
                $businessId,
                (int) $line['item_id'],
                'restock',
                (float) $line['quantity'],
                'Received from purchase order',
                $po['po_number'],
                $userId
            );
        }

        $this->poModel->update($id, [
            'status'      => 'received',
            'received_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->logAudit('purchase_order.received', "Received purchase order {$po['po_number']} — stock updated");
        session()->setFlashdata('success', "Purchase order {$po['po_number']} received — stock levels updated.");
        return redirect()->to('/admin/purchase-orders/view/' . $id);
    }

    public function cancel($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $po = $this->poModel->forBusiness($this->currentBusinessId())->find($id);
        if ($po && in_array($po['status'], ['draft', 'pending', 'approved'], true)) {
            $this->poModel->update($id, ['status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
            $this->logAudit('purchase_order.cancelled', "Cancelled purchase order {$po['po_number']}");
            session()->setFlashdata('success', "Purchase order {$po['po_number']} cancelled.");
        }

        return redirect()->to('/admin/purchase-orders');
    }

    /** Statuses whose purchase orders may be deleted. */
    public const DELETABLE_STATUSES = ['draft', 'pending', 'cancelled'];

    /**
     * Permanently delete a purchase order and its lines.
     * Received orders are kept: their stock was added and they appear in the
     * Book of Accounts. Approved orders must be cancelled first.
     */
    public function delete($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/purchase-orders');
        }

        $po = $this->poModel->forBusiness($this->currentBusinessId())->find($id);
        if (!$po) {
            session()->setFlashdata('error', 'Purchase order not found.');
            return redirect()->to('/admin/purchase-orders');
        }

        if (!in_array($po['status'], self::DELETABLE_STATUSES, true)) {
            session()->setFlashdata('error', $po['status'] === 'received'
                ? "{$po['po_number']} was already received, so it can't be deleted. Its stock and accounting records depend on it."
                : "Cancel {$po['po_number']} first, then delete it.");
            return redirect()->to('/admin/purchase-orders');
        }

        $db = db_connect();
        $db->transStart();
        $db->table('purchase_order_items')->where('purchase_order_id', $po['id'])->delete();
        $this->poModel->delete($po['id']);
        $db->transComplete();

        if (!$db->transStatus()) {
            session()->setFlashdata('error', "Could not delete {$po['po_number']}. Nothing was removed.");
            return redirect()->to('/admin/purchase-orders');
        }

        $this->logAudit('purchase_order.deleted', "Deleted {$po['status']} purchase order {$po['po_number']} (₱" . number_format((float) $po['total_amount'], 2) . ')');
        session()->setFlashdata('success', "Purchase order {$po['po_number']} deleted.");
        return redirect()->to('/admin/purchase-orders');
    }
}

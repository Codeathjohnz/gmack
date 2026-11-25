<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderItemModel extends Model
{
    protected $table            = 'purchase_order_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['purchase_order_id', 'item_id', 'item_name', 'quantity', 'unit_cost', 'subtotal'];

    protected $useTimestamps = false;

    public function forPurchaseOrder(int $purchaseOrderId)
    {
        return $this->where('purchase_order_id', $purchaseOrderId)->findAll();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table            = 'purchase_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['business_id', 'supplier_id', 'po_number', 'status', 'total_amount', 'notes', 'created_by', 'approved_by', 'received_at', 'created_at', 'updated_at'];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function forBusiness(?int $businessId)
    {
        // No business context yet (e.g. a superadmin who has not switched
        // into a business) — do not filter, rather than crash or hide everything.
        if ($businessId === null) {
            return $this;
        }

        return $this->where('business_id', $businessId);
    }

    /**
     * PO-YYYYMMDD-#### — unique per business, human readable.
     */
    public function generatePoNumber(int $businessId): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $count  = $this->where('business_id', $businessId)
            ->like('po_number', $prefix, 'after')
            ->countAllResults();

        return $prefix . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }
}

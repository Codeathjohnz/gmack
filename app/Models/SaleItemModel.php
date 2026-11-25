<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleItemModel extends Model
{
    protected $table            = 'sale_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['business_id', 'sale_id', 'item_id', 'quantity', 'price', 'subtotal'];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function forBusiness(?int $businessId)
    {
        if ($businessId === null) {
            return $this;
        }

        return $this->where('business_id', $businessId);
    }

    public function getSaleItems($saleId)
    {
        return $this->join('items', 'sale_items.item_id = items.id')
                    ->where('sale_items.sale_id', $saleId)
                    ->select('sale_items.*, items.name, items.code')
                    ->findAll();
    }

    public function getSoldItemsForDateRange($businessId, $dateFrom = null, $dateTo = null, $itemCode = null)
    {
        $builder = $this->db->table('sale_items')
            ->select('items.id as item_id, items.code, items.name, SUM(sale_items.quantity) as total_qty, SUM(sale_items.subtotal) as total_amount, COUNT(DISTINCT sales.id) as transaction_count')
            ->join('items', 'sale_items.item_id = items.id')
            ->join('sales', 'sale_items.sale_id = sales.id')
            ->where('sales.business_id', $businessId)
            ->where('sales.status', 'completed');

        if ($dateFrom) {
            $builder->where('DATE(sales.created_at) >=', $dateFrom);
        }
        if ($dateTo) {
            $builder->where('DATE(sales.created_at) <=', $dateTo);
        }
        if ($itemCode) {
            $builder->where('items.code', $itemCode);
        }

        return $builder->groupBy('sale_items.item_id')
                       ->orderBy('total_qty', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    public function getSoldItemsByPaymentMethod($businessId, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table('sale_items')
            ->select('sales.payment_method, items.code, items.name, SUM(sale_items.quantity) as total_qty, SUM(sale_items.subtotal) as total_amount')
            ->join('items', 'sale_items.item_id = items.id')
            ->join('sales', 'sale_items.sale_id = sales.id')
            ->where('sales.business_id', $businessId)
            ->where('sales.status', 'completed');

        if ($dateFrom) {
            $builder->where('DATE(sales.created_at) >=', $dateFrom);
        }
        if ($dateTo) {
            $builder->where('DATE(sales.created_at) <=', $dateTo);
        }

        return $builder->groupBy('sales.payment_method, sale_items.item_id')
                       ->orderBy('sales.payment_method', 'ASC')
                       ->orderBy('total_qty', 'DESC')
                       ->get()
                       ->getResultArray();
    }
}

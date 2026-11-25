<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table            = 'sales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['business_id', 'reference_no', 'total_amount', 'discount', 'net_amount', 'payment_method', 'payment_reference', 'amount_received', 'change_amount', 'cashier_id', 'status', 'created_at', 'updated_at'];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

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

    public function getSaleWithItems($saleId)
    {
        return $this->find($saleId);
    }

    public function getSalesForDateRange($businessId, $startDate, $endDate)
    {
        return $this->forBusiness($businessId)
                    ->where('DATE(created_at) >=', $startDate)
                    ->where('DATE(created_at) <=', $endDate)
                    ->findAll();
    }
}

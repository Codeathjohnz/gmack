<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table            = 'items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['business_id', 'code', 'name', 'description', 'image', 'category_id', 'price', 'discount', 'quantity', 'unit', 'low_stock_threshold', 'status', 'expiration_date', 'created_by', 'created_at', 'updated_at'];

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

    /**
     * Scope any subsequent query to a single business (tenant).
     */
    public function forBusiness(?int $businessId)
    {
        // No business context yet (e.g. a superadmin who has not switched
        // into a business) — do not filter, rather than crash or hide everything.
        if ($businessId === null) {
            return $this;
        }

        return $this->where('business_id', $businessId);
    }

    public function getActiveItems(?int $businessId = null)
    {
        $query = $this->where('status', 'active');
        if ($businessId !== null) {
            $query->where('business_id', $businessId);
        }
        return $query->findAll();
    }

    public function getItemByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    public function getLowStockItems(int $threshold = 10)
    {
        return $this->where('quantity <=', $threshold)
                     ->where('quantity >', 0)
                     ->where('status', 'active')
                     ->findAll();
    }

    public function getCriticalStockItems(int $threshold = 5)
    {
        return $this->where('quantity <=', $threshold)
                     ->where('status', 'active')
                     ->findAll();
    }
}

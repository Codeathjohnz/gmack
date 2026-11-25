<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['business_id', 'name', 'description', 'color', 'status', 'created_at', 'updated_at'];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function forBusiness(?int $businessId)
    {
        if ($businessId === null) {
            return $this;
        }

        return $this->where('business_id', $businessId);
    }

    public function getActiveCategories(?int $businessId = null)
    {
        $query = $this->where('status', 'active');
        if ($businessId !== null) {
            $query->where('business_id', $businessId);
        }
        return $query->orderBy('name', 'ASC')->findAll();
    }
}

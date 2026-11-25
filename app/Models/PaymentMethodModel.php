<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentMethodModel extends Model
{
    protected $table            = 'payment_methods';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['business_id', 'name', 'type', 'account_name', 'account_number', 'qr_code', 'icon', 'status', 'created_at', 'updated_at'];

    public function forBusiness(?int $businessId)
    {
        if ($businessId === null) {
            return $this;
        }

        return $this->where('business_id', $businessId);
    }

    public function getActiveMethods(?int $businessId = null)
    {
        $query = $this->where('status', 'active');
        if ($businessId !== null) {
            $query->where('business_id', $businessId);
        }
        return $query->findAll();
    }
}

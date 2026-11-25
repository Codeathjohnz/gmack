<?php

namespace App\Models;

use CodeIgniter\Model;

class StockMovementModel extends Model
{
    protected $table            = 'stock_movements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['business_id', 'item_id', 'movement_type', 'quantity', 'reason', 'reference', 'user_id', 'created_at'];

    protected $useTimestamps = false;

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
     * Record one movement. $quantity is signed (+in / -out).
     */
    public function log(int $businessId, int $itemId, string $type, float $quantity, ?string $reason = null, ?string $reference = null, ?int $userId = null): void
    {
        $this->insert([
            'business_id'   => $businessId,
            'item_id'       => $itemId,
            'movement_type' => $type,
            'quantity'      => $quantity,
            'reason'        => $reason,
            'reference'     => $reference,
            'user_id'       => $userId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function getForItem(int $businessId, int $itemId)
    {
        return $this->where('business_id', $businessId)
            ->where('item_id', $itemId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}

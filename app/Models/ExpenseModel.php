<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseModel extends Model
{
    protected $table            = 'expenses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['business_id', 'category', 'description', 'amount', 'expense_date', 'created_by', 'created_at', 'updated_at'];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Common expense categories offered in the UI dropdown. Stored as plain
     * text (not an enum) so a custom category typed in the "Other" field
     * doesn't require a migration to support.
     */
    public const CATEGORIES = [
        'Rent',
        'Electricity',
        'Water',
        'Internet / Load',
        'Supplies',
        'Salaries',
        'Transportation',
        'Maintenance & Repairs',
        'Other',
    ];

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
     * Total expenses for a business within an optional date range.
     */
    public function getTotal(?int $businessId, ?string $dateFrom = null, ?string $dateTo = null): float
    {
        $builder = $this->forBusiness($businessId);

        if ($dateFrom) {
            $builder->where('expense_date >=', $dateFrom);
        }
        if ($dateTo) {
            $builder->where('expense_date <=', $dateTo);
        }

        return (float) ($builder->selectSum('amount')->first()['amount'] ?? 0);
    }

    /**
     * Totals grouped by category, for the expenses report breakdown.
     */
    public function getTotalsByCategory(?int $businessId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $builder = $this->forBusiness($businessId)
            ->select('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('total', 'DESC');

        if ($dateFrom) {
            $builder->where('expense_date >=', $dateFrom);
        }
        if ($dateTo) {
            $builder->where('expense_date <=', $dateTo);
        }

        return $builder->findAll();
    }
}

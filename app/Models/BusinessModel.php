<?php

namespace App\Models;

use CodeIgniter\Model;

class BusinessModel extends Model
{
    protected $table            = 'businesses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'slug', 'industry', 'address', 'phone', 'status', 'owner_user_id', 'created_at', 'updated_at'];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Turn "G-Mack Construction Supply" into a unique "g-mack-construction-supply"
     * slug, appending -2, -3, ... if the base slug is already taken.
     */
    public function generateUniqueSlug(string $name): string
    {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        $base = $base !== '' ? $base : 'business';

        $slug = $base;
        $i    = 2;
        while ($this->where('slug', $slug)->first()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function getActiveBusinesses()
    {
        return $this->where('status', 'active')->orderBy('name', 'ASC')->findAll();
    }
}

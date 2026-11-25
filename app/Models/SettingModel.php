<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['business_id', 'key', 'value'];

    public function forBusiness(?int $businessId)
    {
        // No business context yet (e.g. a superadmin who has not switched
        // into a business) — do not filter, rather than crash or hide everything.
        if ($businessId === null) {
            return $this;
        }

        return $this->where('business_id', $businessId);
    }

    public function getSetting(int $businessId, string $key, $default = null)
    {
        $row = $this->where('business_id', $businessId)->where('key', $key)->first();
        return $row ? $row['value'] : $default;
    }

    public function setSetting(int $businessId, string $key, string $value)
    {
        $row = $this->where('business_id', $businessId)->where('key', $key)->first();
        if ($row) {
            $this->update($row['id'], ['value' => $value]);
        } else {
            $this->insert(['business_id' => $businessId, 'key' => $key, 'value' => $value]);
        }
    }
}

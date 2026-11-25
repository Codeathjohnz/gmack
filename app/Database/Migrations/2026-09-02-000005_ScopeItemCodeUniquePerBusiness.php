<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * items.code was globally unique (one code, one business, forever) — meaning
 * two unrelated businesses couldn't both use "CEMENT-40KG" even though they
 * have nothing to do with each other. Re-key it to (business_id, code) so
 * uniqueness is enforced per tenant, matching every other business_id-scoped
 * table.
 */
class ScopeItemCodeUniquePerBusiness extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE items DROP INDEX `code`');
        $this->db->query('ALTER TABLE items ADD UNIQUE KEY items_business_code_unique (business_id, code)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE items DROP INDEX items_business_code_unique');
        $this->db->query('ALTER TABLE items ADD UNIQUE KEY `code` (`code`)');
    }
}

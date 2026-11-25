<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Scopes every tenant-owned table to a business. Columns are added nullable
 * so the migration is safe to run against an existing database; the
 * MultiTenantSeeder backfills business_id = 1 (G-Mack) on all pre-existing
 * rows immediately after. Every controller write going forward always sets
 * business_id explicitly.
 */
class AddBusinessIdToCoreTables extends Migration
{
    protected array $tables = ['items', 'categories', 'sales', 'sale_items', 'payment_methods', 'settings'];

    public function up()
    {
        foreach ($this->tables as $table) {
            $this->forge->addColumn($table, [
                'business_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'id',
                ],
            ]);
            $this->db->query("ALTER TABLE {$table} ADD CONSTRAINT {$table}_business_id_foreign FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE");
            $this->db->query("ALTER TABLE {$table} ADD INDEX {$table}_business_id_index (business_id)");
        }

        // settings.key was globally unique; each business needs its own
        // 'or_prefix' / 'or_next_number' rows, so re-key to (business_id, key).
        $this->db->query('ALTER TABLE settings DROP INDEX `key`');
        $this->db->query('ALTER TABLE settings ADD UNIQUE KEY settings_business_key_unique (business_id, `key`)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE settings DROP INDEX settings_business_key_unique');
        $this->db->query('ALTER TABLE settings ADD UNIQUE KEY `key` (`key`)');

        foreach ($this->tables as $table) {
            $this->db->query("ALTER TABLE {$table} DROP FOREIGN KEY {$table}_business_id_foreign");
            $this->forge->dropColumn($table, 'business_id');
        }
    }
}

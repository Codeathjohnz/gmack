<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('settings');

        // Seed default OR number
        $this->db->table('settings')->insertBatch([
            ['key' => 'or_next_number', 'value' => '1'],
            ['key' => 'or_prefix', 'value' => 'OR-'],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}

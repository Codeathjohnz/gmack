<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitToItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items', [
            'unit' => [
                'type'       => 'ENUM',
                'constraint' => ['pcs', 'kilo'],
                'default'    => 'pcs',
                'null'       => false,
                'after'      => 'quantity',
            ],
        ]);

        // Quantity needs to support fractional amounts for kilo-based items
        // (e.g. 25.50 kg), so widen it from a plain integer to a decimal.
        $this->forge->modifyColumn('items', [
            'quantity' => [
                'name'       => 'quantity',
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('items', [
            'quantity' => [
                'name'       => 'quantity',
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
        ]);

        $this->forge->dropColumn('items', 'unit');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLowStockThresholdToItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items', [
            'low_stock_threshold' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 10,
                'null'       => false,
                'after'      => 'quantity',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('items', 'low_stock_threshold');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAmountReceivedAndChangeAmountToSales extends Migration
{
    public function up()
    {
        $fields = [
            'amount_received' => [
                'type' => 'DECIMAL',
                'constraint' => [10, 2],
                'default' => 0,
            ],
            'change_amount' => [
                'type' => 'DECIMAL',
                'constraint' => [10, 2],
                'default' => 0,
            ],
        ];

        $this->forge->addColumn('sales', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('sales', ['amount_received', 'change_amount']);
    }
}

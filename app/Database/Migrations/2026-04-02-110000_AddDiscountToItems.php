<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiscountToItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items', [
            'discount' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0,
                'after'      => 'price',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('items', 'discount');
    }
}

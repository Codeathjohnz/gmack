<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaleItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sale_id'  => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'item_id'  => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'price'    => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
            ],
        ]);
        $this->forge->addKey('id', false, true);
        $this->forge->addForeignKey('sale_id', 'sales', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id', '', 'CASCADE');
        $this->forge->createTable('sale_items');
    }

    public function down()
    {
        $this->forge->dropTable('sale_items');
    }
}

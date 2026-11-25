<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesTable extends Migration
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
            'reference_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
            ],
            'discount'     => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
                'default'    => 0,
            ],
            'net_amount'   => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'cashier_id'   => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status'       => [
                'type'       => 'ENUM',
                'constraint' => ['completed', 'cancelled'],
                'default'    => 'completed',
            ],
            'created_at'  => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at'  => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', false, true);
        $this->forge->addForeignKey('cashier_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('sales');
    }

    public function down()
    {
        $this->forge->dropTable('sales');
    }
}

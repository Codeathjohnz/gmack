<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchaseOrdersTables extends Migration
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
            'business_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'supplier_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'po_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'pending', 'approved', 'received', 'cancelled'],
                'default'    => 'draft',
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
                'default'    => 0,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'received_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('business_id');
        $this->forge->addUniqueKey(['business_id', 'po_number']);
        $this->forge->addForeignKey('business_id', 'businesses', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', '', 'CASCADE');
        $this->forge->createTable('purchase_orders');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'purchase_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'item_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
            ],
            'unit_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('purchase_order_id');
        $this->forge->addForeignKey('purchase_order_id', 'purchase_orders', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('purchase_order_items');
    }

    public function down()
    {
        $this->forge->dropTable('purchase_order_items');
        $this->forge->dropTable('purchase_orders');
    }
}

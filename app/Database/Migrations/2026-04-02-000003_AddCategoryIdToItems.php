<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryIdToItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items', [
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'image',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('items', 'category_id');
    }
}

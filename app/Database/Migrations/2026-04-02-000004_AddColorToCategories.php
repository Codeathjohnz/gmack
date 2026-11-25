<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColorToCategories extends Migration
{
    public function up()
    {
        $this->forge->addColumn('categories', [
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'default'    => '#6c757d',
                'after'      => 'description',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('categories', 'color');
    }
}

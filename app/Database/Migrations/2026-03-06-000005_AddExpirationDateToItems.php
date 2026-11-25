<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpirationDateToItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items', [
            'expiration_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Item expiration date',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('items', 'expiration_date');
    }
}

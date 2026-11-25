<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBusinessIdToUsers extends Migration
{
    public function up()
    {
        // A superadmin (platform-level) user has business_id = NULL;
        // admin/cashier users belong to exactly one business.
        $this->forge->addColumn('users', [
            'business_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        // Widen the role enum to add the platform-level 'superadmin' role.
        $this->forge->modifyColumn('users', [
            'role' => [
                'name'       => 'role',
                'type'       => 'ENUM',
                'constraint' => ['superadmin', 'admin', 'cashier'],
                'default'    => 'cashier',
            ],
        ]);

        $this->db->query('ALTER TABLE users ADD CONSTRAINT users_business_id_foreign FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE users DROP FOREIGN KEY users_business_id_foreign');
        $this->forge->dropColumn('users', 'business_id');

        $this->forge->modifyColumn('users', [
            'role' => [
                'name'       => 'role',
                'type'       => 'ENUM',
                'constraint' => ['admin', 'cashier'],
                'default'    => 'cashier',
            ],
        ]);
    }
}

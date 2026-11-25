<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FixAdminPasswordSeeder extends Seeder
{
    public function run()
    {
        // Update admin user with correct password hash
        $correctHash = '$2y$10$acpAlUVJ719AGL0oRANQZeltX.PxmG5jNCcaKcSIbjjZT7nBcEDba';
        
        $this->db->table('users')
            ->where('username', 'admin')
            ->update(['password' => $correctHash]);
        
        // Update cashier users with correct password hash
        $this->db->table('users')
            ->where('username', 'cashier')
            ->update(['password' => password_hash('cashier123', PASSWORD_BCRYPT)]);
            
        $this->db->table('users')
            ->where('username', 'cashier2')
            ->update(['password' => password_hash('cashier123', PASSWORD_BCRYPT)]);
        
        echo "✅ User passwords have been fixed!\n";
        echo "Admin - Username: admin, Password: admin123\n";
        echo "Cashier - Username: cashier, Password: cashier123\n";
        echo "Cashier 2 - Username: cashier2, Password: cashier123\n";
    }
}

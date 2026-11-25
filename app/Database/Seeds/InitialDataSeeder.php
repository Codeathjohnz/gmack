<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Sample Users
        $users = [
            ['username' => 'admin', 'email' => 'admin@pos.local', 'password' => password_hash('admin123', PASSWORD_BCRYPT), 'role' => 'admin', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['username' => 'cashier', 'email' => 'cashier@pos.local', 'password' => password_hash('cashier123', PASSWORD_BCRYPT), 'role' => 'cashier', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['username' => 'cashier2', 'email' => 'cashier2@pos.local', 'password' => password_hash('cashier123', PASSWORD_BCRYPT), 'role' => 'cashier', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('users')->insertBatch($users);

        // Sample Items (Construction & Hardware Products in Philippine Peso)
        $items = [
            ['code' => 'NAIL001', 'name' => 'Galvanized Nails 2.5" (1 lb)', 'description' => 'Common galvanized nails', 'price' => 225.00, 'quantity' => 150, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+2 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'SCREW001', 'name' => 'Wood Screws #8 x 2.5" (1 lb)', 'description' => 'Premium wood screws', 'price' => 310.00, 'quantity' => 120, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+2 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'LUMBER001', 'name' => '2x4 Stud 8ft Lumber', 'description' => 'Standard grade lumber', 'price' => 395.00, 'quantity' => 80, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+3 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'PLYWOOD001', 'name' => 'Plywood Sheet 3/4" x 4x8', 'description' => 'Standard plywood', 'price' => 2580.00, 'quantity' => 40, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+5 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'PAINT001', 'name' => 'Latex Paint Gallon - White', 'description' => 'Latex paint', 'price' => 1680.00, 'quantity' => 60, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+1 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'CEMENT001', 'name' => 'Portland Cement 50 lb Bag', 'description' => 'Cement for mixing', 'price' => 700.00, 'quantity' => 200, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+6 months')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'HAMMER001', 'name' => 'Claw Hammer 16oz', 'description' => 'Professional hammer', 'price' => 1065.00, 'quantity' => 35, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+10 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'DRILL001', 'name' => 'Power Drill 20V Cordless', 'description' => 'Cordless drill kit', 'price' => 4480.00, 'quantity' => 25, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+5 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'PIPE001', 'name' => '1/2" PVC Pipe 10ft', 'description' => 'PVC piping', 'price' => 475.00, 'quantity' => 90, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+20 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'HINGE001', 'name' => 'Brass Hinges 2.5" (Pair)', 'description' => 'Door hinges', 'price' => 450.00, 'quantity' => 110, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+15 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'WIRE001', 'name' => '12 AWG Electrical Wire 50ft', 'description' => 'Electrical wire', 'price' => 1400.00, 'quantity' => 55, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+25 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'SAFETY001', 'name' => 'Safety Glasses (Pack of 12)', 'description' => 'Safety glasses', 'price' => 895.00, 'quantity' => 75, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+3 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'DRYWALL001', 'name' => 'Drywall Sheet 1/2" x 4x8', 'description' => 'Interior drywall', 'price' => 925.00, 'quantity' => 70, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+10 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'SAND001', 'name' => 'Quikrete Concrete Mix 60 lb', 'description' => 'Concrete mix', 'price' => 335.00, 'quantity' => 180, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+1 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'LADDER001', 'name' => 'Aluminum Ladder 6ft (Type II)', 'description' => 'Step ladder', 'price' => 2800.00, 'quantity' => 30, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('+5 years')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'PAINT002', 'name' => 'Acrylic Paint Gallon - Blue (EXPIRED)', 'description' => 'Expired acrylic paint', 'price' => 1500.00, 'quantity' => 15, 'status' => 'active', 'expiration_date' => date('Y-m-d', strtotime('-1 days')), 'created_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('items')->insertBatch($items);

        echo "✅ G-Mack Construction Supply database seeded!";
    }
}

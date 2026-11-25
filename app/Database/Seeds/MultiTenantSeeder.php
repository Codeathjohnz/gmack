<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * One-time migration seeder that turns the existing single-tenant data into
 * "business #1" and adds a platform-level superadmin account.
 *
 * Safe to re-run: every step is guarded so it only inserts/backfills what's
 * missing.
 */
class MultiTenantSeeder extends Seeder
{
    public function run()
    {
        $businesses = $this->db->table('businesses');

        $gmack = $businesses->where('slug', 'g-mack')->get()->getRowArray();

        if (!$gmack) {
            $businesses->insert([
                'name'       => 'G-Mack Construction Supply',
                'slug'       => 'g-mack',
                'industry'   => 'Hardware & Construction Supply',
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $businessId = $this->db->insertID();
            echo "✅ Created business #{$businessId}: G-Mack Construction Supply\n";
        } else {
            $businessId = $gmack['id'];
            echo "ℹ️  G-Mack business already exists (#{$businessId})\n";
        }

        // Backfill every pre-existing tenant-owned row onto G-Mack.
        foreach (['users', 'items', 'categories', 'sales', 'sale_items', 'payment_methods', 'settings'] as $table) {
            $this->db->table($table)
                ->where('business_id', null)
                ->update(['business_id' => $businessId]);
        }
        echo "✅ Backfilled existing data onto business #{$businessId}\n";

        // Point the business at its owning admin, if one exists.
        $owner = $this->db->table('users')
            ->where('business_id', $businessId)
            ->where('role', 'admin')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if ($owner) {
            $businesses->where('id', $businessId)->update(['owner_user_id' => $owner['id']]);
        }

        // Platform-level superadmin (business_id stays NULL).
        $existingSuper = $this->db->table('users')->where('username', 'superadmin')->get()->getRowArray();

        if (!$existingSuper) {
            // Password comes from the SUPERADMIN_PASSWORD env variable; if it
            // is not set, a random one is generated and printed once.
            $superPassword = getenv('SUPERADMIN_PASSWORD') ?: bin2hex(random_bytes(8));
            $this->db->table('users')->insert([
                'business_id' => null,
                'username'    => 'superadmin',
                'email'       => 'superadmin@sdpos-sad.app',
                'password'    => password_hash($superPassword, PASSWORD_DEFAULT),
                'role'        => 'superadmin',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            echo "✅ Created platform superadmin — username: superadmin / password: {$superPassword}\n";
        } else {
            echo "ℹ️  superadmin account already exists\n";
        }

        echo "✅ Multi-tenant migration complete.\n";
    }
}

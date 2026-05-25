<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed a default admin user.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email'     => 'admin@barangay.gov.ph',
                'full_name' => 'System Administrator',
                'password'  => 'password',
                'is_active' => true,
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $staff = User::firstOrCreate(
            ['username' => 'staff'],
            [
                'email'     => 'staff@barangay.gov.ph',
                'full_name' => 'Default Staff',
                'password'  => 'password',
                'is_active' => true,
            ]
        );

        if (! $staff->hasRole('staff')) {
            $staff->assignRole('staff');
        }
    }
}

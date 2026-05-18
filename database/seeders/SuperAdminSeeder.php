<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'  => 'Super Admin',
            'email' => env('SUPERADMIN_EMAIL', 'superadmin@yourdomain.com'),
            'role'  => 'superadmin',
            'status' => 'active',
        ]);
    }
}

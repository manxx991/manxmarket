<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultPassword = config('auth.seed_default_password', 'password123');
        $hashedPassword = \Illuminate\Support\Facades\Hash::make($defaultPassword);

        // Seed default initial accounts if not present
        User::firstOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Owner Toko',
                'password' => $hashedPassword,
                'role' => 'owner',
                'is_active' => 1,
                'credit_limit' => 0,
                'created_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir Utama',
                'password' => $hashedPassword,
                'role' => 'kasir',
                'is_active' => 1,
                'credit_limit' => 0,
                'created_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['username' => 'gudang'],
            [
                'name' => 'Staf Gudang',
                'password' => $hashedPassword,
                'role' => 'gudang',
                'is_active' => 1,
                'credit_limit' => 0,
                'created_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['username' => 'pembeli'],
            [
                'name' => 'Pelanggan Grosir Bpk. H. Ahmad',
                'password' => $hashedPassword,
                'role' => 'pembeli',
                'is_active' => 1,
                'credit_limit' => 10000000.00,
                'created_at' => now(),
            ]
        );
    }
}

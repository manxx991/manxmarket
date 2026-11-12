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
        // Seed default initial accounts if not present
        User::firstOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Owner Toko',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
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
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
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
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
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
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'pembeli',
                'is_active' => 1,
                'credit_limit' => 10000000.00,
                'created_at' => now(),
            ]
        );
    }
}

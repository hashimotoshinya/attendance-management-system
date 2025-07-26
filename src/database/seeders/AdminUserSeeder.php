<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('adminadmin'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('admin_users')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'email' => 'admin@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
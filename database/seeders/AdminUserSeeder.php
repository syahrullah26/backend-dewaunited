<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@dewaunited.com',
            ],
            [
                'name' => 'Super Admin',
                'phone' => '081234567890',
                'password' => Hash::make('password123'),
                'role' => User::ADMIN,
                'avatar' => null,
            ]
        );
    }
}

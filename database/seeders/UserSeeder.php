<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'user@dewaunited.com',
            ],
            [
                'name' => 'User Testing',
                'phone' => '0812345678910',
                'password' => Hash::make('password123'),
                'role' => User::USER,
                'avatar' => null,
            ]
        );
    }
}

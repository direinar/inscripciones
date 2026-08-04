<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrInsert(
            ['email' => 'diego.reina9@hotmail.com'],
            [
                'name' => 'Administrador',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrInsert(
            ['email' => 'mercadeo@example.com'],
            [
                'name' => 'Mercadeo',
                'role' => 'mercadeo',
                'password' => Hash::make('password123'),
            ]
        );
    }
}

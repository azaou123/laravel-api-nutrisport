<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'id' => 1,
            'name' => 'Admin Agent',
            'email' => 'admin@nutrisport.test',
            'password' => Hash::make('password'),
            'type' => 'agent'
        ]);

        User::create([
            'name' => 'Demo Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'type' => 'customer'
        ]);
    }
}


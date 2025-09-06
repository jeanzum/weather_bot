<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Usuario Demo',
            'email' => 'demo@weatherchat.com',
            'password' => bcrypt('password'),
        ]);
    }
}
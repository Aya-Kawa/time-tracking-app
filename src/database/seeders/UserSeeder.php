<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'user1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'admin_status' => false,
        ]);

        User::create([
            'name' => 'user2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'admin_status' => false,
        ]);

        User::create([
            'name' => 'user3',
            'email' => 'user3@example.com',
            'password' => bcrypt('password'),
            'admin_status' => true,
        ]);

    }
}

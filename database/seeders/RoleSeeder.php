<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['role' => 'SuperUser', 'superuser' => true, 'admin' => false,
                'user' => false, 'banned' => false],
            ['role' => 'Admin', 'superuser' => false, 'admin' => true,
                'user' => false, 'banned' => false],
            ['role' => 'User', 'superuser' => false, 'admin' => false,
                'user' => true, 'banned' => false],
            ['role' => 'Banned', 'superuser' => false, 'admin' => false,
                'user' => false, 'banned' => true],
        ];

        DB::table('roles')->insert($roles);
    }
}

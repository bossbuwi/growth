<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigurationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $configurations = [
            [
                'code' => 'frontlog', 'name' => 'Frontend Logging', 'app' => 'F',
                'description' => 'Determines whether the frontend app logging is activated.',
                'type' => 'boolean', 'current_value' => true, 'default_value' => false, 'accepted_values' => 'true, false',
                'last_modified_by' => 'superuser', 'last_modified_on' => now()
            ],
            [
                'code' => 'backlog', 'name' => 'Backend Logging', 'app' => 'B',
                'description' => 'Determines whether the backend app logging is activated.',
                'type' => 'boolean', 'current_value' => true, 'default_value' => false, 'accepted_values' => 'true, false',
                'last_modified_by' => 'superuser', 'last_modified_on' => now()
            ],
            [
                'code' => 'tabdesign', 'name' => 'Navigation Tab Design', 'app' => 'F',
                'description' => 'Changes the design of the navigation tabs.',
                'type' => 'alpha', 'current_value' => 'M', 'default_value' => 'M', 'accepted_values' => 'C, M',
                'last_modified_by' => 'superuser', 'last_modified_on' => now()
            ]
        ];

        DB::table('configurations')->insert($configurations);
    }
}

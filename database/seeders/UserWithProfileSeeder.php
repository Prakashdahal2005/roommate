<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserWithProfileSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Create users and profiles together using the ProfileFactory
            // This automatically creates a User for each Profile due to the 'user_id' => User::factory() definition
            \App\Models\Profile::factory()->count(100)->create();
        });
    }
}
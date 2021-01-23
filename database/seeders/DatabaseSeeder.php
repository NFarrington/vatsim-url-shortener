<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DomainSeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(UrlSeeder::class);
    }
}

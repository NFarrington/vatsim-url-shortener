<?php

namespace Database\Seeders;

use App\Models\Url;
use App\Models\User;
use Illuminate\Database\Seeder;

class UrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = User::count();
        foreach (User::inRandomOrder()->limit(ceil($count / 2)) as $user) {
            create(Url::class, ['user_id' => $user->id], 10);
        }

        create(Url::class, [], 100);
        create(Url::class, ['user_id' => null], 10);
    }
}

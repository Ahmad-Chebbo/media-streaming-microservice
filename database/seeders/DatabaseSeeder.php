<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Episode;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(LaratrustSeeder::class);

        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@app.com',
            'password' => 'password',
        ]);

        $user = User::create([
            'name' => 'user',
            'email' => 'user@app.com',
            'password' => 'password',
        ]);

        $admin->addRole('admin');
        $user->addRole('user');


        Episode::create([
            'mp3_url' => 'https://archive.org/download/work_2307.poem_librivox/work_anderson_alp.mp3',
            'name' => 'episode 1',
            'author'=> 'Anonymous 1',
        ]);

        Episode::create([
            'mp3_url' => 'https://archive.org/download/work_2307.poem_librivox/work_anderson_alp.mp3',
            'name' => 'episode 2',
            'author'=> 'Anonymous 2',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Frederick Marcoux',
            'email' => 'fmarcoux@conceptsweb.ca',
            'email_verified_at' => now(),
            'password' => \Hash::make('demo1234'),
        ]);
    }
}

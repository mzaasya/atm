<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'User Test 1',
                'pin_number' => 123456,
                'balance' => 1250000,
                'card_number' => 1122334455667788,
            ],
            [
                'name' => 'User Test 2',
                'pin_number' => 123456,
                'balance' => 3000000,
                'card_number' => 8877665544332211,
            ]
        ]);

        DB::table('machines')->insert([
            [
                'code' => 'M001',
                'location' => 'Karawang',
                'balance' => 5000000,
            ],
            [
                'code' => 'M002',
                'location' => 'Bekasi',
                'balance' => 7200000,
            ]
        ]);
    }
}

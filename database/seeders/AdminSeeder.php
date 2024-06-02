<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'first_name'    => 'Nand',
            'last_name' => 'Lal',
            'email' => 'nand_raisal@hotmail.com',
            'avatar'    => '',
            'gender'    => 'M',
            'dob'   => fake()->date(),
            'phone' => '+923003779411',
            'two_factor'    => null,
            'notification'  => null,
            'password'  => 'secret',
            'remember_token' => Str::random(10),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
    }
}

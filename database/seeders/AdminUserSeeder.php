<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@gmail.com');
        $pass  = env('ADMIN_PASSWORD', '123');

        User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Administrator', 'email' => $email, 'password' => Hash::make($pass)]
        );
    }
}

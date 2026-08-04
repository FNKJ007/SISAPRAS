<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Akun ADMIN =====
        User::updateOrCreate(
            ['nip' => '000000000000000001'],
            [
                'name'     => 'Administrator',
                'email'    => 'admin@damkar.local',
                'nip'      => '000000000000000001',
                'role'     => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // ===== Akun USER (Petugas) =====
        User::updateOrCreate(
            ['nip' => '199001012020011001'],
            [
                'name'     => 'User Petugas',
                'email'    => 'petugas@damkar.local',
                'nip'      => '199001012020011001',
                'role'     => 'user',
                'password' => Hash::make('user123'),
            ]
        );
    }
}

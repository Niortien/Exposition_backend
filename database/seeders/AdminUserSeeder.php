<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('AdminUserSeeder ignoré : réservé aux environnements local/testing. Créez le premier admin via un artisan command dédié en production.');

            return;
        }

        $email = env('ADMIN_INITIAL_EMAIL', 'admin@ticketrama.dev');
        $password = env('ADMIN_INITIAL_PASSWORD') ?: throw new \RuntimeException(
            'ADMIN_INITIAL_PASSWORD doit être défini dans .env pour exécuter AdminUserSeeder.'
        );

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin Ticketrama',
                'phone' => env('ADMIN_INITIAL_PHONE', '0700000000'),
                'phone_verified_at' => now(),
                'password' => Hash::make($password),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}

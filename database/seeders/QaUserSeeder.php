<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Test users for the E2E run (credentials from .env.e2e):
 * a verified admin, a verified non-admin and an unverified admin.
 * Refuses to run outside the e2e and testing environments.
 */
class QaUserSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('e2e', 'testing')) {
            throw new RuntimeException('QaUserSeeder only runs in the e2e and testing environments.');
        }

        $users = [
            'QA_ADMIN' => ['name' => 'Admin', 'role' => 'admin', 'verified' => true],
            'QA_USER' => ['name' => 'Benutzer', 'role' => 'editor', 'verified' => true],
            'QA_UNVERIFIED' => ['name' => 'Unbestätigt', 'role' => 'admin', 'verified' => false],
        ];

        foreach ($users as $prefix => $user) {
            $email = env("{$prefix}_EMAIL");
            $password = env("{$prefix}_PASSWORD");
            if (!$email || !$password) {
                throw new RuntimeException("{$prefix}_EMAIL and {$prefix}_PASSWORD must be set.");
            }

            // firstname and name aren't fillable
            User::firstOrNew(['email' => $email])->forceFill([
                'firstname' => 'QA',
                'name' => $user['name'],
                'password' => Hash::make($password),
                'role' => $user['role'],
                'email_verified_at' => $user['verified'] ? now() : null,
            ])->save();
        }
    }
}

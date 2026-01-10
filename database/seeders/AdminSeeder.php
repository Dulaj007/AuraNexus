<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Permission;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'password123');

        // 1️⃣ Create admin user
        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );

        // 2️⃣ Assign admin role
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        if ($adminRoleId) {
            DB::table('role_user')->updateOrInsert(
                [
                    'user_id' => $admin->id,
                    'role_id' => $adminRoleId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 3️⃣ OPTIONAL: give ALL permissions directly to admin (user override)
        $permissionIds = Permission::pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_user')->updateOrInsert(
                [
                    'user_id' => $admin->id,
                    'permission_id' => $permissionId,
                ],
                [
                    'effect' => 'allow',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

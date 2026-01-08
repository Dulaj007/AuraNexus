<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        // Create the admin user
        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make($password),
            ]
        );

        // Assign role as 'admin'
        $role = DB::table('roles')->where('name', 'admin')->first();

        if ($role) {
            DB::table('role_user')->updateOrInsert([
                'user_id' => $admin->id,
                'role_id' => $role->id,
            ]);
        }

        // Optionally give all permissions
        $allPermissions = DB::table('permissions')->pluck('id');

        foreach ($allPermissions as $permId) {
            DB::table('permission_role')->updateOrInsert([
                'role_id' => $role->id,
                'permission_id' => $permId,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tuDept   = Department::where('code', 'TU')->first();
        $kurDept  = Department::where('code', 'KUR')->first();
        $umumDept = Department::where('code', 'UMUM')->first();

        $adminPassword = env('ARSIPARI_ADMIN_PASSWORD', 'password');

        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@arsipari.local'],
            [
                'name'          => 'Administrator',
                'password'      => Hash::make($adminPassword),
                'role'          => UserRole::ADMIN,
                'department_id' => $tuDept?->id,
                'is_active'     => true,
            ]
        );

        // Operator User
        User::firstOrCreate(
            ['email' => 'operator@arsipari.local'],
            [
                'name'          => 'Operator Arsip',
                'password'      => Hash::make('password'),
                'role'          => UserRole::OPERATOR,
                'department_id' => $kurDept?->id,
                'is_active'     => true,
            ]
        );

        // Viewer User
        User::firstOrCreate(
            ['email' => 'viewer@arsipari.local'],
            [
                'name'          => 'Viewer Arsip',
                'password'      => Hash::make('password'),
                'role'          => UserRole::VIEWER,
                'department_id' => $umumDept?->id,
                'is_active'     => true,
            ]
        );
    }
}

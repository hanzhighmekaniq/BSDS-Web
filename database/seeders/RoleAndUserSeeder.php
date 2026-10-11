<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $researcherRole = Role::firstOrCreate(['name' => 'researcher', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@bsds.test'],
            [
                'name' => 'Administrator BSDS',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles([$adminRole]);

        $researcher = User::firstOrCreate(
            ['email' => 'researcher@bsds.test'],
            [
                'name' => 'Peneliti Riset BSDS',
                'password' => Hash::make('password'),
            ]
        );
        $researcher->syncRoles([$researcherRole]);

        $student = User::firstOrCreate(
            ['email' => 'student@bsds.test'],
            [
                'name' => 'Siswa Pelatihan CS',
                'password' => Hash::make('password'),
            ]
        );
        $student->syncRoles([$studentRole]);
    }
}

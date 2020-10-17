<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserIsAdmin;
use App\Enums\UserStatus;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
                'name'          => 'Admin User',
                'email'         => 'admin@aspire.test',
                'password'      => bcrypt('password'),
                'is_admin'      => UserIsAdmin::USER_IS_ADMIN,
                'status'        => UserStatus::APPROVED,
            ]);
        User::create([
                'name'          => 'Test User 1',
                'email'         => 'testuser1@aspire.test',
                'password'      => bcrypt('testuser1'),
                'is_admin'      => UserIsAdmin::USER_IS_NOT_ADMIN,
                'status'        => UserStatus::PENDING,
            ]);
        User::create([
            'name'          => 'Test User 2',
            'email'         => 'testuser2@aspire.test',
            'password'      => bcrypt('testuser2'),
            'is_admin'      => UserIsAdmin::USER_IS_NOT_ADMIN,
            'status'        => UserStatus::PENDING,
        ]);
        User::create([
            'name'          => 'Rejcted User',
            'email'         => 'rejected@aspire.test',
            'password'      => bcrypt('rejected'),
            'is_admin'      => UserIsAdmin::USER_IS_NOT_ADMIN,
            'status'        => UserStatus::REJECTED,
        ]);
    }
}

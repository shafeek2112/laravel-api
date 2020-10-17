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
                'name'          => 'Pending User',
                'email'         => 'pending@aspire.test',
                'password'      => bcrypt('pending'),
                'is_admin'      => UserIsAdmin::USER_IS_NOT_ADMIN,
                'status'        => UserStatus::PENDING,
            ]);
        User::create([
            'name'          => 'Approved User',
            'email'         => 'approved@aspire.test',
            'password'      => bcrypt('approved'),
            'is_admin'      => UserIsAdmin::USER_IS_NOT_ADMIN,
            'status'        => UserStatus::APPROVED,
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

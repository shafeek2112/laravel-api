<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserIsAdmin;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name'          => 'Admin User',
            'email'         => 'admin@aspire.test',
            'password'      => bcrypt('password'),
            'is_admin'      => UserIsAdmin::USER_IS_ADMIN,
        ]);
    }
}

<?php

namespace Modules\DevelUserRoles\Database\Seeders;

use Devel\Database\Seeders\Seeder;
use Illuminate\Database\Eloquent\Model;

class DevelUserRolesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call(UserRolesSeeder::class);
    }

    /**
     * Revert the database seeds.
     *
     * @return void
     */
    public function revert()
    {
        // $this->uncall(UserRolesSeeder::class);
    }
}

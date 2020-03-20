<?php

use Illuminate\Database\Seeder;

use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' 		=> 'Shellrean Co',
            'username'  => 'shellrean',
            'email' 	=> 'shellrean@shellrean.xyz',
            'password' 	=> bcrypt('wandinak21')
        ]);

        User::create([
            'name' 		=> 'Administrator',
            'username'  => 'admin',
            'email' 	=> 'admin@shellrean.xyz',
            'password' 	=> bcrypt('secret')
        ]);
    }
}

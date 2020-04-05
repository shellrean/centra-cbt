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
        // User::create([
        //     'name' 		=> 'Shellrean Co',
        //     'username'  => 'shellrean',
        //     'email' 	=> 'shellrean@shellrean.xyz',
        //     'password' 	=> bcrypt('wandinak21')
        // ]);

        // User::create([
        //     'name' 		=> 'Administrator',
        //     'username'  => 'admin',
        //     'email' 	=> 'admin@shellrean.xyz',
        //     'password' 	=> bcrypt('secret')
        // ]);
        User::create([
            'name'      => 'Drs. H. Ilham',
            'username'  => 'G001A',
            'email'     => 'g001A@shellrean.com',
            'password'  => bcrypt('G001A$$koreksi'),
        ]);

        User::create([
            'name'      => 'Awanita Flora S',
            'username'  => 'G001A2',
            'email'     => 'g001A2@shellrean.com',
            'password'  => bcrypt('G001A2$$koreksi'),
        ]);

        User::create([
            'name'      => 'Siti Alfiyah, M. Pd.',
            'username'  => 'G002A',
            'email'     => 'g002A@shellrean.com',
            'password'  => bcrypt('G002A$$koreksi'),
        ]);

        User::create([
            'name'      => 'Junaedi, M. Pd.',
            'username'  => 'G003A',
            'email'     => 'g003A@shellrean.com',
            'password'  => bcrypt('G003A$$koreksi'),
        ]);

        User::create([
            'name'      => 'Suyati, M. Pd.',
            'username'  => 'G004A',
            'email'     => 'g004A@shellrean.com',
            'password'  => bcrypt('G004A$$koreksi'),
        ]);

        User::create([
            'name'      => 'Drs. Suyatno',
            'username'  => 'G005A',
            'email'     => 'g005A@shellrean.com',
            'password'  => bcrypt('G005A$$koreksi'),
        ]);

        User::create([
            'name'      => 'Sartika Yuniarti, ST. Par',
            'username'  => 'G006A',
            'email'     => 'g006A@shellrean.com',
            'password'  => bcrypt('G006A$$koreksi'),
        ]);

        User::create([
            'name'      => 'Surur, S. Pd.',
            'username'  => 'G007A',
            'email'     => 'g007A@shellrean.com',
            'password'  => bcrypt('G007A$$koreksi'),
        ]);

        User::create([
            'name'      => 'Nuraeni, M. Pd',
            'username'  => 'G001B',
            'email'     => 'g001B@shellrean.com',
            'password'  => bcrypt('G001B$$koreksi'),
        ]);

        User::create([
            'name'      => 'Fitriah, M. Pd.',
            'username'  => 'G003B',
            'email'     => 'g003B@shellrean.com',
            'password'  => bcrypt('G003B$$koreksi'),
        ]);

        User::create([
            'name'      => 'Erni, Bahariawati, S. Pd.',
            'username'  => 'G005B',
            'email'     => 'g005B@shellrean.com',
            'password'  => bcrypt('G005B$$koreksi'),
        ]);

        User::create([
            'name'      => 'Nunuk S. S., M. Pd.',
            'username'  => 'G001C',
            'email'     => 'g001C@shellrean.com',
            'password'  => bcrypt('G001C$$koreksi'),
        ]);

        User::create([
            'name'      => 'Puji Astuti, M. Pd.',
            'username'  => 'G002C',
            'email'     => 'g002C@shellrean.com',
            'password'  => bcrypt('G002C$$koreksi'),
        ]);

        User::create([
            'name'      => 'Prihandoko, S. Pd.',
            'username'  => 'G001D',
            'email'     => 'g001D@shellrean.com',
            'password'  => bcrypt('G001D$$koreksi'),
        ]);

        
        User::create([
            'name'      => 'Surur, S. Pd.',
            'username'  => 'G002D',
            'email'     => 'g002D@shellrean.com',
            'password'  => bcrypt('G002D$$koreksi'),
        ]);

        
        User::create([
            'name'      => 'Hj. Indah Susi A., S. Pd., M.M',
            'username'  => 'G003D',
            'email'     => 'g003D@shellrean.com',
            'password'  => bcrypt('G003D$$koreksi'),
        ]);

        User::create([
            'name'      => 'Hendro',
            'username'  => 'G001E',
            'email'     => 'g001E@shellrean.com',
            'password'  => bcrypt('G001E$$koreksi'),
        ]);
        User::create([
            'name'      => 'Dewi',
            'username'  => 'G002E',
            'email'     => 'g002E@shellrean.com',
            'password'  => bcrypt('G002E$$koreksi'),
        ]);
    }
}

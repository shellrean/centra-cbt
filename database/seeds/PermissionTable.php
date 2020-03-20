<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\RoleHasPermission;

use App\User;
use Illuminate\Support\Facades\DB;

class PermissionTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
        	'server','create_server','active_server','reset_server','delete_server',
        	'peserta','create_peserta','delete_peserta','upload_peserta',
        	'matpel','create_matpel','edit_matpel','delete_matpel',
        	'banksoal','create_banksoal','edit_banksoal','delete_banksoal',
        	'soal','create_soal','edit_soal','delete_soal',
        	'jadwal','create_jadwal','active_jadwal',
        	'hasil_ujian',
        	'skoring',
        	'filemedia',
        	'setting'
        ];

        $roles = [
        	'superadmin','admin','teacher','school'
        ];

        foreach ($roles as $role) {
        	Role::create([
        		'name'	=> $role
        	]);
        }

    	$role = Role::find(1);

        foreach ($permissions as $permission) {
            $permission = Permission::create([
            	'name' => $permission
            ]);
            DB::table('role_has_permissions')->insert([
            	'permission_id'	=> $permission->id,
            	'role_id'		=> 1
            ]);
        }

        $user = User::find(1);
        $user->assignRole('superadmin');
    }
}

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * @version 1
 * api response for v1
 */

Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1'], function() {

	Route::post('/login', 'AuthController@login');

	Route::group(['middleware' => 'auth:api'], function() {
		Route::get('/logout', 'AuthController@logout');

		Route::get('roles', 'RolePermissionController@getAllRole');
		Route::get('permissions', 'RolePermissionController@getallPermission');
		Route::post('role-permission', 'RolePermissionController@getRolePermission');
		Route::post('set-role-permission', 'RolePermissionController@setRolePermission');
		Route::post('set-role-user', 'RolePermissionController@setRoleUser');

		Route::get('user-authenticated', 'UserController@getUserLogin');
		Route::get('user-lists', 'UserController@userLists');

		Route::get('matpel/list', 'MatpelController@getAll');
		Route::apiResource('matpel', 'MatpelController');

		Route::apiResource('peserta', 'PesertaController')->except('show','update');
		Route::post('peserta/upload', 'PesertaController@import');

		Route::get('banksoal/get-all', 'BanksoalController@getAll');
		Route::get('banksoal/active', 'BanksoalController@active');
		Route::apiResource('banksoal', 'BanksoalController');

		Route::resource('/soal', 'SoalController')->only('show');
		Route::get('soal/banksoal/{id}', 'SoalController@getSoalByBanksoal');
		Route::get('all-soal/banksoal/{id}','SoalController@getSoalByBanksoalAll');
		Route::get('all-soal/banksoal/analys/{id}', 'SoalController@getSoalByBanksoalAnalys');
		Route::post('soal/banksoal', 'SoalController@storeSoalBanksoal');
		Route::post('soal/banksoal/edit', 'SoalController@updateSoalBanksoal');
		Route::delete('soal/banksoal/{id}', 'SoalController@destroySoalBanksoal');

		Route::apiResource('ujian', 'UjianController');
		Route::get('ujian/list', 'UjianController@getAll');
		Route::post('ujian/set-status', 'UjianController@setStatus');
		Route::post('ujian/change-token', 'UjianController@changeToken');
		Route::get('ujian/get-peserta/{id}', 'UjianController@getPeserta');
		Route::get('ujian/hasil/{id}', 'UjianController@getHasil');
		Route::post('hasil/filter', 'UjianController@getByFilter');
		Route::get('ujian/esay/koreksi/{id}', 'UjianController@getEsay');
		Route::post('ujian/esay/input', 'UjianController@inputEsay');
		Route::get('ujian/banksoal/{id}', 'UjianController@getByBanksoal');
		Route::get('ujian/esay/exists', 'UjianController@getExistsEsay');
		Route::get('ujian/result/sekolah/jadwal/{id}', 'UjianController@getSekolahByJadwal');
		Route::post('ujian/result/sekolah/hasil', 'UjianController@getHasilByJadwalAndSekolah');
		Route::post('ujian/result/sekolah/banksoal', 'UjianController@getBanksoalByJadwalAndSekolah');
		Route::post('ujian/resul/capaian-siswa', 'UjianController@getCapaianSiswa');
		Route::post('ujian/esay/rujukan', 'UjianController@setRujukan');

		Route::apiResource('directory', 'DirectoryController');
		Route::post('directory/filemedia', 'DirectoryController@storeFilemedia');
		Route::delete('directory/filemedia/{id}', 'DirectoryController@deleteFilemedia');
		Route::post('upload/file-audio', 'DirectoryController@uploadAudio');
		Route::get('directory/banksoal/{id}', 'DirectoryController@getDirectoryBanksoal');

		Route::get('server/list', 'ServerController@getAll');
		Route::apiResource('server','ServerController');
		Route::post('server/changed/{id}', 'ServerController@changeStatus');
		Route::post('server/reset-serial/{id}', 'ServerController@resetSerial');

		Route::apiResource('sekolah','SekolahController');
		Route::get('all-sekolah', 'SekolahController@allSekolah');
		Route::get('jurusan', 'SekolahController@allJurusan');
		Route::get('agama', 'SekolahController@allAgama');

		/**
		 * Heigher actions
		 * @since 1.0.1
		 * @author <wandinak17@gmail.com>
		 */
		Route::get('heager/generate/hasil-ujian', 'HigherController@generateHasilUjian');
		Route::get('heager/generate/anayls', 'HigherController@generateAnalys');
		Route::get('heager/arsip/jawaban', 'HigherController@arsipJawaban');
		
	});
});


/**
 * @version 1
 * api response for v1
 * Response to local server
 */
Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1'], function() {

	Route::post('pusat/test-sync', 'PusatController@testSync');
	Route::post('pusat/register-server', 'PusatController@registerServer');
	Route::post('pusat/connect', 'PusatController@connect');
	Route::post('pusat/upload-hasil', 'PusatController@uploadHasil');
	Route::post('pusat/cbt-sync', 'PusatController@cbtSync');
});
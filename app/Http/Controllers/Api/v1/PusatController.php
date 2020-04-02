<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

use App\Matpel;
use App\Banksoal;
use App\Soal;
use App\JawabanSoal;
use App\Jadwal;
use App\Peserta;
use App\Server;
use App\Directory;
use App\File;
use App\Result;

use DB;

class PusatController extends Controller
{
	/**
	 * Register server locak
	 * @param \Illuminate\Http\Request
	 */
	public function registerServer(Request $request) 
    {
        $server = Server::where([
        	'server_name' => $request->server_name,
        	'password'	 => $request->password
        ])
        ->select('id','server_name','password')
        ->first();

        if($server) {
            $serverc = Server::find($server->id);
            if($serverc->serial_number != '-') {
                return response()->json(['status' => 'error']);
            }
    
            $serverc->serial_number = $request->serial_number;
            $serverc->save();
    
            return response()->json(['status' => 'success', 'data' => $serverc, 'password' => $server->password]);
        }
        
        return response()->json(['status' => 'notfound']);
    }   

    /**
     * Check connection 
     * @param \Illuminate\Http\Request
     */
    public function connect(Request $request) 
    {
        $server = Server::where([
            'server_name'   => $request->server_name
        ])->first();

        if($server) {
            if($server->serial_number != $request->serial_number) {
                return response()->json(['data' => 'block']);
            }
            return response()->json(['data' => $server]);
        }
        return response()->json(['data' => 'unregistered']);
    }

    /**
     * Check data that has the request server
     * @param \Illuminate\Http\Request
     */
    public function testSync(Request $request)
    {
    	$server = Server::where('server_name', $request->server_name)->first();
    	if(!$server) {
    		return response()->json(['message' => 'You have idea'],403);
    	}

    	if($server->serial_number != $request->serial_number) {
    		return response()->json(['message' => 'You have changed your device'],403);
    	}

    	$jadwal = Jadwal::where(['status_ujian' => 1, 'server_id' => 0])->get()->pluck('banksoal_id');

    	$jadwal2 = Jadwal::where([
    		['status_ujian','=',1],
    		['server_id','<>','0']
    	])->get()->pluck('banksoal_id');

        $banksoal_dipakai = array_merge($jadwal->toArray(), $jadwal2->toArray());

        $vokasi = Peserta::where([
            'name_server' => $request->server_name
        ])->groupBy('jurusan_id')->pluck('jurusan_id');

        $check_vokasi = $vokasi->toArray();

        $banksoal = 0;
        $useBanksoal = array();
        foreach ($banksoal_dipakai as $jad) {
            foreach($jad as $j) {
                if(is_array($j['jurusan'])) {
                    foreach($j['jurusan'] as $d) {
                        if(in_array($d, $check_vokasi)) {
                            $banksoal++;
                            array_push($useBanksoal, $j['id']);
                            break;
                        }
                    }
                } 
                else {
                    if($j['jurusan'] == 0) {
                        $banksoal++;
                        array_push($useBanksoal, $j['id']);
                    }
                }
            }
        }

        $peserta = Peserta::where([
            'name_server'   => $request->server_name
        ])->count();

        $matpel = DB::table('matpels')
        ->where('jurusan_id',0)
        ->orWhereIn('jurusan_id', $vokasi)
        ->count();
        
        $soal = Soal::whereIn('banksoal_id', $useBanksoal);
        $countSoal = $soal->count();
        $jawaban = JawabanSoal::whereIn('soal_id', $soal->pluck('id'))->count();
        
        $c_dir = Banksoal::whereIn('id', $useBanksoal)->pluck('directory_id');
        $gambar = File::whereIn('directory_id', $c_dir)->count();

        $jadwal = $jadwal->count()+$jadwal2->count();

        $data = [
            'peserta'       => $peserta,
            'matpel'        => $matpel,
            'banksoal'      => $banksoal,
            'soal'          => $countSoal,
            'jawaban_soal'  => $jawaban,
            'gambar'        => $gambar,
            'jadwal'        => $jadwal,
        ];
        return response()->json(['data' => $data]);
    }

    /**
     * CBT Sync
     * @param \Illuminate\Http\Request
     */
    public function cbtSync(Request $request )
    {
        $server = Server::where('server_name', $request->server_name)->first();
        if(!$server) {
            return response()->json(['message' => 'You have idea'],403);
        }

        if($server->status != 1) {
            return response()->json(['message' => 'server offline'],403);
        }

        $jad1 = Jadwal::where(['status_ujian' => 1, 'server_id' => 0])->get()
        ->makeHidden('kode_banksoal')
        ->makeHidden('banksoal_id')
        ->makeHidden('server_id')
        ->makeVisible('ids')
        ->makeHidden('created_at')
        ->makeHidden('updated_at');

        $jadwal = $jad1->pluck('banksoal_id');

        $jad2 = Jadwal::where([
            ['status_ujian','=',1],
            ['server_id','<>','0']
        ])->get()
        ->makeHidden('kode_banksoal')
        ->makeHidden('banksoal_id')
        ->makeHidden('server_id')
        ->makeVisible('ids')
        ->makeHidden('created_at')
        ->makeHidden('updated_at');

        $jadwal2 = $jad2->pluck('banksoal_id');

        $banksoal_dipakai = array_merge($jadwal->toArray(), $jadwal2->toArray());

        $vokasi = Peserta::where([
            'name_server' => $request->server_name
        ])->groupBy('jurusan_id')->pluck('jurusan_id');

        $check_vokasi = $vokasi->toArray();

        $banksoal = 0;
        $useBanksoal = array();
        foreach ($banksoal_dipakai as $jad) {
            foreach($jad as $j) {
                if(is_array($j['jurusan'])) {
                    foreach($j['jurusan'] as $d) {
                        if(in_array($d, $check_vokasi)) {
                            $banksoal++;
                            array_push($useBanksoal, $j['id']);
                            break;
                        }
                    }
                } 
                else {
                    if($j['jurusan'] == 0) {
                        $banksoal++;
                        array_push($useBanksoal, $j['id']);
                    }
                }
            }
        }

        $banksoaler = Banksoal::whereIn('id',$useBanksoal)->get()
        ->makeHidden('inputed')
        ->makeHidden('created_at')
        ->makeHidden('updated_at');

        $soal = Soal::whereIn('banksoal_id', $useBanksoal);

        switch ($request->req) {
            case 'peserta':
                $server = Server::where('server_name', $request->server_name)->first();
                $server->sinkron = 1;
                $server->save();
                
                $peserta = Peserta::where([
                    'name_server'   => $request->server_name
                ])->get()
                ->makeHidden('created_at')
                ->makeHidden('updated_at');
                
                $data = [
                    'table' => 'pesertas',
                    'data'  => $peserta
                ];
                break;
            case 'matpel':
                $matpels = DB::table('matpels')
                ->where('jurusan_id',0)
                ->orWhereIn('jurusan_id', $vokasi)
                ->select('id','kode_mapel','agama_id','nama','jurusan_id')
                ->get();
                
                $data = [
                    'table'  => 'matpels',
                    'data'   => $matpels   
                ];
                break;
            case 'banksoal':
                $data = [
                    'table'  => 'banksoals',
                    'data'   => $banksoaler
                ];
                break;
            case 'soal':
                $soals = $soal->get()
                ->makeHidden('rujukan')
                ->makeHidden('created_at')
                ->makeHidden('updated_at');
                
                $data = [
                    'table'  => 'soals',
                    'data'   => $soals
                ];
                break;
            case 'jawaban_soal':
                $jawaban_soal = JawabanSoal::whereIn('soal_id', $soal->pluck('id'))->get()
                ->makeHidden('created_at')
                ->makeHidden('updated_at');
                
                $data = [
                    'table'  => 'jawaban_soals',
                    'data'   => $jawaban_soal   
                ];
                break;
            case 'jadwal':
                $jadwal = array_merge($jad1->toArray(), $jad2->toArray());
                $data = [
                    'table'  => 'jadwals',
                    'data'   => $jadwal   
                ];
                break;
            case 'file': 
                $c_dir = Banksoal::whereIn('id', $useBanksoal)->pluck('directory_id');
                $files = File::whereIn('directory_id', $c_dir)->get()
                ->makeHidden('created_at')
                ->makeHidden('updated_at');
                
                $data = [
                    'files'  => $files,
                ];
                break;
            default:
        }

        return response()->json($data);
    }

    public function uploadHasil(Request $request)
    {
        $server = Server::where('server_name', $request->server_name)->first();
        if(!$server) {
            return response()->json(['error'],403);
        }
        $esay = json_decode($request->esay, true);
        $data = json_decode($request->datad,true);

        if($data != '') {
            DB::beginTransaction();
            try {
                
                foreach($data as $d) {
               
                    DB::table('jawaban_pesertas')->insert([
                        'banksoal_id'   => $d['banksoal_id'],
                        'soal_id'       => $d['soal_id'],
                        'peserta_id'    => $d['peserta_id'],
                        'jadwal_id'     => $d['jadwal_id'],
                        'jawab'         => $d['jawab'],
                        'esay'          => $d['esay'],
                        'ragu_ragu'     => $d['ragu_ragu'],
                        'iscorrect'     => $d['iscorrect']
                    ]);
                    
                }
                   
                DB::commit();
            } catch (QueryException $e) {
                DB::rollback();
                return response()->json(['message' => 'Server error'],500);
            }
        }

        return response()->json(['data' => 'OK']);
    }
}

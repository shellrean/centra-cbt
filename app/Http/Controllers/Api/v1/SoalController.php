<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\JawabanSoal;
use App\Soal;
use DB;

use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class SoalController extends Controller
{
    protected $permissions = [];

    /**
     * Construction
     */
    public function __construct( )
    {
        $user = request()->user('api'); 
        $permissions = [];
        foreach (Permission::all() as $permission) {
            if (request()->user('api')->can($permission->name)) {
                $permissions[] = $permission->name;
            }
        }

        $this->permissions = $permissions;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $soal = Soal::with('jawabans')->where(['id' => $id])->first();
        return response()->json(['data' => $soal]);
    }

    /**
     * Get soal by banksoal
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getSoalByBanksoal($id)
    {
        $this->checkPermissions('soal');

        $soal = Soal::with('jawabans')->where('banksoal_id',$id);
        if (request()->q != '') {
            $soal = $soal->where('kode_banksoal', 'LIKE', '%'. request()->q.'%');
        }

        $soal = $soal->paginate(10);

        return [ 'data' => $soal ];
    }

    /**
     * Get soal by banksoal
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getSoalByBanksoalAll($id)
    {
        $this->checkPermissions('soal');

        $soal = Soal::with('jawabans')->where('banksoal_id',$id)->get();

        return [ 'data' => $soal ];
    }

    /**
     * Store soal
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function storeSoalBanksoal(Request $request)
    {
        $this->checkPermissions('create_soal');

        $soal = Soal::create([
            'banksoal_id'   => $request->banksoal_id,
            'pertanyaan'    => $request->pertanyaan,
            'tipe_soal'     => $request->tipe_soal,
            'rujukan'       => $request->rujukan,
            'audio'         => $request->audio,
            'direction'     => $request->direction
        ]);

        if($request->tipe_soal != 2) {
            foreach($request->pilihan as $key=>$pilihan) {
                JawabanSoal::create([
                    'soal_id'       => $soal->id,
                    'text_jawaban'  => $pilihan,
                    'correct'       => ($request->correct == $key ? '1' : '0')
                ]);
            }
        } 

        return response()->json(['data' => 'success']);
    }

    public function updateSoalBanksoal(Request $request) 
    {
        $this->checkPermissions('edit_soal');

        $soal = Soal::find($request->soal_id);
        $soal->pertanyaan = $request->pertanyaan;
        $soal->audio = $request->audio;
        $soal->direction = $request->direction;
        $soal->tipe_soal = $request->tipe_soal;
        $soal->rujukan = $request->rujukan;
        $soal->save();

        if($request->tipe_soal != 2 ) {
            DB::table('jawaban_soals')->where('soal_id',$request->soal_id)->delete();
            foreach($request->pilihan as $key=>$pilihan) {
                JawabanSoal::create([
                    'soal_id'       => $soal->id,
                    'text_jawaban'  => $pilihan,
                    'correct'       => ($request->correct == $key ? '1' : '0')
                ]);
            }
        }
        return response()->json(['data' => 'updated']);
    }

    /**
     * Destroy soal
     *
     */
    public function destroySoalBanksoal($id)
    {
        $this->checkPermissions('delete_soal');

        $soal = Soal::find($id);
        JawabanSoal::where('soal_id', $soal->id)->delete();
        $soal->delete();

        return response()->json(['data' => 'success']);
    }

    /**
     * Response denied
     *
     *
     **/
    public function checkPermissions($permission)
    {
        if(in_array($permission, $this->permissions)) {
            return true;
        }

        return response()->json(['error' => 'forbidden'],403);
    }
}

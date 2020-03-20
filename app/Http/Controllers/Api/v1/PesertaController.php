<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PesertaImport;

use PDF;
use App\Sekolah;
use App\Peserta;
use App\Server;

class PesertaController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->checkPermissions('peserta');

        $peserta = Peserta::orderBy('no_ujian');
        if (request()->q != '') {
            $peserta = $peserta->where('nama', 'LIKE', '%'.request()->q.'%');
        }

        if (request()->s != '') {
            $server = Server::where('sekolah_id',request()->s)->pluck('server_name');
            $peserta = $peserta->whereIn('name_server', $server);
        }

        $peserta = $peserta->paginate(10);

        return [ 'data' => $peserta ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->checkPermissions('create_peserta');

        $validator = Validator::make($request->all(), [
            'server_name'   => 'required',
            'no_ujian'      => 'required|unique:pesertas,no_ujian',
            'nama'          => 'required',
            'password'      => 'required',
            'sesi'          => 'required',
            'jurusan_id'    => 'required'
        ]);

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'name_server'   => $request->server_name,
            'no_ujian'      => $request->no_ujian,
            'nama'          => $request->nama,
            'password'      => $request->password,
            'sesi'          => $request->sesi,
            'jurusan_id'    => $request->jurusan_id
        ];

        $data = Peserta::create($data);

        return response()->json(['data' => $data]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->checkPermissions('delete_peserta');

        $peserta = Peserta::find($id);
        $peserta->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Upload peserta by excel
     *
     * @param \Illuminate\Http\Request  $request
     */
    public function import(Request $request)
    {
        Excel::import(new PesertaImport,$request->file('file'));

        return response()->json(['status' => 'success']);
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

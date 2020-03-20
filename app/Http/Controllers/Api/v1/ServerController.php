<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Server;
use PDF;


class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $server = Server::orderBy('created_at');
        if (request()->q != '') {
            $server = $server->where('name_server', 'LIKE', '%'.request()->q.'%');
        }

        if (request()->s != '') {
            $server = $server->where('sekolah_id', request()->s);
        }

        $server = $server->with(['password'])->paginate(10);
        return new AppCollection($server);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'server_name'           => 'required|unique:servers,server_name',
            'description'            => 'required',
            'sekolah_id'            => 'required'
        ]);

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'server_name'           => $request->server_name,
            'description'           => $request->description,
            'sekolah_id'            => $request->sekolah_id,
            'serial_number'         => '-',
            'sinkron'               => '0',
            'status'                => '0'
        ];

        $data = Server::create($data);

        return response()->json(['data' => $data]);
    }

    /**
     * Destroy all data
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $server = Server::find($id);
        $server->delete();

        return response()->json([]);
    }

    public function changeStatus($id)
    {
        $server = Server::find($id);
        $server->status = ($server->status == 0 ? '1' : '0');
        $server->save();

        return response()->json([]);
    }

    public function resetSerial($id)
    {
        $server = Server::find($id);
        $server->serial_number = '-';
        $server->save();

        return response()->json([]);
    }

    public function preDataServer()
    {
        $servers = Server::with('password')->orderBy('sekolah_id')->get();
        $pdf = PDF::loadview('prev.server',compact('servers'));
        return $pdf->stream('data-server.pdf');
    }
}

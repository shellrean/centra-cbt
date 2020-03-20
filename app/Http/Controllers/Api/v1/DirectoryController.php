<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Directory;
use App\File;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DirectoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $directories = Directory::withCount(['file'])->latest()->get();

        return response()->json(['data' => $directories]);
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
            'nama_directory'    => 'required|unique:directories,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        Directory::create([
            'name'      => $request->nama_directory,
            'slug'      => Str::slug($request->nama_directory, '-')
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contentDirectory = File::where(['directory_id' => $id]);
        $contentDirectory = $contentDirectory->paginate(50);
        return [ 'data' => $contentDirectory ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadAudio(Request $request)
    {
        $file = $request->file('file');
        $filename = date('Ymd').'-'.$file->getClientOriginalName();
        $path = $file->storeAs('public/audio/',$filename);

        return response()->json(['data' => $filename]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Insert filemedia.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeFilemedia(Request $request)
    {
        $dir = Directory::find($request->directory_id);
        $file = $request->file('image');
        $type = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $filename = date('Ymd').'-'.$file->getClientOriginalName();
        $path = $file->storeAs('public/'.$dir->slug,$filename);

        $data= [
            'directory_id'      => $request->directory_id,
            'filename'          => $filename,
            'path'              => $path,
            'exstension'        => $type,
            'dirname'           => $dir->slug,
            'size'              => $size,
        ];

        $logo = File::create($data);

        return response()->json(['data' => $logo]);
    }

    public function getDirectoryBanksoal($id)
    {
        $contentDirectory = File::where(['directory_id' => $id]);
        $contentDirectory = $contentDirectory->paginate(10);
        return [ 'data' => $contentDirectory ];
    }
}

<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TesterController extends Controller
{
    public function upload(Request $request)
    {
        // $dir = Directory::find($request->directory_id);
        $file = $request->file('image');
        $type = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $filename = date('Ymd').'-'.$file->getClientOriginalName();
        $path = $file->storeAs('public',$filename);

        // $data= [
        //     'directory_id'      => $request->directory_id,
        //     'filename'          => $filename,
        //     'path'              => $path,
        //     'exstension'        => $type,
        //     'dirname'           => $dir->slug,
        //     'size'              => $size,
        // ];

        // $logo = File::create($data);

        return response()->json(['src' => 'http://localhost/storage/'.$filename]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


require_once(__DIR__ . '../../../../resources/php/config.php');
class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        if ($request->hasFile('image')) 
        {
            $files = $request->file('image');

            foreach($files as $file)
            {

            // $file = $request->file('image');
            // $fileName = $file->getClientOriginalExtension();
        // $file = $request->file('image');
        // maintain original name and contents
        $fileName = $file->getClientOriginalName();
        $fileContents = file_get_contents($file);
        //upload
        // check if file exists
        if (Storage::disk('media')->exists('images/space/'.$fileName)) {
            // rename file
            return response()->json(['success' =>false, 'msg' => 'File Exists!'], 200);

        }

        $extension = $file->getClientOriginalExtension();
        if ($extension != 'jpg' && $extension != 'png' && $extension != 'jpeg') {
            return response()->json(['success' =>false, 'msg' => 'File extension not allowed'], 200);
        }

        // use storage disk media
         Storage::disk('media')->put('images/space/' .$fileName, $fileContents);
    }
        return response()->json(['success' => true, 'msg' => 'Image uploaded successfully']);
        }

        else {
            return response()->json(['success' =>false, 'msg' => 'No file selected'], 200);
        }
        // $file->move('uploads', $file->getClientOriginalName());
    }

    public function content(Request $request)
    {
        // multiple file upload
        if ($request->hasFile('image')) 
        {
            
            // multiple 
            $files = $request->file('image');
            $path = $request->itemPath;
$count = 0;
            foreach($files as $file)
            {

                // $fileName = $file->getClientOriginalExtension();
                // $file = $request->file('image');
                // maintain original name and contents
                $fileName = $file->getClientOriginalName();
                $fileContents = file_get_contents($file);
                //upload
                // check if file exists
                if (Storage::disk('content')->exists(CONTENT_ASSETS . $path . "/".$fileName) && $count > 1) {
                    // rename file
                    return response()->json(['success' =>false, 'msg' => 'File Exists!'], 200);

                }

                $extension = $file->getClientOriginalExtension();
                if ($extension != 'as' && $extension != 'xml' && $extension != 'png') {
                    return response()->json(['success' =>false, 'msg' => 'File extension not allowed'], 200);
                }

                // use storage disk media
                Storage::disk('content')->put(CONTENT_ASSETS . $path . "/" .$fileName, $fileContents);
                $count++;
            }
            return response()->json(['success' => true, 'msg' => 'Files uploaded successfully']);
        }
        //     $file = $request->file('image');
        //     foreach ($file as $files) {

        //     // $fileName = $file->getClientOriginalExtension();
        // // $file = $request->file('image');
        // // maintain original name and contents
        // $fileName = $files->getClientOriginalName();
        // $fileContents = file_get_contents($files);
        // //upload
        // // check if file exists
        // if (Storage::disk('content')->exists(CONTENT_ASSETS_ITEMS . $path . "/".$fileName)) {
        //     // rename file
        //     return response()->json(['success' =>false, 'msg' => 'File Exists!'], 200);

        // }
        // //check file extension
       
        

        // // use storage disk media
        //  Storage::disk('content')->put(CONTENT_ASSETS_ITEMS . $path . "/" .$fileName, $fileContents);
        // return response()->json(['success' => true, 'msg' => $fileName.' uploaded successfully. <a href="'.DEFAULT_CONTENT_PATH.'/'.CONTENT_ASSETS_ITEMS . $path . '/' .$fileName.'" target="top">View it</a>']);
        //     }
        // }

        else {
            return response()->json(['success' =>false, 'msg' => 'No file selected'], 200);
        }
        // $file->move('uploads', $file->getClientOriginalName());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
}

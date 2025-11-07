<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\result\CompressionUtil;
use Illuminate\Support\Facades\Storage;

class Utils extends Controller
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
    }
    public function decompress(Request $request)
    {
        //
        $data = $request->compressed;
        $str = CompressionUtil::decompress($data);
        return response()->json(['success' => true, 'uncompressed' => $str]);
    }

    public static function decompressStr($data)
    {
        return CompressionUtil::decompress($data);
    }


    public function compress(Request $request)
    {
        //
        $data = $request->uncompressed;
        $str = CompressionUtil::compress($data);
        return response()->json(['success' => true, 'compressed' => $str]);
    }
    
    public static function compressStr($data)
    {
        return CompressionUtil::compress($data);
    }

    public static function generateRandomString($length = 10) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function isAdmin($user)
    {
        
        return $user->primaryGroupId == 1 || $user->primaryGroupId == 2 || $user->primaryGroupId == 13 || in_array(13, $user->secondaryGroupIds);
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

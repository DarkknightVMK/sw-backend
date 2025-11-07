<?php
namespace App\result;


class CompressionUtil 
{

    public static function compress($data)
    {
        $data = gzcompress($data, 9);
        $data = base64_encode($data);
        return $data;
    }

    public static function decompress($data)
    {
        $data = base64_decode($data);
        $data = gzuncompress($data);
        return $data;
    }
//   public static String uncompress(String param1) throws IOException {
//     var _loc2_ = Base64.getDecoder().decode(param1);

//     return new String(decompress(_loc2_));
// }

}
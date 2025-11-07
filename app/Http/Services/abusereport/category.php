<?php 

use App\Http\Classes\ServiceResult;

class category
{
    function getCategoriesForType($timeconfig, $type)
    {
        $amf = new stdClass(); 
        $amf->categories[] = array(
          'id' => 1,
          'desc' => 'Scamming',
          'types' => '1',
          'priority' => '1',
        );
        $amf->success=true;
        return $amf;

      // return new ServiceResult(true);
    }
}
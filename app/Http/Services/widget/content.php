<?

use App\Models\avatarItems;

  class content{
    function getContentForItem($timeconfig, $itemId)
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.ServiceResult";
        // $amf->content[] = array (
        //   'creatorAvatar' => array (
        //     'id' => '1',
        //     'ownerId' => '1',
        //     'userId' => '1',
        //     'modelId' => avatarItems::where('item_id', $itemId)->first()->model_id,

        //   ),
        //   'modelId' => avatarItems::where('item_id', $itemId)->first()->model_id,
        //   'itemId' => $itemId,
        //   'userIsArtist' => true,
        //   'id' => null,
        // );
        $amf->content = null;
        // $amf->modelId = ;
        // $amf->itemId = $itemId;
        // $amf->userIsArtist = true;
        $amf->success=true;
        return $amf;
  
    }

  }
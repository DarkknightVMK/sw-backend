<?
use App\Models\pets;
use App\result\SMIPetDetailsResult;
use App\result\DataResult;
use App\result\ServiceResult;
use App\result\CompressionUtil;
class pet
{

  function searchPets($timeconfig, $aid)
  {
    $amf = new stdClass();
        $amf->recordSet = $this->search($aid);
        $amf->success=true;
        return $amf;
  }

  function search($aid)
  {

      // $avatar = new functions();
      $pet = pets::where('ownerId', $aid )->get();

      $count = count($pet);

      $arr = array();
      for ($i = 0; $i < $count; $i++)
      {
          $arr = $pet->toArray();
          }
      return $arr;
  }

  function getPetDetails($timeconfig, $pID)
  {
    $pet = pets::where('id', $pID )->first();
    if ($pet->exists())
    {
      return new DataResult(new SMIPetDetailsResult($pet));
    }
    return new ServiceResult(false);

  }

  function savePetDetails($timeconfig, $pId, $pName, $pMotto, $active, $pMem, $pConfig)
  {
    if (!strval($pId))
    {
      return new ServiceResult(false);
    }

    if (empty($pId) || empty($pName) || empty($pMotto) || empty($active) )
    {
      return new ServiceResult(false);
    }

    $pet = pets::where('stringId', $pId )->first();
    if ($pet->exists())
    {
      $pet->name = $pName;
      $pet->motto = $pMotto;
      $pet->active = $active;
      if (!empty($pMem))
      {
        $pet->memoryString = CompressionUtil::compress($pMem);
      }
      if (!empty($pConfig))
      {
        $pet->configString = CompressionUtil::compress($pConfig);
      }
      $pet->save();
    }
      return new ServiceResult(true);

  }

}
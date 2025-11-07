<?php
include ('functions.php');
use App\Models\Avatars;
use App\Models\Users;
use App\Models\avatar_outfits;
use App\Models\pets;
use App\result\PetDetailsResult;
use App\result\ServiceResult;
use App\result\StringResult;


class pet
{
  function getActivePet()
  {
    if (pets::where('ownerId', session('avatar'))->exists() )
     return new  PetDetailsResult(pets::where('ownerId', session('avatar'))->first());
    else
     return new ServiceResult(true);
  }

  function getUpdatedPetMemory($timeconfig, $petId)
  {
    return new StringResult(pets::where('stringId', $petId)->first()->memoryString);
  } 

  function setHappiness($timeconfig, $petId, $happinessId)
  {
    return new ServiceResult(true);
  }

  function getCommandPrices()
  {
    $amf = new stdClass();
    $arr[] = array ('petOwnerId',
'id'
);
    // $arr[] = array (session('avatar'));
    $arr[] = array (strval(session('avatar')),
    strval(session('avatar')),
    
);
$pet = pets::where('ownerId', session('avatar'))->get();
$petArr = array();
if (pets::where('ownerId', session('avatar'))->exists())
{

foreach ($pet as $p)
{
    // $petArr = array('avatarMem' => $p->memoryString);
    $petArr[] = array(
      
        // 'prices' => array(
        'command' => 'fandango',
        'price' => 2,
        'filename' => '../../../../../context/pet/commands/icon_fandango.png',

        // ),
    
        // do calculation of created_at vs current time in seconds

        // 'lifespanSeconds' => $p->created_at - time(),
    );
}

}
    $amf->prices= 
    $petArr;

    // $amf->currentOwnerId = strval(session('avatar'));
    $amf->success=true;
    return $amf;
}
}
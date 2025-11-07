<?php
use App\Models\s2wCat;
use App\Models\s2wPrize;
use App\result\DataResult;
use App\result\ServiceResult;
use App\result\StringResult;

class prize
{
  function deletePrize($timeconfig, $prizeId)
  {
    // delete s2w prize
    $prize = s2wPrize::find($prizeId);
    $prize->delete();

    return new ServiceResult;
  }

  function editPrize($timeconfig, $prizeId, $type, $modelId, $count, $gold, $tokens, $isBrag, $isPreview)
  {
    // edit s2w prize
    $prize = s2wPrize::find($prizeId);
    $prize->tokens = $tokens;
    $prize->gold = $gold;
    $prize->modelCount = $count;
    $prize->type = $type;
    $prize->isInPreviewList = $isPreview;
    $prize->isBraggable = $isBrag;
    $prize->modelId = $modelId;
    $prize->save();
    return new ServiceResult;
  }

  function addPrize($timeconfig, $id, $catId, $type, $modelId, $count, $goldAmt, $tokenAmt, $isBrag, $isPreview)
  {
    // create new s2w prize
    $prize = new s2wPrize;
    $prize->tokens = $tokenAmt;
    $prize->gold = $goldAmt;
    $prize->modelCount = $count;
    $prize->type = $type;
    $prize->isInPreviewList = $isPreview;
    $prize->isBraggable = $isBrag;
    $prize->categoryId = $catId;
    $prize->modelId = $modelId;
    $prize->save();
    return new StringResult((String)$id);
  }
}
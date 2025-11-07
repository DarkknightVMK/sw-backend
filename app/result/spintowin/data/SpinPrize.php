<?php
 namespace App\result\spintowin\data;
use App\Models\items;
use Illuminate\Support\Str;

#[\AllowDynamicProperties]
class SpinPrize
{
  public $_explicitType = 'com.smallworlds.widget.spintowin.model.SpinPrize';
  
  public function __construct($data)
  {
    if ($data == null) {
      return $this;
    }
    if (is_array($data)) {
      $data = (object) $data;
    }

    switch($data->type)
    {
      case 2:
        //GOLD
        $this->modelIcon = 'ui/attributes/icon_gold.png';
        break;
      case 3:
        //TOKENS
        $this->modelIcon = 'ui/attributes/icon_tokens.png';
        break;
    }
    
    $item = items::where('model_id', $data->modelId)->exists();
    if ($item){
      $items = items::where('model_id', $data->modelId)->first();
      $this->modelIcon = $items->model_icon;
      if (Str::contains($items->model_tags, 'emote'))
        $emote = true;
      else
        $emote = false;
        // //   $this->modelEmote = true;

    }
    $this->id = (String) $data->id;
    $this->tokens = $data->tokens;
    $this->categoryId = $data->categoryId;
    $this->modelCount = (float)$data->modelCount;
    $this->gold = (float)$data->gold;
    $this->modelId = (String)$data->modelId;
    $this->braggable = boolval($data->isBraggable);
    $this->display = boolval($data->isInPreviewList);
    $this->modelName = ($item) ? $items->model_desc : null;
    $this->modelPriceTokens = ($item) ? (float) $items->model_price_tokens : null;
    $this->modelPriceGold = ($item) ? (float) $items->model_price_gold : null;
    $this->modelEmote = ($item) ? $emote : null;
    return $this;
  }

}
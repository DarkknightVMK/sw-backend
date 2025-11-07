<?php

 namespace App\result\spintowin\data;
  use App\result\spintowin\data\SpinPrize;
  use App\Models\s2wPrize;

#[\AllowDynamicProperties]
class SpinPrizeCategory 
{
    public $_explicitType = 'com.smallworlds.widget.spintowin.model.SpinPriceCategory';
    public function __construct($data, $display = null)
    {
       $this->id = $data->id;
       $this->desc = $data->name;
       $this->weighting = $data->weighting;
       $this->background = $data->background;
       $this->count = $data->count;
       $this->prizes = ($display == true) ? s2wPrize::where('categoryId', $data->id)->where('isInPreviewList', true)->get()->map(function($prize) {
        return new SpinPrize($prize);
     }) : s2wPrize::where('categoryId', $data->id)->get()->map(function($prize) {
           return new SpinPrize($prize);
        });
        return $this;
    }
}
<?php
 namespace App\result\spintowin;
  use App\result\ListResult;

  #[\AllowDynamicProperties]
class SpinPriceDetails 
{
    public $_explicitType = 'com.smallworlds.widget.spintowin.model.SpinPriceDetails';
    public function __construct($data)
    {
        $this->spins = (float)$data->spins;
        $this->price = (float)$data->price;
        
        return $this;
    }
}
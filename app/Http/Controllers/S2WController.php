<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\s2wCat;
use App\Models\s2wPrize;
use App\Models\s2w_SpinPriceDetails;
use App\Models\s2wTiming;

class S2WController extends Controller
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

    public function getCategories()
    {
        $categories = s2wCat::all();
        $prizes = s2wPrize::all();
        $totalWeight = 0.0;
        foreach($categories as $cat) 
        {
            if ($cat->isDeluxe == false)
                $totalWeight += $cat->weighting;   
            else
                $totalWeight += $cat->weighting;        
            
            $currWeight = $cat->weighting / $totalWeight * 100;
            $cat->percent = $this->getPercent($currWeight);
            // organize prizes with  a 0 starting index

            $cat->prizes =  s2wPrize::where('categoryId', $cat->id)->get()->toArray();
            // $currWeightArray[] = $currWeight;
            // $weightValue[] = $cat->weighting;
        }
        // if ($categories->isDeluxe == false)
        // {
            

        return response()->json($categories);
    }

    public function getPrices()
    {
        $prices = s2w_SpinPriceDetails::all();
        return response()->json($prices);
    }

    public function getTiming()
    {
        $timing = s2wTiming::all();
        $timing = $timing->first();
        return response()->json($timing);
    }

    public function saveTiming(Request $request)
    {
        // only change id 1

        // error checking if number is greater than 0 and less than 5000
        if ($request->timing < 0 || $request->timing > 5000)
        {
            return abort(400, 'Timing must be between 0 and 5000');
        }

        $timing = s2wTiming::find(1);
        $timing->duration = $request->timing;
        $timing->save();

        // return 200 
        return response()->json(['success' => 'Timing updated.']);
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

    private function roundDec($param1, $param2)
    {
        $loc3_ = pow(10,$param2);
        return round($param1 * $loc3_) / $loc3_;
    }
    private function getPercent($currWeight){
        if($currWeight >= 1)
            $currWeight = $this->roundDec($currWeight,0);
        else if($currWeight >= 0.1)
            $currWeight = $this->roundDec($currWeight,1);
        else if($currWeight >= 0.01)
            $currWeight = $this->roundDec($currWeight,2);
        else
            $currWeight = $this->roundDec($currWeight,3);
        
        // store each $currWeight into an array
        //add percent to $cat object
        return $currWeight;
    }
}

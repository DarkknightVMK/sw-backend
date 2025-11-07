<?php

namespace App\Http\Controllers;

use App\Models\catalogItemEntry;
use Illuminate\Http\Request;
use App\Models\items;

class CatalogItemEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    
        // check every item in the catalog compared to the items table, if the item is not in catalogItemEntry table, add it
        $items = items::where('model_tags', 'LIKE', '%' . 'admin' . '%')->get();
        foreach($items as $item){
            $catalogItemEntry = catalogItemEntry::where('modelID', $item->model_id)->first();

            if(!$catalogItemEntry){
                echo $item->model_id . '<br>';
                // $catalogItemEntry = new catalogItemEntry;
                // $catalogItemEntry->item_id = $item->id;
                // $catalogItemEntry->save();
            }
        }
        
        


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\Models\catalogItemEntry  $catalogItemEntry
     * @return \Illuminate\Http\Response
     */
    public function show(catalogItemEntry $catalogItemEntry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\catalogItemEntry  $catalogItemEntry
     * @return \Illuminate\Http\Response
     */
    public function edit(catalogItemEntry $catalogItemEntry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\catalogItemEntry  $catalogItemEntry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, catalogItemEntry $catalogItemEntry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\catalogItemEntry  $catalogItemEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(catalogItemEntry $catalogItemEntry)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoremissionChainsRequest;
use App\Http\Requests\UpdatemissionChainsRequest;
use App\Models\missionChains;

class MissionChainsController extends Controller
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
     * @param  \App\Http\Requests\StoremissionChainsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoremissionChainsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\missionChains  $missionChains
     * @return \Illuminate\Http\Response
     */
    public function show(missionChains $missionChains)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\missionChains  $missionChains
     * @return \Illuminate\Http\Response
     */
    public function edit(missionChains $missionChains)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatemissionChainsRequest  $request
     * @param  \App\Models\missionChains  $missionChains
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatemissionChainsRequest $request, missionChains $missionChains)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\missionChains  $missionChains
     * @return \Illuminate\Http\Response
     */
    public function destroy(missionChains $missionChains)
    {
        //
    }
}

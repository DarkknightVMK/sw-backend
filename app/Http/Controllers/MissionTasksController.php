<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoremissionTasksRequest;
use App\Http\Requests\UpdatemissionTasksRequest;
use App\Models\missionTasks;

class MissionTasksController extends Controller
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
     * @param  \App\Http\Requests\StoremissionTasksRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoremissionTasksRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\missionTasks  $missionTasks
     * @return \Illuminate\Http\Response
     */
    public function show(missionTasks $missionTasks)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\missionTasks  $missionTasks
     * @return \Illuminate\Http\Response
     */
    public function edit(missionTasks $missionTasks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatemissionTasksRequest  $request
     * @param  \App\Models\missionTasks  $missionTasks
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatemissionTasksRequest $request, missionTasks $missionTasks)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\missionTasks  $missionTasks
     * @return \Illuminate\Http\Response
     */
    public function destroy(missionTasks $missionTasks)
    {
        //
    }
}

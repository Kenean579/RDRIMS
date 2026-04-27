<?php

namespace App\Http\Controllers;

use App\Models\PatentStatus;
use App\Http\Requests\StorePatentStatusRequest;
use App\Http\Requests\UpdatePatentStatusRequest;

class PatentStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatentStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PatentStatus $patentStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatentStatus $patentStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatentStatusRequest $request, PatentStatus $patentStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatentStatus $patentStatus)
    {
        //
    }
}

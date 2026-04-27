<?php

namespace App\Http\Controllers;

use App\Models\CallStatus;
use App\Http\Requests\StoreCallStatusRequest;
use App\Http\Requests\UpdateCallStatusRequest;

class CallStatusController extends Controller
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
    public function store(StoreCallStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CallStatus $callStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CallStatus $callStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCallStatusRequest $request, CallStatus $callStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CallStatus $callStatus)
    {
        //
    }
}

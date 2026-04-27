<?php

namespace App\Http\Controllers;

use App\Models\Patent;
use App\Http\Requests\StorePatentRequest;
use App\Http\Requests\UpdatePatentRequest;

class PatentController extends Controller
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
    public function store(StorePatentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Patent $patent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patent $patent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatentRequest $request, Patent $patent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patent $patent)
    {
        //
    }
}

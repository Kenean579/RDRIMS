<?php

namespace App\Http\Controllers;

use App\Models\FinanceCheck;
use App\Http\Requests\StoreFinanceCheckRequest;
use App\Http\Requests\UpdateFinanceCheckRequest;

class FinanceCheckController extends Controller
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
    public function store(StoreFinanceCheckRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FinanceCheck $financeCheck)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FinanceCheck $financeCheck)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFinanceCheckRequest $request, FinanceCheck $financeCheck)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinanceCheck $financeCheck)
    {
        //
    }
}

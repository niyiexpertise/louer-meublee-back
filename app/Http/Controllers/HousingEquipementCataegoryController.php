<?php

namespace App\Http\Controllers;

use App\Models\housing_equipement_cataegory;
use Illuminate\Http\Request;
use Exception;

class HousingEquipementCataegoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{

    
        } catch(Exception $e) {    
            return response()->json($e);
        }
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
    public function store(Request $request)
    {
        try{

    
        } catch(Exception $e) {    
            return response()->json($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(housing_equipement_cataegory $housing_equipement_cataegory)
    {
        try{

    
        } catch(Exception $e) {    
            return response()->json($e);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(housing_equipement_cataegory $housing_equipement_cataegory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, housing_equipement_cataegory $housing_equipement_cataegory)
    {
        try{

    
        } catch(Exception $e) {    
            return response()->json($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(housing_equipement_cataegory $housing_equipement_cataegory)
    {
        try{

    
        } catch(Exception $e) {    
            return response()->json($e);
        }
    }
}

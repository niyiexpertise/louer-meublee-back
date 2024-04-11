<?php

namespace App\Http\Controllers;

use App\Models\Housing;
use App\Models\Photo;
use Illuminate\Http\Request;
use Exception;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;

class HousingController extends Controller
{ 
    /**
     * Display a listing of the resource.
     */
    public function index($r){

        try{
            
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try{
            
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{

            
            $housing = new Housing();
            $housing->housing_type_id = $request->housing_type_id;
            $housing->property_type_id = $request->property_type_id;
            // $housing->user_id = Auth::user()->id;
            $housing->user_id = 1;
            $housing->name = $request->name;
            $housing->description = $request->description;
            $housing->number_of_bedroom = $request->number_of_bedroom;
            $housing->number_of_bed = $request->number_of_bed;
            $housing->number_of_bathroom = $request->number_of_bathroom;
            $housing->number_of_traveller = $request->number_of_traveller;
            $housing->sit_geo_lat = $request->sit_geo_lat;
            $housing->sit_geo_lng = $request->sit_geo_lng;
            $housing->country = $request->country;
            $housing->address = $request->address;
            $housing->city = $request->city;
            $housing->department = $request->department;
            $housing->is_camera  = $request->is_camera;
            $housing->is_accepted_animal = $request->is_accepted_animal;
            $housing->is_animal_exist = $request->is_animal_exist;
            $housing->is_disponible = $request->is_disponible;
            $housing->interior_regulation = $request->interior_regulation;
            $housing->telephone = $request->telephone;
            $housing->status = $request->status;
            // $housing->image_housing = $name;
            $housing->arrived_independently = $request->arrived_independently;
            $housing->cleaning_fees = $request->cleaning_fees;
            $housing->created_at = $request->created_at;
            $housing->number_of_living_room = $request->number_of_living_room;
            $housing->updated_at = $request->updated_at;
            $housing->is_instant_reservation = $request->is_instant_reservation;
            $housing->maximum_duration = $request->maximum_duration;
            $housing->minimum_duration = $request->minimum_duration;
            $housing->time_before_reservation = $request->time_before_reservation;
            $housing->cancelation_condition = $request->cancelation_condition;
            $housing->departure_condition = $request->departure_condition;
            $housing->save();
            $photo = new Photo();
            $imagePaths = [];
            $baseURL = $request->getSchemeAndHttpHost();
           
            foreach ($request->path as $file) {
                $photo->housing_id = $housing->id;
                $filename = uniqid() . '.' . $request->file('file')->getClientOriginalExtension();
                $photo->extension = $request->file('file')->getClientOriginalExtension();
                $file->save(public_path('image/photo/' . $filename));
                $imageUrl = $baseURL . 'image/photo/' . $filename;
                $photo->extension = $imageUrl;
                $photo->save();
            }



            return response()->json([
                'data' => $housing,
                'message' => 'save successfully'
            ]);
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            
        }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ],500);
      };
    }
}

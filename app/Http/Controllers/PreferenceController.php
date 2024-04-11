<?php

namespace App\Http\Controllers;

use App\Models\Preference;
use Illuminate\Http\Request;
use Exception;



class PreferenceController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/preference/index",
     *     summary="Get all preferences",
     *     tags={"Preference"},
     *     @OA\Response(
     *         response=200,
     *         description="List of preferences"
     * 
     *     )
     * )
     */
    public function index()
    {
        try{
                $preferences = Preference::all()->where('is_deleted', false);
                return response()->json(['data' => $preferences], 200);
    
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
 * @OA\Post(
 *     path="/api/preference/store",
 *     summary="Create a new preference",
 *     tags={"Preference"},
 *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="français,anglais,etc")
     *         )
     *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Preference created successfully"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials"
 *     )
 * )
 */
    public function store(Request $request)
    {
        try{
                $data = $request->validate([
                    'name' => 'required|unique:preferences|max:255',
                ]);
                $preference = new Preference();
                $preference->name = $request->name;
                $preference->save();
                return response()->json(['data' => 'Type de propriété créé avec succès.', 'preference' => $preference], 201);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

 /**
     * @OA\Get(
     *     path="/api/preference/show/{id}",
     *     summary="Get a specific preference by ID",
     *     tags={"Preference"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the preference",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preference details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Preference not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try{
                $preference = Preference::find($id);

                if (!$preference) {
                    return response()->json(['error' => 'Préférence non trouvé.'], 404);
                }
                return response()->json(['data' => $preference], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

/**
     * @OA\Put(
     *     path="/api/preference/update/{id}",
     *     summary="Update a preference by ID",
     *     tags={"Preference"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the preference",
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="français,anglais,etc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preference updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Preference not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try{
                $data = $request->validate([
                    'name' =>'required | string'
                ]);
                $preference = Preference::whereId($id)->update($data);
                return response()->json(['data' => 'Préférence mise à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

     /**
     * @OA\Delete(
     *     path="/api/preference/destroy/{id}",
     *     summary="Delete a preference by ID",
     *     tags={"Preference"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the preference",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Preference deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Preference not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try{
                $preference = Preference::whereId($id)->update(['is_deleted' => true]);

                if (!$preference) {
                    return response()->json(['error' => 'Préférence non trouvé.'], 404);
                }

                return response()->json(['data' => 'Préférence supprimé avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

        /**
 * @OA\Put(
 *     path="/api/preference/block/{id}",
 *     summary="Block a preference",
 *     tags={"Preference"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the preference to block",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Preference successfully blocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Preference successfully blocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Preference not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Preference not found")
 *         )
 *     )
 * )
 */

    public function block(string $id)
 {
    try{
            $preference = Preference::whereId($id)->update(['is_blocked' => true]);

            if (!$preference) {
                return response()->json(['error' => 'Préférence non trouvé.'], 404);
            }

            return response()->json(['data' => 'This type of propriety is block successfuly.'], 200);
    } catch(Exception $e) {    
        return response()->json($e);
    }


 }

  /**
 * @OA\Put(
 *     path="/api/preference/unblock/{id}",
 *     summary="Unblock a preference",
 *     tags={"Preference"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the preference to unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Preference successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Preference successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Preference not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Preference not found")
 *         )
 *     )
 * )
 */

 public function unblock(string $id)
{
    try{
            $preference = Preference::whereId($id)->update(['is_blocked' => false]);

            if (!$preference) {
                return response()->json(['error' => 'Préférence non trouvé.'], 404);
            }

            return response()->json(['data' => 'his type of propriety is unblock successfuly.'], 200);
    } catch(Exception $e) {    
        return response()->json($e);
    }


}

}

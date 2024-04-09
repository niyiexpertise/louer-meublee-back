<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use Illuminate\Http\Request;
use Exception;



class CriteriaController extends Controller
{
    /**
   * @OA\Get(
   *     path="/api/criteria/index",
   *     summary="Get all criterias",
   *     tags={"Criteria"},
   *     @OA\Response(
   *         response=200,
   *         description="List of criterias"
   * 
   *     )
   * )
   */
  public function index()
  {
    try{
            $criterias = Criteria::all()->where('is_deleted', false);
                return response()->json(['data' => $criterias], 200);
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
*     path="/api/criteria/store",
*     summary="Create a new criteria",
*     tags={"Criteria"},
*      @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"name"},
   *             @OA\Property(property="name", type="string", example="communication,sociabilité,etc")
   *         )
   *     ),
*     @OA\Response(
*         response=201,
*         description="Criteria created successfully"
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
                'name' => 'required|unique:criterias|max:255',
            ]);
            $criteria = new Criteria();
            $criteria->name = $request->name;
            $criteria->save();
            return response()->json(['data' => 'Critère créé avec succès.', 'criteria' => $criteria], 201);
    } catch(Exception $e) {    
        return response()->json($e);
    }

  }

/**
   * @OA\Get(
   *     path="/api/criteria/show/{id}",
   *     summary="Get a specific criteria by ID",
   *     tags={"Criteria"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the criteria",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Criteria details"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Criteria not found"
   *     )
   * )
   */
  public function show(string $id)
  {
    try{
        $criteria = Criteria::find($id);

        if (!$criteria) {
            return response()->json(['error' => 'Critèrenon trouvé.'], 404);
        }

        return response()->json(['data' => $criteria], 200);
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
   *     path="/api/criteria/update/{id}",
   *     summary="Update a criteria by ID",
   *     tags={"Criteria"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the criteria",
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
   *         description="Criteria updated successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Criteria not found"
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
        $criteria = Criteria::whereId($id)->update($data);
        return response()->json(['data' => 'Critère mise à jour avec succès.'], 200);
    } catch(Exception $e) {    
        return response()->json($e);
    }

  }

   /**
   * @OA\Delete(
   *     path="/api/criteria/destroy/{id}",
   *     summary="Delete a criteria by ID",
   *     tags={"Criteria"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the criteria",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=204,
   *         description="Criteria deleted successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Criteria not found"
   *     )
   * )
   */
  public function destroy(string $id)
  {
    try{
            $criteria = Criteria::whereId($id)->update(['is_deleted' => true]);

            if (!$criteria) {
                return response()->json(['error' => 'Critère non trouvé.'], 404);
            }

            return response()->json(['data' => 'Critère supprimé avec succès.'], 200);
    
    } catch(Exception $e) {    
        return response()->json($e);
    }

  }

      /**
* @OA\Put(
*     path="/api/criteria/block/{id}",
*     summary="Block a criteria",
*     tags={"Criteria"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the criteria to block",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Criteria successfully blocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Criteria successfully blocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Criteria not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Criteria not found")
*         )
*     )
* )
*/

  public function block(string $id)
{
    try{
            $criteria = Criteria::whereId($id)->update(['is_blocked' => true]);

            if (!$criteria) {
                return response()->json(['error' => 'Critère non trouvé.'], 404);
            }

            return response()->json(['data' => 'This type of propriety is block successfuly.'], 200);
    
    } catch(Exception $e) {    
        return response()->json($e);
    }


}

/**
* @OA\Put(
*     path="/api/criteria/unblock/{id}",
*     summary="Unblock a criteria",
*     tags={"Criteria"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the criteria to unblock",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Criteria successfully unblocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Criteria successfully unblocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Criteria not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Criteria not found")
*         )
*     )
* )
*/

public function unblock(string $id)
{
    try{
            $criteria = Criteria::whereId($id)->update(['is_blocked' => false]);

            if (!$criteria) {
                return response()->json(['error' => 'Critère non trouvé.'], 404);
            }

            return response()->json(['data' => 'his type of propriety is unblock successfuly.'], 200);
    } catch(Exception $e) {
        return response()->json($e);
    }


}

}

<?php

namespace App\Http\Controllers;

use App\Models\Accessibility;
use Illuminate\Http\Request;
use Exception;

class AccessibilityController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/accessibility/index",
     *     summary="Get all accessibilities",
     *     tags={"Accessibility"},
     *     @OA\Response(
     *         response=200,
     *         description="List of accessibilities"
     * 
     *     )
     * )
     */
    public function index()
    {
        try{
                $accessibilities  = Accessibility::where('is_deleted', false)->get();
                return response()->json([
                    'data' => $accessibilities
                ],200);
        } catch(Exception $e) {    
            return response()->json($e);
        }
       
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

  /**
      * @OA\Post(
      *     path="/api/accessibility/store",
      *     summary="Create a new accessibility ",
      *     tags={"Accessibility"},
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             required={"name"},
      *             @OA\Property(property="name", type="string", example="plage,garage"),
      *             @OA\Property(property="category_id", type="integer")
      *         )
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Accessibility  created successfully"
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
                    'name' => 'required|unique:accessibilities|max:255',
                    'accessibility_id' => 'required'
                ]);
                $accessibility = new Accessibility();
                $accessibility->name = $request->name;
                $accessibility->accessibility_id = $request->accessibility_id;
                $accessibility->save();
                return response()->json([
                    'message' => 'accessibility created successfuly',
                    'data' => $accessibility
                ]);
        } catch(Exception $e) {    
            return response()->json($e);
        }
     }

     /**
     * @OA\Get(
     *     path="/api/accessibility/show/{id}",
     *     summary="Get a specific accessibility by ID",
     *     tags={"Accessibility"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the accessibility",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Accessibility details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Accessibility not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try{
                $accessibility = Accessibility::find($id);

                if (!$accessibility) {
                    return response()->json(['error' => 'Accessibiliténon trouvé.'], 404);
                }

                return response()->json(['data' => $accessibility], 200);
    
        } catch(Exception $e) {    
            return response()->json($e);
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

/**
     * @OA\Put(
     *     path="/api/accessibility/update/{id}",
     *     summary="Update a accessibility by ID",
     *     tags={"Accessibility"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the accessibility",
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","accessibility_id"},
     *             @OA\Property(property="name", type="string", example="plage,garage"),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Accessibility updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Accessibility not found"
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

    
        } catch(Exception $e) {    
            return response()->json($e);
        }
        $data = $request->validate([
            'name' =>'required | string',
            'accessibility_id' => 'required'
        ]);
        $accessibility = Accessibility::whereId($id)->update($data);
        return response()->json([
            'message' => 'accessibility updated successfuly'
        ]);
    }

 /**
     * @OA\Delete(
     *     path="/api/accessibility/destroy/{id}",
     *     summary="Delete a accessibility by ID",
     *     tags={"Accessibility"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the accessibility",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Accessibility deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Accessibility not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try{
                $accessibility = Accessibility::whereId($id)->update(['is_deleted' => true]);

                if (!$accessibility) {
                    return response()->json(['error' => 'Accessibilité non trouvé.'], 404);
                }

                return response()->json(['data' => 'Accessibilité supprimé avec succès.'], 200);
    
        } catch(Exception $e) {    
            return response()->json($e);
        }
      
    }

        /**
 * @OA\Put(
 *     path="/api/accessibility/block/{id}",
 *     summary="Block a accessibility",
 *     tags={"Accessibility"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the accessibility to block",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Accessibility successfully blocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Accessibility successfully blocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Accessibility not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Accessibility not found")
 *         )
 *     )
 * )
 */

    public function block(string $id)
 {
    try{
            $accessibility = Accessibility::whereId($id)->update(['is_blocked' => true]);

            if (!$accessibility) {
                return response()->json(['error' => 'Accessibilité non trouvé.'], 404);
            }

            return response()->json(['data' => 'This type of propriety is block successfuly.'], 200);
    
    } catch(Exception $e) {    
        return response()->json($e);
    }

 }

  /**
 * @OA\Put(
 *     path="/api/accessibility/unblock/{id}",
 *     summary="Unblock a accessibility",
 *     tags={"Accessibility"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the accessibility to unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Accessibility successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Accessibility successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Accessibility not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Accessibility not found")
 *         )
 *     )
 * )
 */

 public function unblock(string $id)
{
    try{
            $accessibility = Accessibility::whereId($id)->update(['is_blocked' => false]);
            if (!$accessibility) {
                return response()->json(['error' => 'Accessibilité non trouvé.'], 404);
            }
            return response()->json(['data' => 'his type of propriety is unblock successfuly.'], 200);
    } catch(Exception $e) {
        return response()->json($e);
    }

}
}

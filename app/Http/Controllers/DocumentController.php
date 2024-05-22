<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Exception;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */

         /**
   * @OA\Get(
   *     path="/api/document/index",
   *     summary="Get all documents",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="List of documents"
   * 
   *     )
   * )
   */
    public function index()
    {
        try{
            $documents = Document::where('is_deleted', false)->get();
            if (!$documents) {
                return response()->json(['error' => 'Document not found.'], 404);
            }
           
            return response()->json(['data' => $documents], 200);
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
*     path="/api/document/store",
*     summary="Create a new document",
*     tags={"Document"},
*security={{"bearerAuth": {}}},
*      @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"name"},
   *             @OA\Property(property="name", type="string", example ="piece d'identite, CIP, passport"),
   *             @OA\Property(property="is_actif", type="boolen", example ="0,1"),

   *         )
   *     ),
*     @OA\Response(
*         response=201,
*         description="Document created successfully"
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
                'name' => 'required|unique:documents|max:255',
                'is_actif' => 'required'
            ]);
            $document = new Document();
            $document->name = $request->name;
            $document->is_actif = $request->is_actif;
            $document->save();
            return response()->json(['data' => 'Document créé avec succès.', 'document' => $document], 201);
    } catch(Exception $e) {    
        return response()->json($e->getMessage());
    }
    }

    /**
     * Display the specified resource.
     */

     /**
   * @OA\Get(
   *     path="/api/document/show/{id}",
   *     summary="Get a specific document by ID",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the document",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Document details"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Document not found"
   *     )
   * )
   */
    public function show($id)
    {
        try{
            $document = Document::find($id);
    
            if (!$document) {
                return response()->json(['error' => 'Document non trouvé.'], 404);
            }
    
            return response()->json(['data' => $document], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }
/**
   * @OA\Put(
   *     path="/api/document/update/{id}",
   *     summary="Update a document by ID",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the document",
   *         @OA\Schema(type="integer")
   *     ),
   *   @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"name"},
   *             @OA\Property(property="name", type="string", example="pice of identity, CIP, passport"),
   *             @OA\Property(property="is_actif", type="booleen", example="0,1")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Document updated successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Document not found"
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Validation error"
   *     )
   * )
   */
    public function update(Request $request, $id)
    {
        try{
            $data = $request->validate([
                'name' =>'required | string',
                'is_actif'=> 'required'
            ]);
            $document = Document::whereId($id)->update($data);
            return response()->json(['data' => 'Document  mise à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }
    }

   /**
   * @OA\Delete(
   *     path="/api/document/destroy/{id}",
   *     summary="Delete a document by ID",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the document",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=204,
   *         description="Document deleted successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Document not found"
   *     )
   * )
   */
    public function destroy($id)
    {
        try{
            $document = Document::whereId($id)->update(['is_deleted' => true]);

            if (!$document) {
                return response()->json(['error' => 'Document  non trouvé.'], 404);
            }

            return response()->json(['data' => 'Document  supprimé avec succès.'], 200);
    
    } catch(Exception $e) {    
        return response()->json($e);
    }
    }

          /**
* @OA\Put(
*     path="/api/document/block/{id}",
*     summary="Block a document",
*     tags={"Document"},
security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the document to block",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Document successfully blocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Document successfully blocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Document not found")
*         )
*     )
* )
*/

    public function block($id)
    {
        try{
            $document = Document::whereId($id)->update(['is_blocked' => true]);

            if (!$document) {
                return response()->json(['error' => 'Document  non trouvé.'], 404);
            }

            return response()->json(['data' => 'This document is block successfuly.'], 200);
    
    } catch(Exception $e) {
        return response()->json($e);
    }
    }

    /**
* @OA\Put(
*     path="/api/document/unblock/{id}",
*     summary="Unblock a document",
*     tags={"Document"},
security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the document to unblock",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Document successfully unblocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Document successfully unblocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Document not found")
*         )
*     )
* )
*/

    public function unblock($id)
    {
        try{
            $document = Document::whereId($id)->update(['is_blocked' => false]);

            if (!$document) {
                return response()->json(['error' => 'Document  non trouvé.'], 404);
            }

            return response()->json(['data' => 'this document is unblock successfuly.'], 200);
    } catch(Exception $e) {
        return response()->json($e);
    }
}

        /**
     * Display a listing of the resource.
     */

         /**
   * @OA\Get(
   *     path="/api/document/document_actif",
   *     summary="Get all documents",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="List of inactif documents"
   * 
   *     )
   * )
   */
    public function document_actif(){
        try{
            $documents = Document::where('is_deleted', false)->where('is_actif', true)->get();
            if (!$documents) {
                return response()->json(['error' => 'Document actif not found.'], 404);
            }
           
            return response()->json([
                'data' => [
                    'documents' => $documents,
                    'nombre' => Document::where('is_deleted', false)->where('is_actif', true)->count()
                ]
            ], 200);
        } catch(Exception $e) {
            return response()->json($e);
        }
    }

            /**
     * Display a listing of the resource.
     */

         /**
   * @OA\Get(
   *     path="/api/document/document_inactif",
   *     summary="Get all documents",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="List of actif documents"
   * 
   *     )
   * )
   */

    public function document_inactif(){
        try{
            $documents = Document::where('is_deleted', false)->where('is_actif', false)->get();
            if (!$documents) {
                return response()->json(['error' => 'Document no actif not found.'], 404);
            }
           
            return response()->json(['data' => $documents], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }
    }
  /**
* @OA\Put(
*     path="/api/document/active/{id}",
*     summary="active a document",
*     tags={"Document"},
security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the document to active",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Document successfully active",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Document successfully active")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Document not found")
*         )
*     )
* )
*/

public function active($id)
{
    try{
        $document = Document::whereId($id)->update(['is_actif' => true]);

        if (!$document) {
            return response()->json(['error' => 'Document  non trouvé.'], 404);
        }

        return response()->json(['data' => 'this document is active successfuly.'], 200);
} catch(Exception $e) {
    return response()->json($e);
}
}

/**
* @OA\Put(
*     path="/api/document/inactive/{id}",
*     summary="inactive a document",
*     tags={"Document"},
security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the document to inactive",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Document successfully inactive",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Document successfully inactive")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Document not found")
*         )
*     )
* )
*/

public function inactive($id)
{
try{
    $document = Document::whereId($id)->update(['is_actif' => false]);

    if (!$document) {
        return response()->json(['error' => 'Document  non trouvé.'], 404);
    }

    return response()->json(['data' => 'this document is inactive successfuly.'], 200);
} catch(Exception $e) {
return response()->json($e);
}
}

}
<?php

namespace App\Http\Controllers;

use App\Models\verification_document;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

class VerificationDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */

         /**
     * Display a listing of the resource.
     */

         /**
   * @OA\Get(
   *     path="/api/verification_document/index",
   *     summary="Get all verification_document",
   *     tags={"verification_document"},
   *     @OA\Response(
   *         response=200,
   *         description="List of verification_document"
   * 
   *     )
   * )
   */
    public function index()
    {
        try{
            $verification_documents = verification_document::where('is_deleted', false)->get();
            if (!$verification_documents) {
                return response()->json('verification_document not found');
            }
            return response()->json(['data' => $verification_documents], 200);
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

        /**
* @OA\Post(
*     path="/api/verification_document/store",
*     summary="Create a new document",
*     tags={"verification_document"},
*      @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="document_id", type="integer", example="1"),
 *         @OA\Property(
 *           property="path",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *         required={"path", "document_id"}
 *       )
 *     )
 *   ),
*     @OA\Response(
*         response=201,
*         description="verification_document created successfully"
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

            if ($request->hasFile('path')) {
                $path_name = uniqid() . '.' . $request->file('path')->getClientOriginalExtension();
                $path_path = $request->file('path')->move(public_path('image/document'), $path_name);
                $base_url = url('/');
                $path_url = $base_url . '/image/document/' . $path_name;
            }
            $verification_document = new Verification_document();
            // $verification_document->user_id = Auth::user()->id;
            $verification_document->user_id = 1;
            $verification_document->document_id = $request->document_id;
            $verification_document->path = $path_url;
            $verification_document->save();
            return response()->json(['data' => 'verification document correspondant créé avec succès.', 'verification_document' => $verification_document], 201);
    } catch(Exception $e) {
        return response()->json($e);
    }

    }

    /**
     * Display the specified resource.
     */

         /**
     * Display the specified resource.
     */

     /**
   * @OA\Get(
   *     path="/api/verification_document/show/{id}",
   *     summary="Get a specific verification_document by ID",
   *     tags={"verification_document"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the verification_document",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="verification_document details"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="verification_document not found"
   *     )
   * )
   */
    public function show($id)
    {
        try{
            $verification_document = verification_document::find($id);
    
            if (!$verification_document) {
                return response()->json(['error' => 'verification document correspondant non trouvé.'], 404);
            }
    
            return response()->json(['data' => $verification_document], 200);
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */

        /**
   * @OA\Delete(
   *     path="/api/verification_document/destroy/{id}",
   *     summary="Delete a verification_document by ID",
   *     tags={"Document"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the verification_document",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=204,
   *         description="verification_document deleted successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="verification_document not found"
   *     )
   * )
   */
    public function destroy($id)
    {
        try{
            $verification_document = verification_document::whereId($id)->update(['is_deleted' => true]);

            if (!$verification_document) {
                return response()->json(['error' => 'Document  non trouvé.'], 404);
            }

            return response()->json(['data' => 'Document  supprimé avec succès.'], 200);
    
    } catch(Exception $e) {    
        return response()->json($e);
    }
    }


              /**
* @OA\Put(
*     path="/api/verification_document/block/{id}",
*     summary="Block a verification_document",
*     tags={"verification_document"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the verification_document to block",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="verification_document successfully blocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="verification_document successfully blocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="verification_document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="verification_document not found")
*         )
*     )
* )
*/
    public function block($id)
    {
        try{
            $verification_document = verification_document::whereId($id)->update(['is_blocked' => true]);

            if (!$verification_document) {
                return response()->json(['error' => 'Document  non trouvé.'], 404);
            }

            return response()->json(['data' => 'Document  supprimé avec succès.'], 200);
    
    } catch(Exception $e) {    
        return response()->json($e);
    }
    }

                  /**
* @OA\Put(
*     path="/api/verification_document/unblock/{id}",
*     summary="Block a verification_document",
*     tags={"verification_document"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the verification_document to block",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="verification_document successfully blocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="verification_document successfully blocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="verification_document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="verification_document not found")
*         )
*     )
* )
*/

    public function unblock($id)
    {
        try{
            $verification_document = verification_document::whereId($id)->update(['is_blocked' => false]);

            if (!$verification_document) {
                return response()->json(['error' => 'Document  non trouvé.'], 404);
            }

            return response()->json(['data' => 'Document  supprimé avec succès.'], 200);
    
    } catch(Exception $e) {    
        return response()->json($e);
    }
    }
}

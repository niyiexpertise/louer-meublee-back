<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/review/index",
     *     summary="Get all reviews",
     *     tags={"Review"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of reviews"
     * 
     *     )
     * )
     */
    public function index()
  {
    try {
        $reviews = Review::where('is_deleted', false)
                        ->with('user') 
                        ->get();
        return response()->json([
            'data' => $reviews
        ], 200);
    } catch (Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
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
 *     path="/api/review/store",
 *     operationId="createReview",
 *     summary="Créer un nouvel avis",
 *     tags={"Review"},
 * security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"content"},
 *             @OA\Property(property="content", type="string", example="J'apprécie la démarche, etc.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Avis créé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Création réussie"),
 *          
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Identifiants invalides"
 *     )
 * )
 */
public function store(Request $request)
{
    try {
        $review = new Review();
        $review->user_id = Auth::user()->id;
        $review->content = $request->content;
        $review->save();

        return response()->json([
            'message' => 'Commentaire ajouté avec succès',
            'data' => $review
        ],201);
    } catch (Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }
}

     /**
     * @OA\Get(
     *     path="/api/review/show/{id}",
     *     summary="Get a specific review by ID",
     *     tags={"Review"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the review",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try{
            $review = Review::find($id);
            if (!$review) {
                return response()->json(['error' => 'commentaire non trouvé.'], 404);
            }
            return response()->json([
                'data' => $review
            ]);
        }catch (Exception $e){
              return response()->json(['error' => $e->getMessage()], 500);
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
     *     path="/api/review/update/{id}",
     *     summary="Update a review by ID",
     *     tags={"Review"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the review",
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content","review_id"},
     *             @OA\Property(property="content", type="string", example="j'apprécie la démarche,etc"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'content' => 'required'
            ]);
    
            $review = Review::findOrFail($id);
            $review->update($data);
    
            return response()->json([
                'message' => 'Review updated successfully',
                'data' => $review
            ]);
        } catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    

 /**
     * @OA\Delete(
     *     path="/api/review/destroy/{id}",
     *     summary="Delete a review by ID",
     *     tags={"Review"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the review",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Review deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try{
            $review = Review::whereId($id)->update(['is_deleted' => true]);

            if (!$review) {
                return response()->json(['error' => 'Commentaire non trouvé.'], 404);
            }

            return response()->json(['data' => 'Commentaire supprimé avec succès.'], 200);
        }catch (Exception $e){
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }

/**
    * @OA\Put(
        *     path="/api/review/block/{id}",
        *     summary="Block a review",
        *     tags={"Review"},
        *security={{"bearerAuth": {}}},
        *     @OA\Parameter(
        *         name="id",
        *         in="path",
        *         description="ID of the review to block",
        *         required=true,
        *         @OA\Schema(
        *             type="integer",
        *             format="int64"
        *         )
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Review successfully blocked",
        *         @OA\JsonContent(
        *             @OA\Property(property="data", type="string", example="Review successfully blocked")
        *         )
        *     ),
        *     @OA\Response(
        *         response=404,
        *         description="Review not found",
        *         @OA\JsonContent(
        *             @OA\Property(property="error", type="string", example="Review not found")
        *         )
        *     )
        * )
        */
    public function block(string $id)
    {
        try{
            $review = Review::whereId($id)->update(['is_blocked' => true]);

            if (!$review) {
                return response()->json(['error' => 'Commentaire non trouvé.'], 404);
            }

            return response()->json(['data' => 'Commentaire bloqué avec succès.'], 200);
        }catch (Exception $e){
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }


      /**
 * @OA\Put(
 *     path="/api/review/unblock/{id}",
 *     summary="Unblock a review",
 *     tags={"Review"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the review to unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Review successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Review successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Review not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Review not found")
 *         )
 *     )
 * )
 */
    public function unblock(string $id)
    {
        try{
            $review = Review::whereId($id)->update(['is_blocked' => false]);

            if (!$review) {
                return response()->json(['error' => 'Commentaire non trouvé.'], 404);
            }

            return response()->json(['data' => 'Commentaire débloqué avec succès.'], 200);
        }catch (Exception $e){
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

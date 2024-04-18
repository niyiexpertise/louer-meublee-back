<?php

namespace App\Http\Controllers;

use App\Models\Review_reservation;
use App\Models\Category;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ReviewReservationController extends Controller
{

 /**
 * @OA\Post(
 *     path="/api/reservation/reviews/note/add",
 *     tags={"Reviews and Note"},
 *     summary="Stocke les notes pour chaque critère ainsi que le commentaire général de la réservation",
 *     description="Stocke les notes pour chaque critère ainsi que le commentaire général de la réservation.",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="reservation_id", type="integer", example="1", description="ID de la réservation"),
 *                 @OA\Property(
 *                     property="criteria_notes",
 *                     type="array",
 *                     description="Tableau des notes pour chaque critère",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="criteria_id", type="integer", example="1", description="ID du critère"),
 *                         @OA\Property(property="note", type="number", format="float", example="5", description="Note attribuée au critère")
 *                     )
 *                 ),
 *                 @OA\Property(property="general_comment", type="string", nullable=true, description="Commentaire général sur la réservation")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès - Notes et commentaire ajoutés avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Notes et commentaire ajoutés avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object", additionalProperties={"type": "string"})
 *         )
 *     )
 * )
 */
   
    public function AddReviewNote(Request $request)
{
    $validatedData = $request->validate([
        'reservation_id' => 'required|exists:reservations,id',
        'criteria_notes' => 'required|array',
        'criteria_notes.*.criteria_id' => 'required|exists:criterias,id',
        'criteria_notes.*.note' => 'required|numeric|min:0|max:10', 
        'general_comment' => 'nullable|string', 
    ]);
    $userId = Auth::id();
    foreach ($validatedData['criteria_notes'] as $criteriaNote) {
        $note = new Note();
        $note->user_id = $userId ;
        $note->reservation_id = $validatedData['reservation_id'];
        $note->criteria_id = $criteriaNote['criteria_id'];
        $note->note = $criteriaNote['note'];
        $note->save();
    }

    if (isset($validatedData['general_comment'])) {
        $review = new Review_reservation();
        $review->user_id = $userId ;
        $review->reservation_id = $validatedData['reservation_id'];
        $review->content = $validatedData['general_comment'];
        $review->save();
    }

    return response()->json(['message' => 'Notes et commentaire ajoutés avec succès']);
}
}

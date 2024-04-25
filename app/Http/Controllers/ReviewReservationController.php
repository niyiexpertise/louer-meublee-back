<?php

namespace App\Http\Controllers;

use App\Models\Review_reservation;
use App\Models\Category;
use App\Models\Note;
use App\Models\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Housing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewReservationController extends Controller
{

 /**
 * @OA\Post(
 *     path="/api/reservation/reviews/note/add",
 *   tags={"Note et Commentaire sur les reservation (Logement)"},
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
 
     $criteriaIds = [];
     $duplicateCriteriaIds = [];
 
     foreach ($validatedData['criteria_notes'] as $criteriaNote) {
         if (in_array($criteriaNote['criteria_id'], $criteriaIds)) {
             $duplicateCriteriaIds[] = $criteriaNote['criteria_id'];
         } else {
             $criteriaIds[] = $criteriaNote['criteria_id'];
         }
     }
 
     if (count($duplicateCriteriaIds) > 0) {
         return response()->json([
             'message' => 'Les ID des critères suivants se répètent : ' . implode(', ', $duplicateCriteriaIds)
         ], 400);
     }
 
     foreach ($validatedData['criteria_notes'] as $criteriaNote) {
         $existingNote = Note::where('reservation_id', $validatedData['reservation_id'])
             ->where('user_id', $userId)
             ->where('criteria_id', $criteriaNote['criteria_id'])
             ->first();
 
         if ($existingNote) {
             $criteriaName = Criteria::where('id', $criteriaNote['criteria_id'])->value('name');
             return response()->json([
                 'message' => "Vous avez déjà noté le critère '$criteriaName' avec une note de {$existingNote->note}."
             ], 400);
         }
     }
 
     if (isset($validatedData['general_comment'])) {
         if (Review_reservation::where('reservation_id', $validatedData['reservation_id'])
             ->where('user_id', $userId)
             ->exists()) {
             return response()->json([
                 'message' => 'Vous avez déjà commenté cette réservation.'
             ], 400);
         }
     }
 
     foreach ($validatedData['criteria_notes'] as $criteriaNote) {
         $note = new Note();
         $note->user_id = $userId;
         $note->reservation_id = $validatedData['reservation_id'];
         $note->criteria_id = $criteriaNote['criteria_id'];
         $note->note = $criteriaNote['note'];
         $note->save();
     }
 
     if (isset($validatedData['general_comment'])) {
         $review = new Review_reservation();
         $review->user_id = $userId;
         $review->reservation_id = $validatedData['reservation_id'];
         $review->content = $validatedData['general_comment'];
         $review->save();
     }
 
     return response()->json(['message' => 'Notes et commentaire ajoutés avec succès'], 200);
 }
 


/**
 * @OA\Get(
 *     path="/api/reservation/{housingId}/reviews/note/get",
 *     summary="Lister pour un logement donné la moyenne des notes par critère ,la moyenne globale,les commentaires des utilisateurs.",
 *     tags={"Note et Commentaire sur les reservation (Logement)"},
 *   security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="Identifiant du logement",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Détails des réservations pour un logement donné, des notes des utilisateurs et des commentaires associés",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement ou réservations non trouvés",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message d'erreur")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Message d'erreur")
 *         )
 *     )
 * )
 */

public function LogementAvecMoyenneNotesCritereEtCommentairesAcceuil($housingId)
{
    $reservations = Reservation::where('housing_id', $housingId)->get();

    $criteria_notes = [];

    $user_comments = [];

    foreach ($reservations as $reservation) {
        $notes = Note::where('reservation_id', $reservation->id)->get();

        foreach ($notes as $note) {
            if (!isset($criteria_notes[$note->criteria_id])) {
                $criteria_notes[$note->criteria_id] = [
                    'criteria_name' => $note->criteria->name,
                    'total_notes' => 0,
                    'note_sum' => 0
                ];
            }

            $criteria_notes[$note->criteria_id]['total_notes'] += 1;
            $criteria_notes[$note->criteria_id]['note_sum'] += $note->note;
        }

        $review = Review_reservation::where('reservation_id', $reservation->id)->first();
        if ($review) {
            $user = User::find($review->user_id);
            $user_comments[] = [
                'content' => $review->content,
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
                'user' => $user, 
            ];
        }
    }

    $average_notes_by_criteria = [];
    $overall_note_sum = 0;
    $total_criteria_count = 0;

    foreach ($criteria_notes as $criteria_id => $details) {
        $average_note = $details['note_sum'] / $details['total_notes'];
        $average_notes_by_criteria[] = [
            'criteria_name' => $details['criteria_name'],
            'average_note' => round($average_note, 2)
        ];

        $overall_note_sum += $average_note;
        $total_criteria_count += 1;
    }

    $overall_average = $total_criteria_count > 0 ? round($overall_note_sum / $total_criteria_count, 2) : 0;

    return response()->json(['data' =>[
        'average_notes_by_criteria' => $average_notes_by_criteria,
        'overall_average' => $overall_average,
        'user_comments' => $user_comments]
    ]);
}

}

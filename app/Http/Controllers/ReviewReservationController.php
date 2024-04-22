<?php

namespace App\Http\Controllers;

use App\Models\Review_reservation;
use App\Models\Category;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Housing;
use App\Models\Reservation;
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

/**
 * @OA\Get(
 *   path="/api/reservation/reviews/note/get",
 *   summary="retourne les notes et commentaires des utilisateurs pour chaque logement . on retourne aussi la moyenne des notes pour chaque utilisateur sur un logement donné et la moyenne des notes sur un logement donné. ",
 *   tags={"Note et Commentaire sur les reservation (Logement)"},
 *   security={{"bearerAuth": {}}},
 *   @OA\Response(
 *     response=200,
 *     description="Succès",
 *     @OA\JsonContent(
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Réservation non trouvée",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", description="Message d'erreur")
 *     )
 *   ),
 *   @OA\Response(
 *     response=400,
 *     description="Paramètre manquant ou invalide",
 *     @OA\JsonContent(
 *       @OA\Property(property="error", type="string", description="Message d'erreur")
 *     )
 *   )
 * )
 */
public function ListeDesLogementsAvecNoteCommentaire() {
    $housings = Housing::all();

    if ($housings->isEmpty()) {
        return response()->json(['message' => 'Aucun logement trouvé'], 404);
    }

    $housingSummary = [];

    foreach ($housings as $housing) {
        $reservations = Reservation::where('housing_id', $housing->id)->get();

        if ($reservations->isEmpty()) {
            continue; 
        }

        $reservationSummaries = [];
        $totalAverage = 0;
        $reservationCount = 0;

        foreach ($reservations as $reservation) {
            $notes = Note::where('reservation_id', $reservation->id)
                ->with(['user', 'criteria'])
                ->get();

            $review = Review_reservation::where('reservation_id', $reservation->id)->first();
            $reservationComment = $review ? $review->content : null;

            if ($notes->isEmpty()) {
                continue; 
            }

            $userNote = $notes->first();
            $user = $userNote->user;

            $criteriaNotes = $notes->map(function($note) {
                return [
                    'criteria_id' => $note->criteria_id,
                    'criteria_name' => $note->criteria->name,
                    'note' => $note->note,
                ];
            });

            $userAverageScore = $criteriaNotes->avg('note');

            $totalAverage += $userAverageScore;
            $reservationCount++;

            $reservationSummaries[] = [
                'reservation_id' => $reservation->id,
                'user_note_review' => [
                    'user_detil' => $user,
                    'notes' => $criteriaNotes,
                    'average_score' => $userAverageScore,
                    'reservation_comment' => $reservationComment,
                    
                ],
            ];
        }

        $housingAverage = $reservationCount > 0 ? ($totalAverage / $reservationCount) : 0;

        $housingSummary[] = [
            'housing_id' => $housing->id,
            'housing_name' => $housing->name,
            'reservations' => $reservationSummaries,
            'housing_average' => $housingAverage,
        ];
    }

    return response()->json(['housing_summary' => $housingSummary], 200);
}

/**
 * @OA\Get(
 *     path="/api/reservation/{housingId}/reviews/note/get",
 *     summary="Récupérer les notes des utilisateurs et les commentaires associés à un logement spécifique",
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
 *         description="Détails des réservations, des notes des utilisateurs et des commentaires associés",
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
public function LogementAvecNotesEtCommentaires($housingId) {
    $housing = Housing::find($housingId);

    if (!$housing) {
        return response()->json(['message' => 'Logement non trouvé'], 404);
    }

    $reservations = Reservation::where('housing_id', $housingId)->get();

    if ($reservations->isEmpty()) {
        return response()->json(['message' => 'Aucune réservation associée à ce logement'], 404);
    }

    $reservationSummaries = [];
    $totalAverage = 0;
    $reservationCount = 0;

    foreach ($reservations as $reservation) {
        $notes = Note::where('reservation_id', $reservation->id)
            ->with(['user', 'criteria'])
            ->get();

        $review = Review_reservation::where('reservation_id', $reservation->id)->first();
        $reservationComment = $review ? $review->content : null;

        if ($notes->isEmpty()) {
            continue; 
        }

        $userAverageScore = $notes->avg('note');

        $user = $notes->first()->user; 
        $criteriaNotes = $notes->map(function($note) {
            return [
                'criteria_id' => $note->criteria_id,
                'criteria_name' => $note->criteria->name,
                'note' => $note->note,
                'content' => $note->content,
            ];
        });

        $totalAverage += $userAverageScore;
        $reservationCount++;

        $reservationSummaries[] = [
            'reservation_id' => $reservation->id,
            'user_note_review' => [
                'user_detil' => $user,
                'notes' => $criteriaNotes,
                'average_score' => $userAverageScore,
                'reservation_comment' => $reservationComment,
                
            ],
        ];
    }

    $housingAverage = $reservationCount > 0 ? ($totalAverage / $reservationCount) : 0;

    return response()->json([
        'housing_id' => $housingId,
        'housing_name' => $housing->name,
        'reservations' => $reservationSummaries,
        'housing_average' => $housingAverage,
    ], 200);
}


}

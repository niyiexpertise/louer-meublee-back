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
         'reservation_id' => 'required',
         'criteria_notes' => 'required|array',
         'criteria_notes.*.criteria_id' => 'required|exists:criterias,id',
         'criteria_notes.*.note' => 'required|numeric|min:0|max:10',
         'general_comment' => 'nullable|string',
     ]);
     $userId = Auth::id();
     $reservation = Reservation::find($request->reservation_id);
     if(!$reservation){
        return (new ServiceController())->apiResponse(404,[], 'Reservation non trouvé');
     }

     if($userId != $reservation->user_id){
        return (new ServiceController())->apiResponse(404,[], 'Vous ne pouvez pas donner votre avis ou une note sur une réservation qui ne vous appartient pas');
     }

     if($reservation->is_integration !=1){
        return (new ServiceController())->apiResponse(404,[], 'Vous ne pouvez pas donner votre avis ou une note sur une réservation pour laquelle vous n\'avez pas encore intégrer le logement concerné');
     }

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

     return (new ServiceController())->apiResponse(200,[], 'Avis effectué avec succès');

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
     $user_notes = [];  // Pour stocker les notes par utilisateur
 
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
 
             // Ajouter les notes de l'utilisateur
             if (!isset($user_notes[$note->user_id])) {
                 $user_notes[$note->user_id] = [
                     'note_sum' => 0,
                     'total_notes' => 0
                 ];
             }
 
             $user_notes[$note->user_id]['note_sum'] += $note->note;
             $user_notes[$note->user_id]['total_notes'] += 1;
         }
 
         $reviews = Review_reservation::where('reservation_id', $reservation->id)->get();
         if ($reviews) {
             foreach ($reviews as $review) {
                 $user = User::find($review->user_id);
                 $user_comments[] = [
                     'content' => $review->content,
                     'created_at' => $review->created_at,
                     'updated_at' => $review->updated_at,
                     'user' => [
                         'id' => $user->id,
                         'lastname' => $user->lastname,
                         'firstname' => $user->firstname,
                         'country' => $user->country,
                         'city' => $user->city,
                         'address' => $user->address,
                         'file_profil' => $user->file_profil,
                         'created_at' => $user->created_at,
                     ],
                 ];
             }
         }
     }
 
     $average_notes_by_criteria = [];
     $overall_note_sum = 0;
     $total_criteria_count = 0;
 
     foreach ($criteria_notes as $details) {
         $average_note = $details['note_sum'] / $details['total_notes'];
         $average_notes_by_criteria[] = [
             'criteria_name' => $details['criteria_name'],
             'average_note' => round($average_note, 2),
             'nb_personne' => $details['total_notes']
         ];
 
         $overall_note_sum += $average_note;
         $total_criteria_count += 1;
     }
 
     $overall_average = $total_criteria_count > 0 ? round($overall_note_sum / $total_criteria_count, 2) : 0;
 
     // Calculer les moyennes des utilisateurs
     $user_average_notes = [];
     foreach ($user_notes as $user_id => $data) {
         $average_note = $data['note_sum'] / $data['total_notes'];
         $user_average_notes[$user_id] = round($average_note, 2);
     }
 
     // Compter les utilisateurs par note globale
     $note_distribution = [];
     $note_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
     foreach ($user_average_notes as $average_note) {
         $rounded_note = round($average_note);
         if (isset($note_counts[$rounded_note])) {
             $note_counts[$rounded_note]++;
         }
     }
 
     // Transformer les données en format requis
     foreach ($note_counts as $note => $count) {
         $note_distribution[] = [
             'note' => $note,
             'effectif' => $count
         ];
     }
 
     return response()->json([
         'data' => [
             'average_notes_by_criteria' => $average_notes_by_criteria,
             'overall_average' => $overall_average,
             'user_comments' => $user_comments,
             'note_distribution' => $note_distribution // Distribution des notes au format requis
         ]
     ]);
 }
 




/**
 * @OA\Get(
 *     path="/api/reservation/statistiques_notes/{housing_id}",
 *     summary="Implementation de la fonction qui retourne pour un logement donné,la statistique des notes obtenus par utilisateur(exemple. on dira le nombre de personne qui ont noté 5,le nombre personne qui ont noté 4,le nombre de personne qui ont noté 3,etc",
 *     description="Cette API retourne le nombre total d'utilisateurs ayant donné leur avis sur le logement ainsi que le nombre de notes obtenues pour chaque valeur de 1 à 5, et le pourcentage de chaque catégorie de notes.",
 *     tags={"Note et Commentaire sur les reservation (Logement)"},
 *    security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="housing_id",
 *         in="path",
 *         required=true,
 *         description="L'ID du logement",
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Statistiques des notes des utilisateurs pour le logement donné",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="logement_id", type="integer", example=1),
 *             @OA\Property(property="nombre_total_utilisateurs", type="integer", example=10),
 *             @OA\Property(
 *                 property="statistiques",
 *                 type="object",
 *                 @OA\Property(property="notes_5", type="integer", example=5),
 *                 @OA\Property(property="notes_4", type="integer", example=3),
 *                 @OA\Property(property="notes_3", type="integer", example=1),
 *                 @OA\Property(property="notes_2", type="integer", example=1),
 *                 @OA\Property(property="notes_1", type="integer", example=0)
 *             ),
 *             @OA\Property(
 *                 property="pourcentages",
 *                 type="object",
 *                 @OA\Property(property="notes_5", type="number", format="float", example=50.0),
 *                 @OA\Property(property="notes_4", type="number", format="float", example=30.0),
 *                 @OA\Property(property="notes_3", type="number", format="float", example=10.0),
 *                 @OA\Property(property="notes_2", type="number", format="float", example=10.0),
 *                 @OA\Property(property="notes_1", type="number", format="float", example=0.0)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Paramètres invalides ou erreur de requête"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur"
 *     )
 * )
 */


public function getStatistiquesDesNotes($logementId)
{

    $reservations = Reservation::where('housing_id', $logementId)->get();

    if ($reservations->isEmpty()) {
        return response()->json(['message' => 'Aucune réservation trouvée pour ce logement'], 404);
    }


    $notes = Note::whereIn('reservation_id', $reservations->pluck('id'))->get();

    $notesParUtilisateur = $notes->groupBy('user_id');

    $nombreTotalUtilisateurs = $notesParUtilisateur->count();
    $moyennesParUtilisateur = $notesParUtilisateur->map(function ($notesUtilisateur) {
        $somme = $notesUtilisateur->sum('note');
        $nombreDeNotes = $notesUtilisateur->count();
        return $somme / $nombreDeNotes;
    });

    $statistiques = [
        'notes_5' => 0,
        'notes_4' => 0,
        'notes_3' => 0,
        'notes_2' => 0,
        'notes_1' => 0,
    ];

    foreach ($moyennesParUtilisateur as $moyenne) {
        if ($moyenne >= 4.5) {
            $statistiques['notes_5']++;
        } elseif ($moyenne >= 3.5) {
            $statistiques['notes_4']++;
        } elseif ($moyenne >= 2.5) {
            $statistiques['notes_3']++;
        } elseif ($moyenne >= 1.5) {
            $statistiques['notes_2']++;
        } else {
            $statistiques['notes_1']++;
        }
    }

    $pourcentages = [];
    foreach ($statistiques as $key => $count) {
        $pourcentage = ($nombreTotalUtilisateurs > 0) ? ($count / $nombreTotalUtilisateurs) * 100 : 0;
        $pourcentages[$key] = round($pourcentage, 2);
    }

    return response()->json([
        'logement_id' => $logementId,
        'nombre_total_utilisateurs' => $nombreTotalUtilisateurs,
        'statistiques' => $statistiques,
        'pourcentages' => $pourcentages,
    ], 200);
}


}

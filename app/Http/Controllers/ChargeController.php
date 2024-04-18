<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Housing_charge;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File as F ;
use Illuminate\Validation\ValidationException ;
use Exception;


class ChargeController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/charge/index",
     *     summary="Get all charges",
     *     tags={"Charge"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of charges"
     *
     *     )
     * )
     */



     public function index()
     {
         try{
                 $charges = Charge::where('is_deleted', false)->get();
                 return response()->json(['data' => $charges], 200);
         } catch(Exception $e) {
             return response()->json($e->getMessage());
         }
 
     }

    /**
     * Show the form for creating a new resource.
     */


 /**
         * @OA\Post(
         *     path="/api/charge/store",
         *     summary="Create a new charge ",
         *     tags={"Charge"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="électricité"),
 *         @OA\Property(
 *           property="icone",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *       )
 *     )
 *   ),
         *     @OA\Response(
         *         response=200,
         *         description="Charge  created successfully"
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
                'name' => 'required|unique:charges|max:255',
            ]);
            $charge = new Charge();
            if ($request->hasFile('icone')) {
                $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                $identity_profil_path = $request->file('icone')->move(public_path('image/iconeCharge'), $icone_name);
                $base_url = url('/');
                $icone_url = $base_url . '/image/iconeCharge/' . $icone_name;
                $charge->icone = $icone_url;
                }
            $charge->name = $request->name;
            $charge->save();
            return response()->json(['data' => 'Type de charge créé avec succès.', 'charge' => $charge], 201);
    } catch(Exception $e) { 
        return response()->json($e->getMessage());
    }
    }

    /**
     * Show the form for editing the specified resource.
     */
/**
     * @OA\Put(
     *     path="/api/charge/updateName/{id}",
     *     summary="Update a charge by ID",
     *     tags={"Charge"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the charge",
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="charge1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Charge updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Charge not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateName(Request $request, string $id)
    {
        try{
            $charge = Charge::find($id);
            if(!$charge){
                return response()->json(['error' => 'charge non trouvé.'], 404);
            }
            // return response()->json(['error' =>$request->name ]);
                Charge::whereId($id)->update(['name' => $request->name]);
                return response()->json(['data' => 'charge mise à jour avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

   /**
     * @OA\Post(
     *     path="/api/charge/updateIcone/{id}",
     *     summary="Update an charge icone by ID",
     *     tags={"Charge"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the charge to update",
     *         @OA\Schema(type="integer")
     *     ),
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *           property="icone",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *       )
 *     )
 *   ),
     *     @OA\Response(
     *         response=200,
     *         description="Charge updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Charge updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Charge not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Charge not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function updateIcone(Request $request, string $id)
    {
        
        try {
            $charge = Charge::find($id);
            
            if (!$charge) {
                return response()->json(['error' => 'Charge non trouvé.'], 404);
            }
            
            // $request->validate([
            //         'icone' => 'image|mimes:jpeg,jpg,png,gif'
            //     ]);

            $oldProfilePhotoUrl = $charge->icone;
            if ($oldProfilePhotoUrl) {
                $parsedUrl = parse_url($oldProfilePhotoUrl);
                $oldProfilePhotoPath = public_path($parsedUrl['path']);
                if (F::exists($oldProfilePhotoPath)) {
                    F::delete($oldProfilePhotoPath);
                }
            }
                
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $icone_path = $request->file('icone')->move(public_path('image/iconeCharge'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconeCharge/' . $icone_name;
                    
                    Charge::whereId($id)->update(['icone' => $icone_url]);
                    
                    return response()->json(['data' => 'icône de la charge mis à jour avec succès.'], 200);
                } else {
                dd("h");
                return response()->json(['error' => 'Aucun fichier d\'icône trouvé dans la requête.'], 400);
            }
        } catch (QueryException $e) {
            return response()->json(['error' => 'Erreur de requête SQL: ' . $e->getMessage()], 500);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

 /**
     * @OA\Delete(
     *     path="/api/charge/destroy/{id}",
     *     summary="Delete a charge by ID",
     *     tags={"Charge"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the charge",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Charge deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Charge not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try{
            $charge = Charge::find($id);

            if (!$charge) {
                return response()->json(['message' => 'charge non trouvé.'], 404);
            }

            $existingAssociation = Housing_charge::where('charge_id', $id)
            ->exists();
            if ($existingAssociation) {
                return response()->json('cette charge a  déjà été associé à un/ou plusieurs logement(s), veuillez le leur retiré avant de la supprimé ');
            }else{
                $charge->delete();
                return response()->json([
                    'message' => 'deleted successfully',
                ]);
            }
        } catch(Exception $e) {
            return response()->json($e);
        }

    }
}
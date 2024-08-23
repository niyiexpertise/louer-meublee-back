<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\HousingType;
use App\Models\Housing;
use App\Services\FileService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File as F ;
use Illuminate\Validation\ValidationException ;
use Illuminate\Validation\Rule;
/**
 * @OA\Info(
 *      title="Api Louer Meublée",
 *      version="1.0.0",
 *      description="il s'agit de la documentation complète de chaque methode,route,etc",
 *      @OA\Contact(
 *          email="ayenaaurel15@gmail.com",
 *          email="zakiyoubababodi@gmail.com "
 *      )
 * )
 */
class HousingTypeController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

      /**
     * @OA\Get(
     *     path="/api/housingtype/index",
     *     summary="Get all housing types no block",
     *     tags={"HousingType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of housing types"
     *
     *     )
     * )
     */
    public function index()
    {
        try{
            $housingTypes = HousingType::where('is_deleted', false)
            ->where('is_blocked', false)
            ->orderBy('id', 'desc')
            ->get();

                return response()->json(['data' => $housingTypes], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }

    }

          /**
     * @OA\Get(
     *     path="/api/housingtype/indexBlock",
     *     summary="Get all housing types block",
     *     tags={"HousingType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of housing types"
     *
     *     )
     * )
     */
    public function indexBlock()
    {
        try{
                $housingTypes = HousingType::where('is_deleted', false)->where('is_blocked', true)->get();

                return response()->json(['data' => $housingTypes], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }

    }
    /**
         * @OA\Post(
         *     path="/api/housingtype/store",
         *     summary="Create a new housingtype ",
         *     tags={"HousingType"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="chambre partagé"),
 * @OA\Property(property="description", type="string", example="cadre accueillant ... "),
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
         *         description="Housingtype  created successfully"
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
                $validatedData = $request->validate([
                    'name' => 'required|unique:housing_types|max:255',
                    'description' => 'required|string',
                ]);


                $housingType =new HousingType();

                $identity_profil_url = '';
                if ($request->hasFile('icone')) {
                    $identity_profil_url = $this->fileService->uploadFiles($request->file('icone'), 'image/iconeHousingType');
                    $housingType->icone = $identity_profil_url;
                    }

                $housingType->name = $request->name;
                $housingType->description = $request->description;
                $housingType->save();
                return response()->json(['data' => 'Type de type de logement  créé avec succès.', 'housingType' => $housingType], 201);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }

    }

     /**
     * @OA\Get(
     *     path="/api/housingtype/show/{id}",
     *     summary="Get a specific housing type by ID",
     *     tags={"HousingType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the housing type",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Housing type details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Housing type not found"
     *     )
     * )
     */
    public function show($id)
    {
        try{
                $housingType = HousingType::find($id);

                if (!$housingType) {
                    return response()->json(['error' => 'Type de logement non trouvé.'], 404);
                }

                return response()->json(['data' => $housingType], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }

    }
/**
     * @OA\Put(
     *     path="/api/housingtype/update/{id}",
     *     summary="Update a housing type by ID",
     *     tags={"HousingType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the housing type",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "description"},
 *             @OA\Property(property="name", type="string", example="Apartment"),
 *             @OA\Property(property="description", type="string", example="Spacious apartment in the city center")
 *         )
 *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Housing type updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Housing type not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
            */
            public function update(Request $request, $id)
        {
            try {


                $housingType = HousingType::find($id);

                if (!$housingType) {
                    return response()->json(['error' => 'Type de logement non trouvé.'], 404);
                }

                $validatedData = $request->validate([
                    'name' => ['required', 'string', Rule::unique('housing_types')->ignore($id)],
                    'description' => 'required|string',
                ]);

                $existingHousingType = HousingType::where('name', $validatedData['name'])
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingHousingType) {
                    return response()->json(['error' => 'Un autre type de logement avec le même nom existe déjà.'], 409);
                }

                $housingType->update($validatedData);

                return response()->json(['data' => 'Type de logement mis à jour avec succès.'], 200);
            } catch(Exception $e) {
                return response()->json(['error' => $e->getMessage()], 200);
            }
        }



    /**
     * @OA\Post(
     *     path="/api/housingtype/updateIcone/{id}",
     *     summary="Update an housingtype icone by ID",
     *     tags={"HousingType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the housingType to update",
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
     *         description="HousingType updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="HousingType updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="HousingType not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="HousingType not found")
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
            $housingType = HousingType::find($id);

            if (!$housingType) {
                return response()->json(['error' => 'type de logement non trouvé.'], 404);
            }

            // $request->validate([
            //         'icone' => 'image|mimes:jpeg,jpg,png,gif'
            //     ]);

            $oldProfilePhotoUrl = $housingType->icone;
            if ($oldProfilePhotoUrl) {
                $parsedUrl = parse_url($oldProfilePhotoUrl);
                $oldProfilePhotoPath = public_path($parsedUrl['path']);
                if (F::exists($oldProfilePhotoPath)) {
                    F::delete($oldProfilePhotoPath);
                }
            }
                $identity_profil_url = '';
                if ($request->hasFile('icone')) {
                    $identity_profil_url = $this->fileService->uploadFiles($request->file('icone'), 'image/iconeHousingType');;

                    $housingType->icone = $identity_profil_url;
                    $housingType->save();
                    return response()->json(['data' => 'icône de l\'équipement mis à jour avec succès.'], 200);
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
     *     path="/api/housingtype/destroy/{id}",
     *     summary="Delete a housing type by ID",
     *     tags={"HousingType"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the housing type",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Housing type deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Housing type not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        try{
            $housingType = HousingType::find($id);

            if (!$housingType) {
                return response()->json(['error' => 'Type de logement  non trouvé.'], 200);
            }
            $nbexist= Housing::where('housing_type_id', $id)->count();

            if ($nbexist > 0) {
                return response()->json(['error' => "Suppression impossible car ce type de logement est déjà associé à un logement."],200);

            }

            $housingType->is_deleted = true;
            $housingType->save();

            return response()->json(['data' => 'Type de logement  supprimé avec succès.'], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }

    }

    /**
 * @OA\Put(
 *     path="/api/housingtype/block/{id}",
 *     summary="Block a housing type",
 *     tags={"HousingType"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the housing type to block",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="HousingType successfully blocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="HousingType successfully blocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="HousingType not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="HousingType not found")
 *         )
 *     )
 * )
 */
    public function block($id)
    {
        try{
                $housingType = HousingType::find($id);

                if (!$housingType) {
                    return response()->json(['error' => 'HousingType non trouvé.'], 404);
                }

                $housingType->is_blocked = true;
                $housingType->save();

                return response()->json(['data' => 'HousingType bloqué avec succès.'], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }

    }

    /**
 * @OA\Put(
 *     path="/api/housingtype/unblock/{id}",
 *     summary="Unblock a housing type",
 *     tags={"HousingType"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the housing type to unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="HousingType successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="HousingType successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="HousingType not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="HousingType not found")
 *         )
 *     )
 * )
 */
    public function unblock($id)
    {
        try{
                $housingType = HousingType::find($id);

                if (!$housingType) {
                    return response()->json(['error' => 'HousingType non trouvée.'], 404);
                }

                $housingType->is_blocked = false;
                $housingType->save();

                return response()->json(['data' => 'HousingType débloquée avec succès.'], 200);
        } catch(Exception $e) {
            return response()->json($e->getMessage());
        }

    }

    /**
 * @OA\Delete(
 *     path="/api/housingtype/destroymultiple",
 *     summary="Delete multiple housing types by IDs",
 *     tags={"HousingType"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(type="integer", format="int64")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Housing types deleted and not deleted",
 *         @OA\JsonContent(
 *             type="object",
 *             )
 *         )
 *     )
 * )
 */
public function destroymultiple(Request $request)
{
    try{
        $ids = $request->json()->all();

        if (empty($ids)) {
            return response()->json(['error' => 'No IDs provided'], 400);
        }

        $deleted = [];
        $notDeleted = [];
        foreach ($ids as $id) {
            $housingType = HousingType::find($id);

            if (!$housingType) {
                return response()->json("housing_type with id: {$id} not found");
            }
        }
        foreach ($ids as $id) {
            $housingType = HousingType::find($id);

            if (!$housingType) {
                continue;
            }

            $nbexist = Housing::where('housing_type_id', $id)->count();

            if ($nbexist > 0) {
                $notDeleted[] = $housingType->name;
                continue;
            }

            $housingType->is_deleted = true;
            $housingType->save();

            $deleted[] = $housingType->name;
        }

        $response = [
            'deleted' => $deleted,
            'not_deleted(car il est déjà associé à un logement' => $notDeleted
        ];

        return response()->json($response, 200);
    } catch(Exception $e) {
        return response()->json($e->getMessage());
    }
}

}

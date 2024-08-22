<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Equipment_category;
use App\Models\EquipmentCategory;
use App\Models\File;
use App\Models\Housing_category_file;
use App\Services\FileService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder as DatabaseEloquentBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File as F ;
use Illuminate\Validation\ValidationException;

class CategorieController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * @OA\Get(
     *     path="/api/category/VerifiednotBlocknotDelete",
     *     summary="Get all category for admin (not blocked and not deleted)",
     *     tags={"Category"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of category with categories"
     *     )
     * )
     */
    public function VerifiednotBlocknotDelete()
    {
        try {
            $categories = Category::with('equipment_category.equipment')->where('is_verified',true)->where('is_blocked', false)->where('is_deleted', false)->get();
            $data = [];


            foreach ($categories as $category) {
                $a = [];
                $categoryEquipment = Equipment_category::where('category_id', $category->id)->get();
                foreach ($categoryEquipment as $k) {
                    $b = Equipment::where('id', $k->equipment_id)->where('is_verified',true)->get();
                        foreach ($b as $e) {
                            $a[] = [
                                'id_equipement' => $e->id,
                                'id_equipement_category' => $k->id,
                                'name' => $e->name,
                                'icone' => $e->icone,
                                'is_deleted' => $e->is_deleted,
                                'is_blocked' => $e->is_blocked,
                                'created_at' => $e->created_at,
                                'updated_at' => $e->updated_at,
                            ];
                        }
                }

                $data[] = [
                    'id_categorie' => $category->id,
                    'name' =>$category->name,
                    'icone' => $category->icone,
                    'is_deleted' =>$category->id_deleted,
                    'is_blocked' =>$category->is_blocked,
                    'created_at' =>$category->created_at,
                    'updated_at' =>$category->updated_at,
                    'equipments'  => $a,

                ];

            }
            return response()->json([
                'data' => $data
            ], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }

     /**
     * @OA\Get(
     *     path="/api/category/index",
     *     summary="Get all category for admin (not blocked and not deleted)",
     *     tags={"Category"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of category with categories"
     *     )
     * )
     */
    public function index()
    {
        try {
            $categories = Category::with('equipment_category.equipment')->where('is_verified',true)->where('is_blocked', false)->where('is_deleted', false)->get();
            $data = [];


            foreach ($categories as $category) {
                $a = [];
                $categoryEquipment = Equipment_category::where('category_id', $category->id)->get();
                foreach ($categoryEquipment as $k) {
                    $b = Equipment::where('id', $k->equipment_id)->where('is_verified',true)->get();
                        foreach ($b as $e) {
                            $a[] = [
                                'id_equipement' => $e->id,
                                'id_equipement_category' => $k->id,
                                'name' => $e->name,
                                'icone' => $e->icone,
                                'is_deleted' => $e->is_deleted,
                                'is_blocked' => $e->is_blocked,
                                'created_at' => $e->created_at,
                                'updated_at' => $e->updated_at,
                            ];
                        }
                }

                $data[] = [
                    'id_categorie' => $category->id,
                    'name' =>$category->name,
                    'icone' => $category->icone,
                    'is_deleted' =>$category->id_deleted,
                    'is_blocked' =>$category->is_blocked,
                    'created_at' =>$category->created_at,
                    'updated_at' =>$category->updated_at,
                    'equipments'  => $a,

                ];

            }
            return response()->json([
                'data' => $data
            ], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }

        /**
     * @OA\Get(
     *     path="/api/category/VerifiedBlocknotDelete",
     *     summary="Get all category for admin (blocked and not deleted)",
     *     tags={"Category"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of category with categories"
     *     )
     * )
     */
    public function VerifiedBlocknotDelete()
    {
        try {
            $categories = Category::with('equipment_category.equipment')->where('is_verified',true)->where('is_blocked', true)->where('is_deleted', false)->get();
            $data = [];


            foreach ($categories as $category) {
                $a = [];
                $categoryEquipment = Equipment_category::where('category_id', $category->id)->get();
                foreach ($categoryEquipment as $k) {
                    $b = Equipment::where('id', $k->equipment_id)->get();
                        foreach ($b as $e) {
                            $a[] = [
                                'id' => $e->id,
                                'name' => $e->name,
                                'icone' => $e->icone,
                                'is_deleted' => $e->is_deleted,
                                'is_blocked' => $e->is_blocked,
                                'created_at' => $e->created_at,
                                'updated_at' => $e->updated_at,
                            ];
                        }
                }

                $data[] = [
                    'id' => $category->id,
                    'name' =>$category->name,
                    'is_deleted' =>$category->id_deleted,
                    'is_blocked' =>$category->is_blocked,
                    'created_at' =>$category->created_at,
                    'updated_at' =>$category->updated_at,
                    'equipments'  => $a
                ];

            }
            return response()->json([
                'data' => $data
            ], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }


            /**
     * @OA\Get(
     *     path="/api/category/VerifiednotBlockDelete",
     *     summary="Get all category  (not blocked and deleted))",
     *     tags={"Category"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of category with categories"
     *     )
     * )
     */
    public function VerifiednotBlockDelete()
    {
        try {
            $categories = Category::with('equipment_category.equipment')->where('is_verified',true)->where('is_blocked', false)->where('is_deleted', true)->get();
            $data = [];

            foreach ($categories as $category) {
                $a = [];
                $categoryEquipment = Equipment_category::where('category_id', $category->id)->get();
                foreach ($categoryEquipment as $k) {
                    $b = Equipment::where('id', $k->equipment_id)->get();
                        foreach ($b as $e) {
                            $a[] = [
                                'id' => $e->id,
                                'name' => $e->name,
                                'icone' => $e->icone,
                                'is_deleted' => $e->is_deleted,
                                'is_blocked' => $e->is_blocked,
                                'created_at' => $e->created_at,
                                'updated_at' => $e->updated_at,
                            ];
                        }
                }

                $data[] = [
                    'id' => $category->id,
                    'name' =>$category->name,
                    'is_deleted' =>$category->id_deleted,
                    'is_blocked' =>$category->is_blocked,
                    'created_at' =>$category->created_at,
                    'updated_at' =>$category->updated_at,
                    'equipments'  => $a
                ];

            }
            return response()->json([
                'data' => $data
            ], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }

/**
         * @OA\Post(
         *     path="/api/category/store",
         *     summary="Create a new category ",
         *     tags={"Category"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="Bureau,..."),
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
         *         description="Category  created successfully"
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
                     $request->validate([
                         'name' => 'required|unique:categories|max:255',
                     ]);
                     $category = new Category();
                     $identity_profil_url = '';
                     if ($request->hasFile('icone')) {
                        $identity_profil_url = $this->fileService->uploadFiles($request->file('icone'), 'image/iconeCategory');;
                         $category->icone = $identity_profil_url;
                         }
                     $category->name = $request->name;
                     $category->is_verified = true;
                     $category->save();
                     return response()->json([
                         'message' => 'Category is successfully created',
                         'data' => $category
                     ]);

             } catch(Exception $e) {
                 return response()->json($e->getMessage());
             }

         }


     /**
     * @OA\Get(
     *     path="/api/category/show/{id}",
     *     summary="Get a specific category by ID",
     *     tags={"Category"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try{
            $a = [];
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['error' => 'Catégorie non trouvé.'], 404);
            }

            $categoryEquipment = Equipment_category::where('category_id', $category->id)->get();
            // return response()->json($categoryEquipment);
            foreach ($categoryEquipment as $k) {
                $b = Equipment::where('id', $k->equipment_id)->get();
                // return response()->json($b);
                    // $a[] = $b;
                    foreach ($b as $e) {
                        $a[] = [
                            'id' => $e->id,
                            'name' => $e->name,
                            'icone' => $e->icone,
                            'is_deleted' => $e->is_deleted,
                            'is_blocked' => $e->is_blocked,
                            'created_at' => $e->created_at,
                            'updated_at' => $e->updated_at,
                        ];
                    }
            }
            return response()->json([
                'data' => [
                    'id' => $category->id,
                    'name' =>$category->name,
                    'is_deleted' =>$category->id_deleted,
                    'is_blocked' =>$category->is_blocked,
                    'created_at' =>$category->created_at,
                    'updated_at' =>$category->updated_at,
                    'equipments'  => $a
                ]
            ], 200);

        } catch(Exception $e) {
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
     *     path="/api/category/updateName/{id}",
     *     summary="Update category by ID",
     *     tags={"Category"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="cuisine,etc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
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
            $data = $request->validate([
                'name' => [
                    'required',
                    'string',
                    Rule::unique('categories')->ignore($id),
                ],
            ]);
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['error' => 'Catégorie non trouvé.'], 404);
            }

            $category->name = $request->name;
            $category->save();
            return response()->json(['data' => 'Catégorie mise à jour avec succès.'], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/category/updateIcone/{id}",
     *     summary="Update a category icone by ID",
     *     tags={"Category"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to update",
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
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Category updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Category not found")
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
            $category = Category::find($id);

            if (!$category) {
                return response()->json(['error' => 'Category non trouvé.'], 404);
            }

            // $request->validate([
            //         'icone' => 'image|mimes:jpeg,jpg,png,gif'
            //     ]);

            $oldProfilePhotoUrl = $category->icone;
            if ($oldProfilePhotoUrl) {
                $parsedUrl = parse_url($oldProfilePhotoUrl);
                $oldProfilePhotoPath = public_path($parsedUrl['path']);
                if (F::exists($oldProfilePhotoPath)) {
                    F::delete($oldProfilePhotoPath);
                }
            }
                $identity_profil_url = '';
                if ($request->hasFile('icone')) {
                    $identity_profil_url = $this->fileService->uploadFiles($request->file('icone'), 'image/iconeCategory');;

                   // Category::whereId($id)->update(['icone' => $identity_profil_url]);
                   $category->icone = $identity_profil_url;
                   $category->save();

                    return response()->json(['data' => 'icône de la catégorie mis à jour avec succès.'], 200);
                } else {
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
     *     path="/api/category/destroy/{id}",
     *     summary="Delete category by ID",
     *     tags={"Category"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Category deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try{
                $category = Category::find($id);

                if (!$category) {
                    return response()->json(['error' => 'Catégorie non trouvé.'], 404);
                }
                $associatedHousing = Housing_category_file::where('category_id', $id)->count();

        if ($associatedHousing > 0) {
            return response()->json(['error' => "Suppression impossible car la catégorie est déjà associé à un logement."], 200);

        }
            $category->is_deleted = true;
            $category->save();
                return response()->json(['data' => 'Catégorie supprimé avec succès.'], 200);

        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

        /**
 * @OA\Put(
 *     path="/api/category/block/{id}",
 *     summary="Block a category",
 *     tags={"Category"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the category to block",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category successfully blocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Category successfully blocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Category not found")
 *         )
 *     )
 * )
 */

    public function block(string $id)
 {
    try{
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'catgegory non trouvé.'], 404);
        }
            $category->is_blocked = true;
            $category->save();

        return response()->json(['data' => 'This category is block successfuly.'], 200);
    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }


 }

  /**
 * @OA\Put(
 *     path="/api/category/unblock/{id}",
 *     summary="Unblock a category",
 *     tags={"Category"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the category to unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Category successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Category not found")
 *         )
 *     )
 * )
 */
 public function unblock(string $id)
{
    try{
        $category = Category::find($id);
           
            if (!$category) {
                return response()->json(['error' => 'Catégorie non trouvé.'], 404);
            }
            $category->is_blocked = false;
            $category->save();
            return response()->json(['data' => 'this category is unblock successfuly.'], 200);
    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }


}


}

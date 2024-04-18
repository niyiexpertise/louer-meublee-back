<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Equipment_category;
use App\Models\EquipmentCategory;
use App\Models\File;
use App\Models\Housing_category_file;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\Builder as DatabaseEloquentBuilder;

class CategorieController extends Controller
{

    
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
            return response()->json($e);
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
            return response()->json($e);
        }
    }

        /**
     * @OA\Get(
     *     path="/api/category/indexUnverified",
     *     summary="Get all category for admin (blocked and not deleted)",
     *     tags={"Category"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of category with categories"
     *     )
     * )
     */
    public function indexUnverified()
    {
        try {
            $categories = Category::with('equipment_category.equipment')->where('is_deleted', false)->where('is_verified', false)->get();
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
            return response()->json($e);
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
            return response()->json($e);
        }
    }

/**
     * @OA\Post(
     *     path="/api/category/store",
     *     summary="create new category",
     *     tags={"Category"},
     * security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="cuisine,etc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="category add successfuly",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="piscine,cuisine,etc")
     *         )
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
                $category->name = $request->name;
                $category->is_verified = true;
                $category->save();
                return response()->json([
                    'message' => 'Category is successfully created',
                    'data' => $category
                ]);
    
        } catch(Exception $e) {
            return response()->json($e);
        }

    }

    public function storeDefault(Request $request,$housingId){
        $request->validate([
            'name' => 'required|unique:categories|max:255',
        ]);
        $category = new Category();
        $category->name = $request->name;
        $category->is_verified = false;
        $category->save();
        $categoryId = $category->id;
            $housingCategoryId = $housingId;
            $photoCategoryKey = 'photo_categories' . $categoryId;
            $photoFiles = $request->file($photoCategoryKey);
            foreach ($photoFiles as $fileId) {
                // Sauvegarder le fichier dans la table files
                $photoModel = new File();
                $photoName = uniqid() . '.' . $fileId->getClientOriginalExtension();
                $photoPath = $fileId->move(public_path('image/photo_category'), $photoName);
                $photoUrl = url('/image/photo_category/' . $photoName);
            
                $photoModel->path = $photoUrl;
                $photoModel->save();
            
                // Sauvegarder l'association entre le fichier et la catégorie dans la table housing_category_files
                $housingCategoryFile = new Housing_category_file();
                $housingCategoryFile->housing_id = $housingCategoryId;
                $housingCategoryFile->category_id = $categoryId;
                $housingCategoryFile->file_id = $photoModel->id;
                $housingCategoryFile->number = $request->input('number_category');
                $housingCategoryFile->save();
            }
        return response()->json([
            'message' => 'Category is successfully created',
            'data' => $category
        ]);
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
            return response()->json($e);
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
     *     path="/api/category/update/{id}",
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

    public function update(Request $request, string $id)
    {
        try{
            $data = $request->validate([
                'name' =>'required | string'
            ]);
            $category = Category::whereId($id)->update($data);
            return response()->json(['data' => 'Catégorie mise à jour avec succès.'], 200);
        } catch(Exception $e) {
            return response()->json($e);
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
                $category = Category::whereId($id)->update(['is_deleted' => true]);

                if (!$category) {
                    return response()->json(['error' => 'Catégorie non trouvé.'], 404);
                }

                return response()->json(['data' => 'Catégorie supprimé avec succès.'], 200);
    
        } catch(Exception $e) {    
            return response()->json($e);
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
        $category = Category::whereId($id)->update(['is_blocked' => true]);

        if (!$category) {
            return response()->json(['error' => 'catgegory non trouvé.'], 404);
        }

        return response()->json(['data' => 'This category is block successfuly.'], 200);
    } catch(Exception $e) {
        return response()->json($e);
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
            $category = Category::whereId($id)->update(['is_blocked' => false]);
            if (!$category) {
                return response()->json(['error' => 'Catégorie non trouvé.'], 404);
            }
            return response()->json(['data' => 'this category is unblock successfuly.'], 200);
    } catch(Exception $e) {
        return response()->json($e);
    }


}

 /**
     * @OA\Put(
     *     path="/api/category/makeVerified/{id}",
     *     summary="make verified an category",
     *     tags={"Category"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the category to verified",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="category successfully verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="category successfully verified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="category not found")
     *         )
     *     )
     * )
     */
    public function makeVerified(string $id)
    {
        try{
                $category = Category::find($id);
                if (!$category) {
                    return response()->json(['error' => 'Category non trouvé.'], 404);
                }
                if ($category->is_verified == true) {
                    return response()->json(['data' => 'Category déjà vérifié.'], 200);
                }
                Category::whereId($id)->update(['is_verified' => true]);

                return response()->json(['data' => 'Category vérifié avec succès.'], 200);
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

}
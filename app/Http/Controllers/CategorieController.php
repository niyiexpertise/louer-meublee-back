<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\Builder as DatabaseEloquentBuilder;

class CategorieController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/category/index",
     *     summary="Get all categories",
     *     tags={"Category"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categorys"
     *
     *     )
     * )
     */
    public function index()
    {
        try{
                $categories = Category::where('is_deleted',false)->get();
                return response()->json([
                    'data' => $categories
                ],200);
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
     * @OA\Post(
     *     path="/api/category/store",
     *     summary="create new category",
     *     tags={"Category"},
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
                $data = $request->validate([
                    'name' => 'required|unique:categories|max:255',
                ]);
                $category = new Category();
                $category->name = $request->name;
                $category->save();
                return response()->json([
                    'message' => 'Category is successfully created',
                    'data' => $category
                ]);
    
        } catch(Exception $e) {    
            return response()->json($e);
        }

    }

     /**
     * @OA\Get(
     *     path="/api/category/show/{id}",
     *     summary="Get a specific category by ID",
     *     tags={"Category"},
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
            $category = Category::find($id);
            $equipment_category = $category->equipment_category()->get();
            $a = [];
            foreach ($equipment_category as $e) {
                $b= Equipment::where('id',$e->equipment_id)->get();
               foreach ($b as $k) {
                $a[] = [
                    'id' => $k->id,
                    'name' => $k->name,
                    'is_deleted' => $k->is_deleted,
                    'is_blocked' => $k->is_blocked,
                    'created_at' => $k->created_at,
                    'updated_at' => $k->updated_at
                ];
               }
            }
            if (!$category) {
                return response()->json(['error' => 'Catégorie non trouvé.'], 404);
            }

            return response()->json([
                'data' => [
                    'id' => $category->id,
                    'name' =>$category->name,
                    'is_deleted' =>$category->id_deleted,
                    'is_blocked' =>$category->is_blocked,
                    'created_at' =>$category->created_at,
                    'updated_at' =>$category->updated_at,
                    'equipment' => $a
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

}

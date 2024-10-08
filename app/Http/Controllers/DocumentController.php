<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\Rule;
use App\Models\verification_document;
use App\Models\document_type_demande;
use App\Services\FileService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
class DocumentController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display a listing of the resource.
     */

         /**
   * @OA\Get(
   *     path="/api/document/index",
   *     summary="Get all documents",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="List of documents"
   *
   *     )
   * )
   */
  public function index()
{
    try {
        $documents = Document::where('is_deleted', false)
            ->with('document_type_demande.type_demande')
            ->get();

        if ($documents->isEmpty()) {
            return response()->json(['error' => 'Document not found.'], 404);
        }

        $formattedDocuments = $documents->map(function ($document) {
            return [
                'id' => $document->id,
                'name' => $document->name,
                'is_actif' => $document->is_actif,
                'icone' => $document->icone,
                'is_deleted' => $document->is_deleted,
                'is_blocked' => $document->is_blocked,
                'created_at' => $document->created_at,
                'updated_at' => $document->updated_at,
                'types_demandes' => $document->document_type_demande->map(function ($documentTypeDemande) {
                    return [
                        'id' => $documentTypeDemande->type_demande->id,
                        'name' => $documentTypeDemande->type_demande->name
                    ];
                })->filter()
            ];
        });

        return response()->json(['data' => $formattedDocuments], 200);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}







/**
* @OA\Post(
*     path="/api/document/store",
*     summary="Create a new document",
*     tags={"Document"},
*     security={{"bearerAuth": {}}},
*     @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*             required={"name"},
*             @OA\Property(property="name", type="string", example="piece d'identite, CIP, passport"),
*             @OA\Property(property="is_actif", type="boolean", example="1"),
*             @OA\Property(property="type_demande_ids", type="array",
*                 @OA\Items(type="integer")
*             )
*         )
*     ),
*     @OA\Response(
*         response=201,
*         description="Document created successfully"
*     ),
*     @OA\Response(
*         response=401,
*         description="Invalid credentials"
*     )
* )
*/
public function store(Request $request)
{
    try {
        $data = $request->validate([
            'name' => 'required|unique:documents|max:255',
            'is_actif' => 'required|boolean',
            'type_demande_ids' => 'required|array',
            'type_demande_ids.*' => 'integer|exists:type_demandes,id',
        ]);

        $document = new Document();
        $document->name = $data['name'];
        $document->is_actif = $data['is_actif'];
        $document->save();

        if (!empty($data['type_demande_ids'])) {
            foreach ($data['type_demande_ids'] as $typeDemandeId) {
                DB::table('document_type_demandes')->insert([
                    'document_id' => $document->id,
                    'type_demande_id' => $typeDemandeId,
                ]);
            }
        }

        return response()->json(['message' => 'Document créé avec succès.', 'document' => $document], 201);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 200);
    }
}


    /**
     * Display the specified resource.
     */

     /**
   * @OA\Get(
   *     path="/api/document/show/{id}",
   *     summary="Get a specific document by ID",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the document",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Document details"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Document not found"
   *     )
   * )
   */
    public function show($id)
    {
        try{

            $document = Document::where('id', $id)
            ->with('document_type_demande.type_demande')
            ->get();

            if (!$document) {
                return response()->json(['error' => 'Document non trouvé.'], 404);
            }

            return response()->json(['data' => $document], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }

 /**
* @OA\Put(
*     path="/api/document/update/{id}",
*     summary="Update a document by ID",
*     tags={"Document"},
*     security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         required=true,
*         description="ID of the document",
*         @OA\Schema(type="integer")
*     ),
*     @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*             required={"name"},
*             @OA\Property(property="name", type="string", example="piece of identity, CIP, passport"),
*             @OA\Property(property="is_actif", type="boolean", example="1"),
*             @OA\Property(property="type_demande_ids", type="array",
*                 @OA\Items(type="integer")
*             )
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Document updated successfully"
*     ),
*     @OA\Response(
*         response=404,
*         description="Document not found"
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
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('documents')->ignore($id),
            ],
            'is_actif' => 'required|boolean',
            'type_demande_ids' => 'required|array',
            'type_demande_ids.*' => 'integer|exists:type_demandes,id',
        ]);

        $document = Document::findOrFail($id);
        $document->name = $data['name'];
        $document->is_actif = $data['is_actif'];
        $document->save();

        DB::table('document_type_demandes')->where('document_id', $id)->delete();
        if (!empty($data['type_demande_ids'])) {
            foreach ($data['type_demande_ids'] as $typeDemandeId) {
                DB::table('document_type_demandes')->insert([
                    'document_id' => $document->id,
                    'type_demande_id' => $typeDemandeId,
                ]);
            }
        }

        return response()->json(['message' => 'Document mis à jour avec succès.', 'document' => $document], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Document non trouvé.'], 404);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

   /**
   * @OA\Delete(
   *     path="/api/document/destroy/{id}",
   *     summary="Delete a document by ID",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the document",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=204,
   *         description="Document deleted successfully"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Document not found"
   *     )
   * )
   */
    public function destroy($id)
    {
        try{

            $document = Document::find($id);
            if (!$document) {
                return response()->json(['error' => 'Document  non trouvé.'], 200);
            }
            $nbexist=verification_document::where('document_id', $id)->count();

            if ($nbexist > 0) {
                return response()->json(['error' => "Suppression impossible car ce type de document a déjà été utilisé lors d'une soumission de la demande."],200);
            }
            $document->is_deleted = true;
            $document->save();
            return response()->json(['data' => 'Document  supprimé avec succès.'], 200);

    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }
    }

          /**
* @OA\Put(
*     path="/api/document/block/{id}",
*     summary="Block a document",
*     tags={"Document"},
*     security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the document to block",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Document successfully blocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Document successfully blocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Document not found")
*         )
*     )
* )
*/

    public function block($id)
    {
        try{
            $document = Document::find($id);

            if (!$document) {
                return response()->json(['error' => 'Document  non trouvé.'], 404);
            }
            $document->is_blocked = true;
            $document->save();
            return response()->json(['data' => 'This document is block successfuly.'], 200);

    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }
    }

    /**
* @OA\Put(
*     path="/api/document/unblock/{id}",
*     summary="Unblock a document",
*     tags={"Document"},
*security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the document to unblock",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Document successfully unblocked",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Document successfully unblocked")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Document not found")
*         )
*     )
* )
*/

    public function unblock($id)
    {
        try{
            $document = Document::find($id);

            if (!$document) {
                return response()->json(['error' => 'Document  non trouvé.'], 404);
            }
            $document->is_blocked = false;
            $document->save();
            return response()->json(['data' => 'this document is unblock successfuly.'], 200);
    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }
}

        /**
     * Display a listing of the resource.
     */

         /**
   * @OA\Get(
   *     path="/api/document/document_actif",
   *     summary="Get all documents",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="List of inactif documents"
   *
   *     )
   * )
   */
  public function document_actif()
  {
      try {
          $documents = Document::where('is_deleted', false)
              ->where('is_actif', true)
              ->where('is_blocked', false)
              ->with('document_type_demande.type_demande')
              ->get();

          if ($documents->isEmpty()) {
              return response()->json(['error' => 'Document actif not found.'], 404);
          }

          $formattedDocuments = $documents->map(function ($document) {
              return [
                  'id' => $document->id,
                  'name' => $document->name,
                  'is_actif' => $document->is_actif,
                  'icone' => $document->icone,
                  'is_deleted' => $document->is_deleted,
                  'is_blocked' => $document->is_blocked,
                  'created_at' => $document->created_at,
                  'updated_at' => $document->updated_at,
                  'types_demandes' => $document->document_type_demande->map(function ($documentTypeDemande) {
                      return [
                          'id' => $documentTypeDemande->type_demande->id,
                          'name' => $documentTypeDemande->type_demande->name
                      ];
                  })->filter()
              ];
          });

          return response()->json([
              'data' => [
                  'documents' => $formattedDocuments,
                  'nombre' => $formattedDocuments->count()
              ]
          ], 200);
      } catch (Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
      }
  }


            /**
     * Display a listing of the resource.
     */

         /**
   * @OA\Get(
   *     path="/api/document/document_inactif",
   *     summary="Get all documents",
   *     tags={"Document"},
   * security={{"bearerAuth": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="List of actif documents"
   *
   *     )
   * )
   */

   public function document_inactif()
   {
       try {
           $documents = Document::where('is_deleted', false)
               ->where('is_actif', false)
               ->where('is_blocked', false)
               ->with('document_type_demande.type_demande')
               ->get();

           if ($documents->isEmpty()) {
               return response()->json(['error' => 'Document actif not found.'], 404);
           }

           $formattedDocuments = $documents->map(function ($document) {
               return [
                   'id' => $document->id,
                   'name' => $document->name,
                   'is_actif' => $document->is_actif,
                   'icone' => $document->icone,
                   'is_deleted' => $document->is_deleted,
                   'is_blocked' => $document->is_blocked,
                   'created_at' => $document->created_at,
                   'updated_at' => $document->updated_at,
                   'types_demandes' => $document->document_type_demande->map(function ($documentTypeDemande) {
                       return [
                           'id' => $documentTypeDemande->type_demande->id,
                           'name' => $documentTypeDemande->type_demande->name
                       ];
                   })->filter()
               ];
           });

           return response()->json([
               'data' => [
                   'documents' => $formattedDocuments,
                   'nombre' => $formattedDocuments->count()
               ]
           ], 200);
       } catch (Exception $e) {
           return response()->json(['error' => $e->getMessage()], 500);
       }
   }

  /**
* @OA\Put(
*     path="/api/document/active/{id}",
*     summary="active a document",
*     tags={"Document"},
*security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the document to active",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Document successfully active",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Document successfully active")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Document not found")
*         )
*     )
* )
*/

public function active($id)
{
    try{
        $document = Document::find($id);
        if (!$document) {
            return response()->json(['error' => 'Document  non trouvé.'], 404);
        }
        $document->is_actif = true;
        $document->save();
        return response()->json(['data' => 'this document is active successfuly.'], 200);
} catch(Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
}
}

/**
* @OA\Put(
*     path="/api/document/inactive/{id}",
*     summary="inactive a document",
*     tags={"Document"},
*security={{"bearerAuth": {}}},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the document to inactive",
*         required=true,
*         @OA\Schema(
*             type="integer",
*             format="int64"
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Document successfully inactive",
*         @OA\JsonContent(
*             @OA\Property(property="data", type="string", example="Document successfully inactive")
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="Document not found",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Document not found")
*         )
*     )
* )
*/

public function inactive($id)
{
try{
    $document = Document::find($id);
    if (!$document) {
        return response()->json(['error' => 'Document  non trouvé.'], 404);
    }
    $document->is_actif = false;
    $document->save();
    return response()->json(['data' => 'this document is inactive successfuly.'], 200);
} catch(Exception $e) {
  return response()->json(['error' => $e->getMessage()], 500);
}
}

}

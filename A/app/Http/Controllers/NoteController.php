<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
                $notes = Note::where('is_deleted', false)->get();
                return response()->json([
                    'data' => [
                        'message' => 'Notes saved successfully',
                        'notes' => $notes
                    ]
                    ]);
        }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
              $request->validate([
                'reservation_id' => 'required|exists:reservations,id',
                'criteria_id' => 'required|exists:criterias,id',
                'note' => 'required',
              ]);
              $note = new Note();
              $note->user_id = Auth::user()->id;
              $note->reservation_id = $request->reservation_id;
              $note->criteria_id = $request->criteria_id;
              $note->note = $request->note;
              $note->content = $request->content;
              $note->save();
              return response()->json([
                'data' =>['message' => 'saved successfully',
                'note' => $note]
              ]);
        }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
                $note = Note::find($id);
                if (!$note) {
                    return response()->json(['error' => 'Note non trouvé.'], 404);
                }
                return response()->json([
                    'data' => $note
                ]);
            }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
            try{
                $data = $request->validate([
                    'reservation_id' => 'required|exists:reservations,id',
                    'criteria_id' => 'required|exists:criterias,id',
                    'note' => 'required',
                    'content'
                  ]);
                  Note::whereId($id)->update($data);
            return response()->json(['data' => 'Note mise à jour avec succès.'], 200);
            }catch(ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $note = Note::find($id);
                if (!$note) {
                    return response()->json(['error' => 'Note non trouvé.'], 404);
                }
                $note->update(['is_deleted' => true]);
                return response()->json(['message' => 'note deleted successfully'], 404);
        }catch(ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->validator->errors()->first()
        ], 422);
    } catch(Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage()
        ], 500);
    }
    }
}

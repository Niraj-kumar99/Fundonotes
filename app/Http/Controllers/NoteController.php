<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Note;
use App\Models\User;
use Validator;
use JWTAuth;
use Auth;
use Exception;

class NoteController extends Controller
{
    /*Function creates Note */
    public function createNote(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:2,100',
            'description' => 'required|string|between:5,2000',
        ]);
        
        if($validator->fails())
        {
            Log::info('minimun letters for title is 2 and for description is 5');
            return response()->json($validator->errors()->toJson(), 400);
        }

        $note = new Note;
        $note->title = $request->input('title');
        $note->description = $request->input('description');
        $note->user_id = Auth::user()->id;
        $note->save();

        $value = Cache::remember('notes', 300, function () {
            return DB::table('notes')->get();
        });

        Log::info('note created',['user_id'=>$note->user_id]);
        return response()->json([ 
            'message' => 'note created successfully'
            ],201);
            
    }

    public function readNote_ByNoteId(Request $request) {
        
        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $notes = $currentUser->notes()->find($id);
        if(!$notes){
            Log::info('note you are searching is not present..');
            return response()->json([
                'status' => 201, 
                'message' => 'no note found'
                ],400);
        } 
    Log::info("note fetched",['user_id'=>$currentUser,'note_id'=>$request->id]);    
    return $notes;

    }

    public function updateNote_ByNote(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'title' => 'required|string|between:2,100',
            'description' => 'required|string|between:5,2000',
        ]);
        if($validator->fails())
        {
            Log::info('minimun letters for title is 2 and for description is 5 and noteId req');
            return response()->json($validator->errors()->toJson(), 404);
        }

        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $notes = $currentUser->notes()->find($id);

        if(!$notes){
            Log::info('note you are searching is not present..');
            return response()->json([
                'status' => 201, 
                'message' => 'no note found'
                ],400);
        } 
        $notes->fill($request->all());

        if($notes->save()) {
            Log::info('notes updated',['user_id'=>$currentUser,'note_id'=>$request->id]);
            return response()->json([
                'message' => 'Updation done'
            ],201);
        }

    }

    public function delete_ByNote(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails())
        {
            Log::info('noteId is a required field');
            return response()->json($validator->errors()->toJson(), 404);
        }
        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $notes = $currentUser->notes()->find($id);

        if(!$notes){
            Log::info('note you are searching is not present for deletion..');
            return response()->json([
                'status' => 201, 
                'message' => 'no note found'
                ],400);
        } 
        if($notes->delete()) {
            Log::info('notes deleted',['user_id'=>$currentUser,'note_id'=>$request->id]);
            return response()->json([
                'message' => 'Note deleted'
            ],201);
        }
    }

    public function allNotes()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if ($currentUser)
        {
            $user = Note::select('notes.id', 'notes.title', 'notes.description')
            ->where('notes.user_id','=',$currentUser->id)
            ->get();
        }
        if ($user=='[]')
            {
                return response()->json(['message' => 'Notes not found'], 404);
            }
            Log::info('Lables fetched',['notes.id'=>$currentUser->id]);
            return response()->json([
                'notes' => $user,
                'message' => 'Fetched Notes Successfully'
            ], 201);
        }
    }




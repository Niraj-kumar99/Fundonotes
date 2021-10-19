<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\User;
use Validator;
use JWTAuth;
use Auth;

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
            return response()->json($validator->errors()->toJson(), 400);
        }

        $note = new Note;
        $note->title = $request->input('title');
        $note->description = $request->input('description');
        $note->user_id = Auth::user()->id;
        $note->save();

        return response()->json([ 
            'message' => 'note created successfully'
            ],201);
            
    }

    public function readNote_ByNoteId(Request $request) {
        
        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $notes = $currentUser->notes()->find($id);
        if(!$notes){
            return response()->json([
                'status' => 201, 
                'message' => 'no note found'
                ],400);
        } 

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
            return response()->json($validator->errors()->toJson(), 404);
        }

        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $notes = $currentUser->notes()->find($id);

        if(!$notes){
            return response()->json([
                'status' => 201, 
                'message' => 'no note found'
                ],400);
        } 
        $notes->fill($request->all());

        if($notes->save()) {
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
            return response()->json($validator->errors()->toJson(), 404);
        }
        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $notes = $currentUser->notes()->find($id);

        if(!$notes){
            return response()->json([
                'status' => 201, 
                'message' => 'no note found'
                ],400);
        } 
        if($notes->delete()) {
            return response()->json([
                'message' => 'Note deleted'
            ],201);
        }
    }

    public function allNotes()
    {
        $note = new Note;
        $note->user_id = auth()->id();

        if ($note->user_id == auth()->id()) 
        {
            $user = Note::select('id', 'title', 'description')
                ->where([
                    ['user_id', '=', $note->user_id],
                    ['notes', '=', '0']
                ])
                ->get();
            if ($user=='[]'){
                return response()->json([
                    'message' => 'Notes not found'
                ], 404);
            }
            return
            response()->json([
                'notes' => $user,
                'message' => 'Fetched Notes Successfully'
            ], 201);
        }
        return response()->json([
            'status' => 403, 
            'message' => 'Invalid token'
        ],403);
    }

}

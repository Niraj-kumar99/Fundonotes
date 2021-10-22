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
    /*
     * Function creates Note with  
     * proper title and description and user bearer token
     * genereted in postman .
    */
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
        try
        {
            $note = new Note;
            $note->title = $request->input('title');
            $note->description = $request->input('description');
            $note->user_id = Auth::user()->id;
            $note->save();

            $value = Cache::remember('notes', 300, function () {
                return DB::table('notes')->get();
        });
        }
        catch(FailCreationException $e)
        {
            return back()->withErrors("Invalid Validation");
        }   

        Log::info('note created',['user_id'=>$note->user_id]);
        return response()->json([ 
        'message' => 'note created successfully'
        ],201);
            
    }

    /* 
     *Function fetch the created note from DB
     *by giving valid note id and proper Authentication token
    */
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

    /*
     *Function takes valid 
     *Note_id , Updated_title , updated_description
     *and fetches the old note and updates with new title and description
    */
    public function updateNote_ByNoteId(Request $request) {
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

        try 
        {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $notes = $currentUser->notes()->find($id);

            if(!$notes)
            {
                Log::info('note you are searching is not present..');
                return response()->json([
                    'status' => 201,
                    'message' => 'no note found'
                ],400);
            }
            $notes->fill($request->all());

            if($notes->save())
            {
                Log::info('notes updated',['user_id'=>$currentUser,'note_id'=>$request->id]);
                return response()->json([
                    'message' => 'Updation done'
                ],201);
            }
        }
        catch(InvalidUserException $e)
        {
            return response()->json([
                'message' => 'User token provided is invalid'
            ],404);
        }
    }

    /*
     *Function takes perticular note_id and a 
     *valid Authentication token as an input and fetch the note 
     *an performs delete operation on that perticular note .
    */
    public function delete_ByNote(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails())
        {
            Log::info('noteId is a required field');
            return response()->json($validator->errors()->toJson(), 404);
        }

        try 
        {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $notes = $currentUser->notes()->find($id);

            if(!$notes)
            {
                Log::info('note you are searching is not present for deletion..');
                return response()->json([
                    'message' => 'no note found'
                ],400);
            }

            if($notes->delete())
            {
                Log::info('notes deleted',['user_id'=>$currentUser,'note_id'=>$request->id]);
                return response()->json([
                    'message' => 'Note deleted'
                ],201);
            }
        }
        catch(UnAuthoriseBearerToken $e)
        {
            return response()->json([
                'message' => 'Provided Beare token is invalid'
            ],404);
        }
    }
    /*
     *Function returns all the created notes of a perticular 
     *user bearing a vaild authentication token 
    */
    public function allNotes()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        try
        {
            if ($currentUser)
            {
                $user = Note::select('notes.id', 'notes.title', 'notes.description')
                ->where('notes.user_id','=',$currentUser->id)
                ->get();
            }
        }
        catch(InvalidAuthenticationException $e)
        {
            if ($user=='[]')
            {
                return response()->json([
                    'message' => 'Notes not found'
                ], 404);
            }
        }
            Log::info('Lables fetched',['notes.id'=>$currentUser->id]);
            return response()->json([
                'notes' => $user,
                'message' => 'Fetched Notes Successfully'
            ], 201);
    }
    

    /* 
     *Function takes perticular note_id and a valid
     *Authentication token as an input and if its a valid note_id
     *then pins the note .
    */
    public function pinNoteWithNoteId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $currentUser = JWTAuth::parseToken()->authenticate();
        $id = $request->input('id');
        $notes = $currentUser->notes()->find($id);

        if($notes->pin == null)
        {
            Note::where('id' , $request->id)
            ->update(['pin'=>1]);
            Log::info('successfully pined : ',['user'=>$currentUser, 'id'=>$request->id]);
            return response()->json(['message'=>'Note Pined']);
        }
        return response()->json(['message'=>'Note already Pined']);

    }


    /**
     *Function takes perticular note_id and a valid
     *Authentication token as an input and if its a valid note_id and 
     *the note is a pinned note then this function updates it from pin to 
     *unpin note.
    */
    public function unpinNoteWithNoteId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $currentUser = JWTAuth::parseToken()->authenticate();
        $id = $request->input('id');
        $notes = $currentUser->notes()->find($id);

        if($notes->pin == 1)
        {
            Note::where('id' , $request->id)
            ->update(['pin'=>null]);
            Log::info('successfully Unpined : ',['user'=>$currentUser, 'id'=>$request->id]);
            return response()->json(['message'=>'Note UnPined']);
        }
        return response()->json(['message'=>'Note is not pined yet']);
    }


    /* 
     *Function returns all the pinned notes of a perticular 
     *user bearing a vaild authentication token
    */
    public function allPinNotes()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if ($currentUser)
        {
            $user = Note::select('notes.id', 'notes.title', 'notes.description')
                ->where([['notes.user_id','=',$currentUser->id], ['pin','=', 1]])
                ->get();
        }
        if($user=='[]')
        {
            Log::info('There are no pin notes created by this user',['user'=>$currentUser]);
            return response()->json(['message' => 'Pin notes not found for this user'], 404);
        }
        return response()->json([
            'lables' => $user,
            'message' => 'All pined notes are Fetched.....'
        ], 201);
    }


    /**
     *Function takes perticular note_id and a valid
     *Authentication token as an input and if its a valid note_id then
     *function updates note's achive status from Null to 1 .
     */
    public function archiveNoteWithNoteId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $currentUser = JWTAuth::parseToken()->authenticate();
        $id = $request->input('id');
        $notes = $currentUser->notes()->find($id);

        if(!$notes)
        {
            return response()->json([
                'message' => 'note you are searching not available'
            ],404);
        }
        if($notes->archive == null)
        {
            Note::where('id' , $request->id)
            ->update(['archive'=>1]);

            Log::info('successfully archived : ',['user'=>$currentUser, 'id'=>$request->id]);
            return response()->json(['message'=>'Note Archived']);
        }
        return response()->json(['message'=>'Note already Archived']);
    }


    /**
     *Function takes perticular note_id and a valid
     *Authentication token as an input and if its a valid note_id and 
     *the note is a archived note then this function updates it from archived to 
     *unarchived note by changing its status from 1 to Null again.
    */
    public function unarchiveNoteWithNoteId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $currentUser = JWTAuth::parseToken()->authenticate();
        $id = $request->input('id');
        $notes = $currentUser->notes()->find($id);

        if($notes->archive == 1)
        {
            Note::where('id' , $request->id)
            ->update(['archive'=>null]);
            Log::info('successfully Unarchived : ',['user'=>$currentUser, 'id'=>$request->id]);
            return response()->json(['message'=>'Note UnArchived']);
        }
        return response()->json(['message'=>'Note is not archived yet']);
    }


    /* 
     *Function returns all the created archives of a perticular 
     *user bearing a vaild authentication token
    */
    public function allArchivedNotes()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if ($currentUser)
        {
            $user = Note::select('notes.id', 'notes.title', 'notes.description')
                ->where([['notes.user_id','=',$currentUser->id], ['archive','=', 1]])
                ->get();
        }
        if($user=='[]')
        {
            Log::info('There are no archive notes created by this user',['user'=>$currentUser]);
            return response()->json(['message' => 'archive notes not found for this user'], 404);
        }
        return response()->json([
            'lables' => $user,
            'message' => 'All archived notes are Fetched.....'
        ], 201);
        
    }
}





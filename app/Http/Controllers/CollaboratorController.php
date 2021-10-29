<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SendEmailRequest;
use Validator;
use App\Models\User;
use App\Models\Note;


class CollaboratorController extends Controller
{
    /**
     * Function takes note_id and email and checks for validation
     * if it's valid then sends the mentioned note_it to the email 
     * which is provided .
     */
    public function sendMailToCollaboratorWithNoteId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note_id' => 'required',
            'email' => 'required|string|email|max:150',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $currentUser = JWTAuth::parseToken()->authenticate();
        $user = User::where('email', $request->email)->first();
        $user_name = User::select('firstname','lastname')
                    ->where([['email','=',$request->email]])
                    ->get();  //------
        $note = $currentUser->notes()->find($request->input('note_id'));

        if($currentUser)
        {
            if($user)
            {
                if($note)
                {
                    $collabedUser = Collaborator::select('id')
                    ->where([['note_id','=',$request->input('note_id')],
                    ['email','=',$request->input('note_id')] ])
                    ->get();

                    if($collabedUser != '[]')
                    {
                        return response()->json(['message' => 'Collabarater Already Created' ], 404); 
                    }
                    try
                    {
                        $collab = new Collaborator;
                        $collab->note_id = $request->get('note_id');
                        $collab->email = $request->get('email');
            
                        $collaborator = Note::select('id','title','description')
                        ->where([['id','=',$request->note_id]])
                        ->get();

                        if($currentUser->collaborators()->save($collab))
                        {
                            $sendEmail = new SendEmailRequest();
                            $sendEmail->sendEmailToCollab($request->email,$collaborator,$currentUser->firstname,$currentUser->lastname,$currentUser->email); //---------

                            Log::info('note shared with this Collaborator',['collaborator_mail'=>$request->email , 'note shared'=>$request->note_id]);
                            return response()->json(['message' => 'Note shared with Collaborator'], 201);
                        }
                    }
                    catch(FundonotesException $e)
                    {
                        Log::info('Mail sharing failed..');
                        return response()->json([
                            'message' => ' sharing to collaborator failed  ...'
                        ],404);
                    }
                }
                return response()->json([
                    'message' => 'Notes not found'
                ], 404);
            }
            return response()->json([
                'message' => 'User Email provided is not Registered'
            ], 404);
        }
        return response()->json([
            'message' => 'User Bearer Token provided is not Valid'
        ], 404);
    }


    /**
     * Function takes valid bearer token and if its a valid one
     * then takes the required credentials ie;note_id , title
     * and description and updates the note .
     */
    public function updateOnNoteByCollaborator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note_id' => 'required',
            'title' => 'string|between:2,200',
            'description' => 'string|between:3,1000',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $currentUser = JWTAuth::parseToken()->authenticate();
        $id = $request->input('note_id');

        if($currentUser)
        {
            $user = Collaborator::where('email', $currentUser->email)->first();

            if($user)
            {
                $id = $request->input('note_id');
                $email = $currentUser->email;

                $collab = Collaborator::select('id')
                ->where([['note_id','=',$id],
                        ['email','=',$email]])
                ->get();

                if($collab == '[]')
                {
                    return response()->json(['message' => 'note_id is WRONG....'], 404); 
                }
                $user = Note::where('id', $request->note_id)
                        ->update(['title' => $request->title,'description'=>$request->description]);

                if($user)
                {
                    return response()->json(['message' => 'Updation on note done...' ], 201);
                }
                return response()->json(['message' => 'Updation on Note failed' ], 201);
            }
            return response()->json(['message' => 'Collaborator Email not registered' ], 404);
        }
        
    }


    /**
     * Function takes valid bearer token and if it's a valid
     * one then asks note_id and collaborator's email if the credentials
     * are valid then removes that perticular note from table .
     */
    public function removeCollaborator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note_id' => 'required',
            'email' => 'required|string|email|max:150'
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $currentUser = JWTAuth::parseToken()->authenticate();

        try
        {
            if ($currentUser)
            {
                $id = $request->input('note_id');
                $email =  $request->input('email');

                $collaborator = Collaborator::select('id')
                    ->where([['note_id','=',$id],
                            ['email','=',$email]])
                    ->get();

                if($collaborator == '[]')
                {
                    return response()->json(['message' => 'Collabarater Not created' ], 404);
                }
                $collabDelete = DB::table('collaborators')->where('note_id', '=', $id)->where('email', '=', $email)->delete();
                if($collabDelete)
                {
                    return response()->json(['message' => 'Collaborator Removed' ], 201);
                }
            }
        }
        catch(FundonotesException $e)
        {
            return response()->json([
                'message' => 'Collaborator could not removed' 
            ], 201);
        }
    }


    public function allSharedNotesToCollaborators()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        if ($currentUser)
        {
            $user = Collaborator::select('note_id', 'email')
                ->where([['user_id','=',$currentUser->id]])
                ->get();

            if($user=='[]')
            {
                Log::info('There are no shared notes with this Collaborator',['user'=>$currentUser]);
                return response()->json([
                    'message' => 'Pin notes not found for this user'
                ], 404);
            }
        }
        Log::info('Fetching all note shared Collaborators');
        return response()->json([
            'Collaborator' => $user,
            'message' => 'All Collaborators are Fetched.....'
        ], 201);
    }
}

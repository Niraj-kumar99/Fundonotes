<?php

namespace App\Http\Controllers;

use App\Models\Lable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;
use JWTAuth;


class LableController extends Controller
{
    /*
     *Function creates a lable to a perticular note
     *by getting valid note_id , lable_name and a 
     *valid authentication token from the user 
    */
    public function createLableWithNoteID(Request $request) {

        $validator = Validator::make($request->all(), [
            'note_id' => 'required',
            'lable_name' => 'required|string|between:2,100',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $lables = new Lable;
        $lables->note_id = $request->get('note_id');
        $lables->lable_name = $request->get('lable_name');
        $currentUser = JWTAuth::parseToken()->authenticate();

        if($currentUser->lables()->save($lables)) {
            return response()->json([
                'status' => 201, 
                'message' => 'lable created successfully'
                ],201);
        }
    }
    /*
    *Function creates a lable without getting a noteid
     *user only have to provide a proper lable_name ie; min 2 and max 100
     *and a valid authentication token .
    */
    public function creatLableWithoutId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lable_name' => 'required|string|between:2,100',
        ]);

        if($validator->fails())
        {
            Log::info('lable_name is mandatory');
            return response()->json($validator->errors()->toJson(), 400);
        }

        $currentUser = JWTAuth::parseToken()->authenticate();
        if($currentUser)
        {
            $lable = Lable::where('lable_name', '=', $request->input('lable_name'))->first();
            if($lable)
            {
                return response()->json(['message' => 'This lable exist already'],401);
            }
            $lables = new Lable;
            $lables->lable_name = $request->get('lable_name');
            if($currentUser->lables()->save($lables))
            {
                return response()->json([ 
                'message' => 'lable created successfully'
                ],201);
            }
        }
    }
    /*
     *Function fetch the lable details of a perticular lable 
     *by taking a valid lable_id and proper authentication token
     * from the user and return deltails of that lable .
    */
    public function readLableByLableId(Request $request) {
        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $lables = $currentUser->lables()->find($id);
        if($lables == ''){
            return response()->json([ 
                'message' => 'no lables found'
                ],400);
        }
        return $lables;
    }

    /* 
     *Function takes valid new lable_id and new lable_name from the user
     *and fetches the old lable and updates with new lable_name
    */
    public function updateLableByLableId(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'lable_name' =>'required|string|between:2,100',
        ]);
        if($validator->fails())
        {
            Log::info('LableId and lable name are mandatory');
            return response()->json($validator->errors()->toJson(), 400);
        }
        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $lable = $currentUser->lables()->find($id);
        if(!$lable){
            Log::error('Unable to find searched lable',['label_id'=>$request->id]);
            return response()->json([ 
                'message' => 'no lable found'
                ],400);
        }
        $lable->fill($request->all());
        if($lable->save()) {
            Log::info('Label updation done',['label_id'=>$request->id]);
            return response()->json([
                'message' => 'Lable Updated'
            ],201);
        }
    }
    /*
     *Function takes perticular lable_id and a valid
     *Authentication token as an input and fetch the old lable
     *and performs delete operation .
    */
    public function deleteLable(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails())
        {
            Log::info('lableId is required field');
            return response()->json($validator->errors()->toJson(), 404);
        }
        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $lable = $currentUser->lables()->find($id);
        if(!$lable){
            Log::info('lable you are searching is not present for deletion..');
            return response()->json([
                'status' => 201, 
                'message' => 'lable not found'
                ],400);
        } 
        if($lable->delete()) {
            Log::info('Label deleted',['label_id'=>$request->id]);
            return response()->json([
                'message' => 'Lable deleted'
            ],201);
        }
    }
    /* 
     *Function returns all the created lables of a perticular 
     *user bearing a vaild authentication token
    */
    public function fetchAllLables()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if ($currentUser)
        {
            $user = Lable::select('lables.id', 'lables.user_id', 'lables.lable_name')
            ->where('lables.user_id','=',$currentUser->id)
            ->get();
        }
        if ($user=='[]')
        {
            Log::info('There are no lables for this user');
            return response()->json(['message' => 'Lables not found'], 404);
        }
        Log::info('Lables fetched',['lables.user_id'=>$currentUser->id]);
        return response()->json([
            'lables' => $user,
            'message' => 'Lables Fetched.....'
        ], 201);
    }
}

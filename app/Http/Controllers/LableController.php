<?php

namespace App\Http\Controllers;

use App\Models\Lable;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;


class LableController extends Controller
{
    public function createLable(Request $request) {
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
                ],400);
        }
    }

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

    public function updateLableByLableId(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'lable_name' =>'required|string|between:2,100',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $lable = $currentUser->lables()->find($id);
        if(!$lable){
            return response()->json([ 
                'message' => 'no lable found'
                ],400);
        }
        $lable->fill($request->all());
        if($lable->save()) {
            return response()->json([
                'message' => 'Lable Updated'
            ],201);
        }
    }

    public function deleteLable(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 404);
        }
        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $lable = $currentUser->lables()->find($id);
        if(!$lable){
            return response()->json([
                'status' => 201, 
                'message' => 'lable not found'
                ],400);
        } 
        if($lable->delete()) {
            return response()->json([
                'message' => 'Lable deleted'
            ],201);
        }
    }
}

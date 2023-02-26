<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if($validator->fails())
            return $this->sendError('Validation Error.', $validator->errors());
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);

        $res['token'] =  $user->createToken('BrightView')->plainTextToken;
        $res['name'] =  $user->name;

        return $this->sendResponse($res, 'User register successfully.');
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails())
            return $this->sendError('Validation Error.', $validator->errors());

        if(! Auth::attempt(['email' => $request->email, 'password' => $request->password]))
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);

        $user = Auth::user(); 

        $res['token'] =  $user->createToken('BrightView')->plainTextToken; 
        $res['name'] =  $user->name;

        return $this->sendResponse($res, 'User login successfully.');
    }
}

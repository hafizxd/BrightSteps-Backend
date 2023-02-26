<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
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
            return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);

        $user = Auth::user(); 
        $token = $user->createToken('BrightView');

        $res['token'] =  $token->plainTextToken; 
        $res['name'] =  $user->name;

        $user->tokens()->where('id', '!=', $token->accessToken->id)->delete();

        return $this->sendResponse($res, 'User login successfully.');
    }
}

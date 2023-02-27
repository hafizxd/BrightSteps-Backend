<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Constants\AccountType;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);

        if($validator->fails())
            return $this->sendError('Validation Error.', $validator->errors());
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['account_type'] = AccountType::getId('parent');

        $user = User::create($input);

        $res['token'] =  $user->createToken(env('APP_NAME'))->plainTextToken;
        $res['user'] =  $user;

        return $this->sendResponse($res, 'User register successfully.');
    }

    public function login(Request $request, $accountType) {
        if (! in_array($accountType, AccountType::rules()))
            abort(404);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails())
            return $this->sendError('Validation Error.', $validator->errors());

        $attempt = Auth::attempt([
            'email' => $request->email, 
            'password' => $request->password,
            'account_type' => AccountType::getId($accountType)
        ]);
        
        if(! $attempt)
            return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);

        $user = Auth::user(); 
        $token = $user->createToken(env('APP_NAME'));

        $res['token'] =  $token->plainTextToken; 
        $res['user'] =  $user;

        $user->tokens()->where('id', '!=', $token->accessToken->id)->delete();

        return $this->sendResponse($res, 'User login successfully.');
    }

    public function socialLogin(Request $request, $providerType)
    {
        if (!in_array($providerType, ['google']))
            abort(404);

        $validator = Validator::make($request->all(), [ 'access_token' => 'required' ]);
        if($validator->fails())
            return $this->sendError('Validation Error.', $validator->errors());

        $providerUser = Socialite::driver($providerType)->userFromToken($request->access_token);

        $user = User::whereHas('providers', function($q) use ($providerType, $providerUser) {
            $q->where('provider_name', $providerType)
                ->where('provider_id', $providerUser->id);
        })->first(); 

        if(! isset($user)){
            dd($providerUser);

            $user = User::create([
                'name' => null,
                'email' => null,
                'password' => null
            ]);

            $user->providers()->create([
                'provider_name' => $providerType,
                'provider_id' => $providerUser->id,
            ]);
        }

        $res['token'] = $user->createToken(env('APP_NAME'))->plainTextToken;
        $res['user'] =  $user;

        return $this->sendResponse($res, 'User login successfully.');
    }

    public function logout() {
        Auth::user()->tokens()->delete();

        return $this->sendResponse([], 'User logout successfully.');
    }
}

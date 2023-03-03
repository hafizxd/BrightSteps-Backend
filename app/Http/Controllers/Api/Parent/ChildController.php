<?php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Constants\AccountType;
use Illuminate\Http\Request;
use Validator;

class ChildController extends Controller
{
    public function store(Request $request) {
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
        $input['account_type'] = AccountType::CHILD;

        $user = Auth::user()->children()->create($input);

        $res['child'] =  $user;

        return $this->sendResponse($res, 'Child register successfully.');
    }
}

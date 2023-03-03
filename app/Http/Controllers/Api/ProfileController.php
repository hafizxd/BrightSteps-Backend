<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class ProfileController extends Controller
{
    public function index() {
        $res['user'] = Auth::user();

        return $this->sendResponse($res, 'Berhasil mengambil data profile');
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
        ]);

        if($validator->fails())
            return $this->sendError('Validation Error.', $validator->errors());

        auth()->user()->update([
            'name' => $request->name,
            'email' => $request->email,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender
        ]);

        return $this->sendResponse([], 'Berhasil mengupdate profile');
    }
}

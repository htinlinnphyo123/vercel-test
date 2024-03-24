<?php

namespace App\Http\Controllers\api;

use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
	/**
	 * @param Request $request
	 * @return array|JsonResponse
	 */
    public function register(Request $request) : array|JsonResponse
	{
		$validator = Validator::make($request->all(), [
			'username' => 'required|min:4|max:100|unique:users,username',
			'password' => 'required'
		]);

		// If validation fails, return response with validation errors
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}
		$user = User::create([
			'id' => GeneralHelper::generateRandomString(20),
			'username' => $request->username,
			'password' => $request->password,
		]);
		return ['token'=>$user->createToken($request->username)->plainTextToken];
	}

	public function login(Request $request) : array|JsonResponse
	{
		$validator = Validator::make($request->all(), [
			'username' => 'required|min:4|max:100',
			'password' => 'required'
		]);
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}
		$user = User::where('username',$request->username)->first();
		if(!$user){
			return response()->json(['message'=>'There is no username with this account.Please register first.'],422);
		}

		if (!Hash::check($request->password, $user->password)) {
			return response()->json(['message'=>'Password wrong.Please try again.'],422);
		}

		return ['token'=>$user->createToken($user->username)->plainTextToken];
	}


}

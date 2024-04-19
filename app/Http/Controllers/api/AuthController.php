<?php

namespace App\Http\Controllers\api;

use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
		try{
			$validator = Validator::make($request->all(), [
				'username' => 'required|min:4|max:100|unique:users,username',
				'password' => 'required'
			]);

			// If validation fails, return response with validation errors
			if ($validator->fails()) {
				return response()->json([
					'code'	=> 422,
					'message' => $validator->getMessageBag()->first()
				],422);
			}
			$user = User::create([
				'id' => GeneralHelper::generateRandomString(20),
				'username' => $request->username,
				'password' => $request->password,
			]);

			$token = $user->createToken($user->username)->plainTextToken;
			$arr = explode('|', $token);
			$getToken = end($arr);

			return response()->json([
				'code' => 201,
				'message' => 'User created successfully',
				'data' => [
					'user' => $user,
					'token' => $getToken
				]
			], 201);
		}catch (\Exception $e){
			return response()->json([
				'code' => 500,
				'error' => $e->getMessage()
			], 500);
		}
	}

	public function login(Request $request) : array|JsonResponse
	{
		try{
			$validator = Validator::make($request->all(), [
				'username' => 'required|min:4|max:100',
				'password' => 'required'
			]);
			if ($validator->fails()) {
				return response()->json([
					'code'	=> 422,
					'message' => $validator->getMessageBag()->first()
					 ],422);
			}
			$user = User::where('username', $request->username)->first();
			if (!$user) {
				return response()->json([
					'code' => 422,
					'message' => 'There is no username with this account.Please register first.'
				], 422);
			}

			if (!Hash::check($request->password, $user->password)) {
				return response()->json([
					'code' => 401,
					'message' => 'Password wrong.Please try again.'
				], 401);
			}

			$token = $user->createToken($user->username)->plainTextToken;
			$arr = explode('|', $token);
			$getToken = end($arr);

			return response()->json([
				'code' => 201,
				'message' => 'User login successfully',
				'data' => [
					'user' => $user,
					'token' => $getToken
				]
			], 201);
		}catch (\Exception $e){
			return response()->json([
				'code' => 500,
				'error' => $e->getMessage()
			], 500);
		}
	}

	public function logout(Request $request) : array|JsonResponse
	{
		$tokenId = $request->user()->currentAccessToken()->id;
		// Revoke the specific token by its ID
		Auth::user()->tokens()->where('id', $tokenId)->delete();
		return response()->json(['code' => 200, 'message' => 'User logged out successfully.']);
	}

}

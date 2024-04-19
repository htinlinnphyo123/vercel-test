<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\DonorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json(['code'=>200,'user'=>$request->user()]);
});

Route::group(['middleware'=>'auth:sanctum'],function(){
	//Logout
	Route::post('logout',[AuthController::class,'logout']);

	//Donor
	Route::get('donors',[DonorController::class,'index']);
	Route::get('donors/{donor}',[DonorController::class,'show']);
	Route::post('donors',[DonorController::class,'store']);
	Route::put('donors/{donor}',[DonorController::class,'store']);
	Route::delete('donors/{donor}',[DonorController::class,'destroy']);
	Route::delete('donors/{donor}/delete-profile',[DonorController::class,'destroyProfile']);
});

Route::post('/post',function(Request $request){
	try{
		\App\Helpers\Tebi::upload($request->file);
	}catch (Exception $e){
		return $e->getMessage();
	}
});

Route::get('hello',function(){
    return 'api hello';
});

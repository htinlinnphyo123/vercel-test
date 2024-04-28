<?php

namespace App\Http\Controllers;

use App\Helpers\Tebi;
use App\Models\Donor;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DonorController extends Controller
{
	public function index() : JsonResponse
	{
		$user = Auth::user();
		$donors = Donor::where('created_by',$user->id)
			->when(request('can_donate'),function($query){
				return $query->whereDate('last_donated','<=',now()->subDay(90));
			})
			->when(request('is_available'),function($query){
				return $query->where('is_available',true);
			})
			->when(request('blood_type'),function($query){
				return $query->where('blood_type',request('blood_type'));
			})
			->when(request('gender'),function($query){
				return $query->where('gender',request('gender'));
			})
			->when(request('name'),function($query){
				return $query->where('name','like','%'.request('name').'%');
			})
			->when(request('phone'),function($query){
				$phoneNumber = request('phone');
				return $query->where(function($query) use($phoneNumber){
					$query->where('ph_work','like','%'.$phoneNumber.'%')
						->orWhere('ph_home','like','%'.$phoneNumber.'%');
				});
			})
			->get();
		return response()->json(['code'=>200,'donors'=>$donors],200);
	}
    public function store(Request $request) : JsonResponse
	{
		try{
			$validator = $this->validator($request);
			if ($validator->fails()) {
				$message = $validator->getMessageBag()->first();
				return response()->json([
					'code'=>422,
					'message'=>$message,
					], 422);
			}
			$status = '';
			$birthdayDate = new Datetime($request->dob);
			$currentDate = new DateTime();
			$getDiff = $currentDate->diff($birthdayDate)->y;
			if($getDiff<18){
				return response()->json(['code'=>422,'message'=>'This user is under 18 and he is not ready to donate blood.'],422);
			}
			$donor = $request->donor_id;
			if($donor){
				$donor = Donor::find($donor);
				if($donor===null){
					return response()->json(['code'=>422,'message'=>'Donor not found.'],404);
				}
				$status = 'Donor Updated Successfully';
				if($donor->avatar && $request->avatar){
					Tebi::delete($donor->avatar);
				}
			}else{
				$donor = new Donor();
				$donor->id  = uniqid('uid_');
				$status = 'Donor Created Successfully';
			}

			$donor->name = $request->name;
			$donor->dob = $request->dob;
			$donor->ph_home = $request->ph_home;
			$donor->ph_work = $request->ph_work;
			$donor->address = $request->address;
			$donor->gender = $request->gender;
			$donor->blood_type = $request->blood_type;
			$donor->is_available = $request->is_available;
			$donor->donated_frequency = $request->donated_frequency;
			$donor->last_donated = $request->last_donated;
			$donor->created_by = Auth::id();
			if ($request->avatar) {
				$avatar = Tebi::upload($request->avatar,path:'/avatar');
				if($avatar){
					$donor->avatar = $avatar;
				}else{
					return response()->json(['code'=>500,'message'=>'There is a problem uploading your avatar'],500);
				}
			}
			$donor->save();
			return response()->json([
				'code'=>200,
				'status'=> $status,
				'data' => [
				    'donor'=>$donor
				]
			],200);
		}catch (\Exception $e){
			return response()->json(['code'=>500,'message'=>$e->getMessage()],500);
		}

	}

	public function show(Request $request) : JsonResponse
	{
		try{
			$donor = Donor::find($request->id);
			if($donor===null){
				return response()->json(['code'=>404,'message'=>'Donor not found.'],404);
			}
			return response()->json([
				'code'=>200,
				'data' => [
					'donor'=>$donor
				]
				],200);
		}catch (\Exception $e){
			return response()->json(['message'=>$e->getMessage()],500);
		}

	}

	public static function destroy($donor) : JsonResponse
	{
		try{
			$donor = Donor::find($donor);
			if ($donor->avatar) {
				Tebi::delete($donor->avatar);
			}
			$donor->delete();
			return response()->json([
				'code' => 200,
				'message' => 'Donor Deleted Successfully'
			], 200);
		}catch (\Exception $e){
			return response()->json([
				'code' => 500,
				'message'=>$e->getMessage()
			],500);
		}
	}

	public function destroyProfile($donor) : JsonResponse
	{
		try{
			$donor = Donor::find($donor);
			if ($donor->avatar) {
				Tebi::delete($donor->avatar);
				$donor->avatar = null;
			}
			$donor->save();
			return response()->json([
				'code' => 200,
				'message' => 'Donor Profile Deleted Successfully'
			]);
		}catch (\Exception $e){
			return response()->json([
				'code'=>500,
				'message'=>$e->getMessage()
			],500);
		}
	}


	/**
	 * @param $request
	 * @return \Illuminate\Validation\Validator
	 */
	protected function validator($request) : \Illuminate\Validation\Validator
	{
		return Validator::make($request->all(),[
			'name' => 'required|min:5|max:50',
			'avatar' => 'nullable|image',
			'dob' => 'required|date',
			'ph_home' => 'required|numeric',
			'gender' => 'required',
			'blood_type' => 'required'
 		]);
	}

}

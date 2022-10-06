<?php

namespace App\Http\Controllers;

use App\Models\AgentStock;
use App\Models\Fruit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    public function register(Request $request)
    {
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('username', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'username' => 'required|string',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()], 200);
        }

        //Request is validated
        //Crean token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 200);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 200);
        }

        try {
            $user = Auth::user();
            // return $user;
            if ($user->status == 0) {
                $this->invalidToken();
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been suspended, kindly contact admin for more information.',
                ], 200);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended, kindly contact admin for more information.',
            ], 200);
        }

        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
            'username' => $user->username,
        ]);
    }

    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user]);
    }

    public function get_fruits(Request $request)
    {
        try {
            $user = Auth::user();
            // return $user;
            if ($user->status == 0) {
                $this->invalidToken();
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been suspended, kindly contact admin for more information.',
                ], 200);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended, kindly contact admin for more information.',
            ], 200);
        }
        $user = auth()->user();
        $agent = $user->agent->id;
        $fruits = AgentStock::with('fruit:id,name,image,sales_price')->where('agent_id', $agent)->where('status', 1)->get();
        foreach ($fruits as $data) {
            $data->fruit->stock = $data->stock_pack;
        }
        $fruits = $fruits->pluck('fruit')->sortBy('name')->values()->all();
        // $fruits = AgentStock::with('fruit:id,name,image,sales_price')->where('agent_id', $agent)->where('status', 1)->get()->pluck('fruit', 'stock_pack')->sortBy('name')->values()->all();
        // $fruits = $fruits->orderBy('name','asc');
        // $fruits = Fruit::select('id','name','image','sales_price')->where('status', 1)->orderBy('name','asc')->get();

        return response()->json(['success' => true, 'fruits' => $fruits]);
    }

    public static function invalidToken()
    {
        $current_token = JWTAuth::getToken();
        JWTAuth::setToken($current_token)->invalidate();
    }
}

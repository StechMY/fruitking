<?php

namespace App\Http\Controllers;

use App\Models\SalesRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class SalesRecordController extends Controller
{

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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

        $sales = $this->user->sales()->get(array(
            DB::raw('products'),
            DB::raw('total_sales'),
            DB::raw('total_commission'),
            DB::raw('Date(created_at) as date'),

        ));
        foreach ($sales as $data){
            foreach ($data->products as $detail){
                $data[$detail['name']] = $detail['quantity'];
            }
            unset($data->products);
        }
        return $sales;
        return response()->json([$data,$data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
        //Validate data
        $data = $request->only('products');
        $validator = Validator::make($data, [
            'products' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new product
        $product = $this->user->sales()->create([
            'user_id' => $this->user->id,
            'products' => $request->products,
            'total_sales' => 1,
            'total_commission' => 1
        ]);

        //Product created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Sales record created successfully',
            'data' => $product
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesRecord  $salesRecord
     * @return \Illuminate\Http\Response
     */
    public function show(SalesRecord $salesRecord)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesRecord  $salesRecord
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesRecord $salesRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesRecord  $salesRecord
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesRecord $salesRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesRecord  $salesRecord
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesRecord $salesRecord)
    {
        //
    }

    public static function invalidToken()
    {
        $current_token = JWTAuth::getToken();
        JWTAuth::setToken($current_token)->invalidate();
    }
}

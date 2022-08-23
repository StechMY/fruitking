<?php

namespace App\Http\Controllers;

use App\Models\SalesRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
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
        return $this->user
            ->sales()
            ->get();
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
}

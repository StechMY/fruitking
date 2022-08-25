<?php

namespace App\Http\Controllers;

use App\Models\Fruit;
use App\Models\SalesRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\MockObject\Stub\ReturnReference;
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
        $allsales = collect();
        $salesrecord = $this->user->sales()->get(array(
            DB::raw('products'),
            DB::raw('total_sales'),
            DB::raw('total_commission'),
            DB::raw('Date(created_at) as date'),
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') new_date")

        ));
        $salesdaily = $salesrecord->groupBy('date');
        $salesmonthly = $salesrecord->groupBy('new_date');
        return $salesmonthly;
        foreach ($salesdaily as $datekey => $date) {
            $date['total_sales'] = $date->sum('total_sales');
            $date['total_commission'] = $date->sum('total_commission');
            $tempfruits = array(['fruitname'=>'jiashen']);

            foreach ($date as $detailkey => $detail) {
                if (is_int($detailkey)) {
                    foreach ($detail->products as $value) {
                        if ($matchkey = array_search($value['fruitname'], array_column($tempfruits, 'fruitname'))) {
                            // return $value['fruitname'];
                            // return array_column($tempfruits, 'fruitname');
                            $tempfruits[$matchkey]['qty'] += $value['quantity'];
                            $tempfruits[$matchkey]['total_sales'] += $value['sales_price'] * $value['quantity'];
                        } else {
                            $newfruit = [
                                'fruitname' => $value['fruitname'],
                                'qty' => $value['quantity'],
                                'total_sales' => $value['sales_price'] * $value['quantity'],
                            ];
                            array_push($tempfruits, $newfruit);
                        }
                    }
                    unset($salesdaily[$datekey][$detailkey]);
                }
            }
            unset($tempfruits[0]);
            $date['fruits'] = array_values($tempfruits);
        }
        // return $salesmonthly;
        foreach ($salesmonthly as $monthkey => $month) {
            $month['total_sales'] = $month->sum('total_sales');
            $month['total_commission'] = $month->sum('total_commission');
            $tempfruits = array(['fruitname'=>'jiashen']);
            foreach ($month as $detailkey => $detail) {
                if (is_int($detailkey)) {
                    foreach ($detail->products as $value) {
                        if ($matchkey = array_search($value['fruitname'], array_column($tempfruits, 'fruitname'))) {
                            $tempfruits[$matchkey]['qty'] += $value['quantity'];
                            $tempfruits[$matchkey]['total_sales'] += $value['sales_price'] * $value['quantity'];
                        } else {
                            $newfruit = [
                                'fruitname' => $value['fruitname'],
                                'qty' => $value['quantity'],
                                'total_sales' => $value['sales_price'] * $value['quantity'],
                            ];
                            array_push($tempfruits, $newfruit);
                        }
                    }
                    unset($salesmonthly[$monthkey][$detailkey]);
                }
            }
            unset($tempfruits[0]);
            $month['fruits'] = array_values($tempfruits);
        }
        $allsales->put('daily', $salesdaily);
        $allsales->put('monthly', $salesmonthly);

        return response()->json($allsales);
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
        $newdata = collect();
        $totalcommission = 0;
        $totalsales = 0;
        foreach ($request->products as $data) {
            $fruit = Fruit::find($data['id']);
            $sales_price = $fruit->sales_price;
            $commission_price = $fruit->commission_price;
            $fruitname = $fruit->name;
            $fruitdata = [
                'fruitname' => $fruitname,
                'sales_price' => $sales_price,
                'commission_price' => $commission_price,
                'quantity' => $data['qty']
            ];
            $totalcommission += $commission_price * $data['qty'];
            $totalsales += $sales_price * $data['qty'];
            $newdata->push($fruitdata);
        }

        //Request is valid, create new product
        $product = $this->user->sales()->create([
            'user_id' => $this->user->id,
            'products' => $newdata,
            'total_sales' => $totalsales,
            'total_commission' => $totalcommission
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

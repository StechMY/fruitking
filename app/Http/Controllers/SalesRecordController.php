<?php

namespace App\Http\Controllers;

use App\Models\AgentStock;
use App\Models\Fruit;
use App\Models\SalesRecord;
use Carbon\Carbon;
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
        $salesrecord = $this->user->sales()->where('is_cancel', 0)->get(array(
            DB::raw('products'),
            DB::raw('total_sales'),
            DB::raw('total_commission'),
            DB::raw('Date(sold_at) as date'),
            DB::raw("DATE_FORMAT(sold_at, '%Y-%m') new_date")

        ));
        $salesdaily = $salesrecord->groupBy('date');
        $salesmonthly = $salesrecord->groupBy('new_date');
        foreach ($salesdaily as $datekey => $date) {
            $date['total_sales'] = $date->sum('total_sales');
            $date['total_commission'] = $date->sum('total_commission');
            $tempfruits = array();
            foreach ($date as $detailkey => $detail) {
                if (is_int($detailkey)) {
                    foreach ($detail->products as $value) {
                        if (is_int($matchkey = array_search($value['fruitname'], array_column($tempfruits, 'fruitname')))) {
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
            $date['fruits'] = array_values($tempfruits);
        }
        // return $salesmonthly;
        foreach ($salesmonthly as $monthkey => $month) {
            $month['total_sales'] = $month->sum('total_sales');
            $month['total_commission'] = $month->sum('total_commission');
            $tempfruits = array();
            foreach ($month as $detailkey => $detail) {
                if (is_int($detailkey)) {
                    foreach ($detail->products as $value) {
                        if (is_int($matchkey = array_search($value['fruitname'], array_column($tempfruits, 'fruitname')))) {
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
            $month['fruits'] = array_values($tempfruits);
        }
        $allsales->put('daily', $salesdaily);
        $newformatmonth = collect();
        foreach ($salesmonthly as $monthkey => $data) {
            $newformatmonth->put($monthkey . '-01', $data);
        }
        $allsales->put('monthly', $newformatmonth);
        $alltotalsales = $salesrecord->sum('total_sales');
        $allsales->put('all_total_sales', $alltotalsales);

        $allself = collect();
        $selfrecord = $this->user->stockrecord()->where('user_id', $this->user->id)->where('type', 4)->get(array(
            DB::raw('agentstock_id'),
            DB::raw('quantity'),
            DB::raw('Date(created_at) as date'),
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') new_date")

        ));
        $selfdaily = $selfrecord->groupBy('date');
        $selfmonthly = $selfrecord->groupBy('new_date');
        // return ($selfdaily);
        foreach ($selfdaily as $datekey => $date) {
            $tempfruits = array();
            foreach ($date as $detailkey => $detail) {
                $agentstock = AgentStock::find($detail->agentstock_id);
                $fruit = Fruit::find($agentstock->fruit_id);
                if (is_int($matchkey = array_search($fruit->name, array_column($tempfruits, 'fruitname')))) {
                    $tempfruits[$matchkey]['qty'] += $detail->quantity;
                    // $tempfruits[$matchkey]['total_sales'] += $value['sales_price'] * $value['quantity'];
                } else {
                    $newfruit = [
                        'fruitname' => $fruit->name,
                        'qty' => $detail->quantity,
                        // 'total_sales' => $value['sales_price'] * $value['quantity'],
                    ];
                    array_push($tempfruits, $newfruit);
                }
                unset($selfdaily[$datekey][$detailkey]);
            }
            $date['fruits'] = array_values($tempfruits);
        }
        // return $salesmonthly;
        foreach ($selfmonthly as $monthkey => $month) {
            $tempfruits = array();
            foreach ($month as $detailkey => $detail) {
                $agentstock = AgentStock::find($detail->agentstock_id);
                $fruit = Fruit::find($agentstock->fruit_id);
                if (is_int($matchkey = array_search($fruit->name, array_column($tempfruits, 'fruitname')))) {
                    $tempfruits[$matchkey]['qty'] += $detail->quantity;
                    // $tempfruits[$matchkey]['total_sales'] += $value['sales_price'] * $value['quantity'];
                } else {
                    $newfruit = [
                        'fruitname' => $fruit->name,
                        'qty' => $detail->quantity,
                        // 'total_sales' => $value['sales_price'] * $value['quantity'],
                    ];
                    array_push($tempfruits, $newfruit);
                }

                unset($selfmonthly[$monthkey][$detailkey]);
            }
            $month['fruits'] = array_values($tempfruits);
        }
        $allself->put('daily', $selfdaily);
        $newformatmonth = collect();
        foreach ($selfmonthly as $monthkey => $data) {
            $newformatmonth->put($monthkey . '-01', $data);
        }
        $allself->put('monthly', $newformatmonth);
        // $alltotalsales = $salesrecord->sum('total_sales');
        // $allsales->put('all_total_sales', $alltotalsales);

        $allgive = collect();
        $givereocrd = $this->user->stockrecord()->where('user_id', $this->user->id)->where('type', 5)->get(array(
            DB::raw('agentstock_id'),
            DB::raw('quantity'),
            DB::raw('Date(created_at) as date'),
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') new_date")

        ));
        $givedaily = $givereocrd->groupBy('date');
        $givemonthly = $givereocrd->groupBy('new_date');
        // return ($givedaily);
        foreach ($givedaily as $datekey => $date) {
            $tempfruits = array();
            foreach ($date as $detailkey => $detail) {
                $agentstock = AgentStock::find($detail->agentstock_id);
                $fruit = Fruit::find($agentstock->fruit_id);
                if (is_int($matchkey = array_search($fruit->name, array_column($tempfruits, 'fruitname')))) {
                    $tempfruits[$matchkey]['qty'] += $detail->quantity;
                    // $tempfruits[$matchkey]['total_sales'] += $value['sales_price'] * $value['quantity'];
                } else {
                    $newfruit = [
                        'fruitname' => $fruit->name,
                        'qty' => $detail->quantity,
                        // 'total_sales' => $value['sales_price'] * $value['quantity'],
                    ];
                    array_push($tempfruits, $newfruit);
                }
                unset($givedaily[$datekey][$detailkey]);
            }
            $date['fruits'] = array_values($tempfruits);
        }
        // return $salesmonthly;
        foreach ($givemonthly as $monthkey => $month) {
            $tempfruits = array();
            foreach ($month as $detailkey => $detail) {
                $agentstock = AgentStock::find($detail->agentstock_id);
                $fruit = Fruit::find($agentstock->fruit_id);
                if (is_int($matchkey = array_search($fruit->name, array_column($tempfruits, 'fruitname')))) {
                    $tempfruits[$matchkey]['qty'] += $detail->quantity;
                    // $tempfruits[$matchkey]['total_sales'] += $value['sales_price'] * $value['quantity'];
                } else {
                    $newfruit = [
                        'fruitname' => $fruit->name,
                        'qty' => $detail->quantity,
                        // 'total_sales' => $value['sales_price'] * $value['quantity'],
                    ];
                    array_push($tempfruits, $newfruit);
                }

                unset($givemonthly[$monthkey][$detailkey]);
            }
            $month['fruits'] = array_values($tempfruits);
        }
        $allgive->put('daily', $givedaily);
        $newformatmonth = collect();
        foreach ($givemonthly as $monthkey => $data) {
            $newformatmonth->put($monthkey . '-01', $data);
        }
        $allgive->put('monthly', $newformatmonth);
        // $alltotalsales = $salesrecord->sum('total_sales');
        // $allsales->put('all_total_sales', $alltotalsales);

        return response()->json(['success' => true, 'record' => $allsales, 'self' => $allself, 'give' => $allgive]);
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
        $data = $request->only('products', 'type');
        $validator = Validator::make($data, [
            'products' => 'required',
            'type' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        $error = '';
        foreach ($request->products as $data) {
            $agentstock = AgentStock::where('agent_id', $this->user->agent_id)->where('fruit_id', $data['id'])->first();
            $fruit = Fruit::find($data['id']);
            if ($agentstock->stock_pack < $data['qty']) {
                $error .= '[' . $fruit->name . ']';
            }
        }
        if ($error != '') {
            return response()->json([
                'success' => true,
                'message' => $error . ' is not available/quantity not enough',
                'data' => $agentstock
            ], 200);
        }
        $newdata = collect();
        $totalcommission = 0;
        $totalsales = 0;
        foreach ($request->products as $data) {
            $fruit = Fruit::find($data['id']);
            $sales_price = $fruit->sales_price;
            // $ori_price = $fruit->ori_price;
            $commission_price = $fruit->commission_price;
            $fruitname = $fruit->name;
            $agentstock = AgentStock::where('agent_id', $this->user->agent_id)->where('fruit_id', $data['id'])->first();
            $totalcommission += $commission_price * $data['qty'];
            $totalsales += $sales_price * $data['qty'];
            $agentstockbefore = $agentstock->stock_pack;
            $agentstock->stock_pack -= $data['qty'];
            $agentstock->save();
            // if ($agentstock->stock_pack <= 0) {
            //     $agentstock->status = 0;
            //     $agentstock->save();
            // }
            if ($request->type == 3) {
                $remark = $this->user->username . '賣出';
            } else if ($request->type == 4) {
                $remark = $this->user->username . ' 員工價 ' . $request->remarks;
            } else if ($request->type == 5) {
                $remark = $this->user->username . ' 警察送出 ' . $request->remarks;
            }
            $agentstockafter = $agentstock->stock_pack;
            if ($request->type == 3) {
                $agentstockrecord = $agentstock->record()->create([
                    'user_id' => $this->user->id,
                    'stock_before' => $agentstockbefore,
                    'quantity' => - ($data['qty']),
                    'stock_after' => $agentstockafter,
                    'type' => $request->type,
                    'total_price' => $fruit->sales_price * $data['qty'],
                    'remarks' => $remark
                ]);
            } else {
                $agentstockrecord = $agentstock->record()->create([
                    'user_id' => $this->user->id,
                    'stock_before' => $agentstockbefore,
                    'quantity' => - ($data['qty']),
                    'stock_after' => $agentstockafter,
                    'type' => $request->type,
                    'total_price' => $fruit->ori_price * $data['qty'],
                    'remarks' => $remark
                ]);
            }

            $fruitdata = [
                'agent_stock_id' => $agentstock->id,
                'agent_stock_record_id' => $agentstockrecord->id,
                'fruitname' => $fruitname,
                'sales_price' => $sales_price,
                'commission_price' => $commission_price,
                'quantity' => $data['qty']
            ];
            $newdata->push($fruitdata);
        }
        $timenow = Carbon::now();
        $sold_at = Carbon::now()->toDateString();
        if ($timenow->lt($timenow->startOfDay()->addHours(11))) {
            $sold_at = Carbon::now()->subDay()->toDateString();
        }
        //Request is valid, create new product
        if ($request->type == 3) {
            $product = $this->user->sales()->create([
                'user_id' => $this->user->id,
                'products' => $newdata,
                'total_sales' => $totalsales,
                'total_commission' => $totalcommission,
                'remarks' => $remark,
                'sold_at' => $sold_at
            ]);
        }
        //Product created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Submit successfully',
            'data' => $data
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

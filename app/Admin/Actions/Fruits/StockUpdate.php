<?php

namespace App\Admin\Actions\Fruits;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class StockUpdate extends RowAction
{
    public $name = '更新庫存';

    public function handle(Model $model, Request $request)
    {
        $quantity = $request->get('quantity');
        $stockbefore = $model->stock;
        $model->stock += $quantity;
        $model->save();
        $stockafter = $model->stock;
        if ($quantity != 0) {
            $model->record()->create([
                'from_id' => Admin::user()->id,
                'stock_before' => $stockbefore,
                'quantity' => $quantity,
                'stock_after' => $stockafter,
                'remarks' => 'Admin 更新库存'
            ]);
        }

        return $this->response()->success('更新成功')->refresh();
    }

    public function form()
    {

        $this->text('quantity', '數量')->rules('required|numeric');
    }

    public function display($value)
    {
        return $value;
    }
}

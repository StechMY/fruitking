<?php

namespace App\Admin\Actions\AgentStock;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AgentStockUpdate extends RowAction
{
    public $name = '更新庫存';

    public function handle(Model $model, Request $request)
    {
        $type = $request->get('type');
        $quantity = $request->get('quantity');
        $stockbefore = $model->stock_pack;
        if ($type == 1) {
            $model->stock_pack += $quantity;
        } else {
            $model->stock_pack -= $quantity;
            $quantity = -$quantity;
        }
        $model->save();
        $stockafter = $model->stock_pack;
        if ($quantity != 0) {
            $model->record()->create([
                'stock_before' => $stockbefore,
                'quantity' => $quantity,
                'stock_after' => $stockafter,
                'remarks' => 'Admin 更改库存',
                'user_id' => 0,
                'type' => 0,
                'total_price' => 0,
            ]);
        }
        return $this->response()->success('更新成功')->refresh();
    }

    public function form()
    {

        $this->text('quantity', '數量')->rules('required|numeric|gt:0');
        $this->radio('type', '操作')->options([1 => '增加', 2 => '减少'])->rules('required');
    }

    public function display($value)
    {
        return $value;
    }
}

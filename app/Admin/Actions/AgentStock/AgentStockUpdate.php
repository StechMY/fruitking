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
        $quantity = $request->get('quantity');
        $stockbefore = $model->stock_pack;
        $model->stock_pack += $quantity;
        $model->save();
        $stockafter = $model->stock_pack;
        if ($quantity != 0) {
            $model->agentstock()->create([
                'stock_before' => $stockbefore,
                'quantity' => $quantity,
                'stock_after' => $stockafter,
                'remarks' => 'Agent 更新库存'
            ]);
        }
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
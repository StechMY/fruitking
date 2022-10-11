<?php

namespace App\Admin\Actions\AgentStock;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Stocktake extends RowAction
{
    public $name = '自購';

    public function handle(Model $model, Request $request)
    {
        $quantity = $request->get('quantity');
        $stockbefore = $model->stock_pack;
        $model->stock_pack -= $quantity;
        $model->save();
        $stockafter = $model->stock_pack;
        if ($quantity != 0) {
            $model->record()->create([
                'stock_before' => $stockbefore,
                'quantity' => $quantity,
                'stock_after' => $stockafter,
                'type' => 2,
                'user_id' => 0,
                'remarks' => Admin::user()->username . '自購'
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
        return '<button type="button" class="btn btn-success">自購</button>';
    }
}

<?php

namespace App\Admin\Actions\SalesRecord;

use App\Models\AgentStock;
use App\Models\AgentStockRecord;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Cancel extends RowAction
{
    public $name = '取消此單';

    public function handle(Model $model, Request $request)
    {
        if ($model->is_cancel == 1) {
            return $this->response()->error('已被操作')->refresh();
        }
        $model->is_cancel = 1;
        $model->save();
        foreach ($model->products as $data) {
            $agentstock = AgentStock::find($data['agent_stock_id']);
            $agentstockrecord = AgentStockRecord::find($data['agent_stock_record_id']);
            $agentstockrecord->delete();
            $stockbefore = $agentstock->stock_pack;
            $agentstock->stock_pack += $data['quantity'];
            $agentstock->save();
            $stockafter = $agentstock->stock_pack;
            $agentstock->record()->create([
                'stock_before' => $stockbefore,
                'quantity' => $data['quantity'],
                'stock_after' => $stockafter,
                'remarks' => 'Sales被取消 返回庫存',
                'type' => 0,
                'total_price' => 0,
                'user_id' => 0,
            ]);
        }
        return $this->response()->success('更新成功')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定取消？');
    }

    public function display($value)
    {
        return '<button type="button" class="btn btn-error">取消</button>';
    }
}

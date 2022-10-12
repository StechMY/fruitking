<?php

namespace App\Admin\Actions\SalesRecord;

use App\Models\AgentStock;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Cancel extends RowAction
{
    public $name = '取消此單';

    public function handle(Model $model, Request $request)
    {
        $model->is_cancel = 1;
        $model->save();
        foreach ($model->products as $data) {
            $agentstock = AgentStock::find($data['agent_stock_id']);
            $agentstock->stock_pack += $data['quantity'];
            $agentstock->save();
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

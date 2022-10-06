<?php

namespace App\Admin\Controllers;

use App\Models\AgentStock;
use App\Models\AgentStockRecord;
use App\Models\Fruit;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class AgentSelfBuyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'AgentStockRecord';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AgentStockRecord());

        $grid->export(function ($export) {
            $export->column('agentstock_id', function ($value, $original) {
                $model = AgentStock::find($original);
                $fruitid = $model->fruit_id;
                $fruit = Fruit::find($fruitid);
                $fruitname = $fruit->name;
                $agentid = $model->agent_id;
                $agent = Administrator::find($agentid);
                $agentname = $agent->username;
                return $agentname . ':' . $fruitname;
            });
        });
        $grid->model()->orderBy('id', 'DESC')->where('type', 2);
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->between('created_at', 'Time')->datetime();
            $filter->equal('agentstock.fruit_id', __('Fruit'))->select(Fruit::pluck('name', 'id'));
            if (Admin::user()->inRoles(['administrator', 'company'])) {
                $filter->equal('agentstock.agent_id', ' Agent')->select(Administrator::whereExists(function ($query) {
                    $query->select(DB::raw('role_id', 'user_id'))
                        ->from('admin_role_users')
                        ->where('admin_role_users.role_id', 2)
                        ->whereColumn('admin_role_users.user_id', 'admin_users.id');
                })->pluck('username', 'id'));
            }
        });
        $grid->column('id', __('Id'));
        $grid->column('agentstock_id', __('Agent stock'))->display(function ($data) {
            $model = AgentStock::find($data);
            $fruitid = $model->fruit_id;
            $fruit = Fruit::find($fruitid);
            $fruitname = $fruit->name;
            $agentid = $model->agent_id;
            $agent = Administrator::find($agentid);
            $agentname = $agent->username;
            return "<span style='color:blue'>$agentname:</span><span style='color:red'>$fruitname</span>";
        });
        $grid->disableActions();
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->disableCreateButton();
        if (!Admin::user()->inRoles(['administrator', 'company'])) {
            $grid->model()->whereHas("agentstock", function ($q) {
                return $q->where('agent_stocks.agent_id', '=', Admin::user()->id);
            });
        }
        $grid->column('stock_before', __('Stock before'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('stock_after', __('Stock after'));
        $grid->column('remarks', __('Remarks'))->filter('like');;
        $grid->column('type', __('Type'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->header(function ($query) {
            // dd(request()->all());
            $fruits = Fruit::when(request('agentstock') != null, function ($q) {
                return $q->when(request('agentstock')['fruit_id'] != null, function ($q) {
                    return $q->where('id', request('agentstock')['fruit_id']);
                });
            })->get();
            $htmltext = '';
            foreach ($fruits as $data) {
                $quantity = AgentStockRecord::where('type', 2)->whereHas(["agentstock" => function ($q) use ($data) {
                    $q->whereHas("fruit", function ($q) use ($data) {
                        $q->where('fruit.id', '=', $data->id);
                    });
                }])
                    ->when(request('created_at') != null, function ($q) {
                        return $q->when(request('created_at')['start'] != null && request('created_at')['end'] == null, function ($q) {
                            return $q->where('created_at', '>', request('created_at')['start']);
                        })
                            ->when(request('created_at')['end'] != null && request('created_at')['start'] == null, function ($q) {
                                return $q->where('created_at', '<', request('created_at')['end']);
                            })
                            ->when(request('created_at')['end'] != null && request('created_at')['start'] != null, function ($q) {
                                return $q->whereBetween('created_at', request('created_at'));
                            });
                    })
                    // ->when(request('fruit_id') != null, function ($q) {
                    //     return $q->where('fruit_id', request('fruit_id'));
                    // })
                    ->when(request('agentstock') != null && Admin::user()->inRoles(['administrator', 'company']), function ($q) {
                        return $q->when(request('agentstock')['agent_id'] != null, function ($q) {
                            // dd('lol');
                            return $q->whereHas("agentstock", function ($q) {
                                $q->where('agent_id', '=', request('agentstock')['agent_id']);
                            });
                        });
                    })->when(request('user_id') != null, function ($q) {
                        return $q->where('user_id', request('user_id'));
                    })->sum('quantity');
                $htmltext .= "<div class='badge bg-yellow' style='padding: 10px;margin-right:10px;'>" . $data->name . ": " . $quantity . "</div>";
            }


            return $htmltext;
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    // protected function detail($id)
    // {
    //     $show = new Show(AgentStockRecord::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('agentstock_id', __('Agentstock id'));
    //     $show->field('stock_before', __('Stock before'));
    //     $show->field('quantity', __('Quantity'));
    //     $show->field('stock_after', __('Stock after'));
    //     $show->field('remarks', __('Remarks'));
    //     $show->field('created_at', __('Created at'));
    //     $show->field('updated_at', __('Updated at'));

    //     return $show;
    // }

    // /**
    //  * Make a form builder.
    //  *
    //  * @return Form
    //  */
    // protected function form()
    // {
    //     $form = new Form(new AgentStockRecord());

    //     $form->number('agentstock_id', __('Agentstock id'));
    //     $form->number('stock_before', __('Stock before'));
    //     $form->number('quantity', __('Quantity'));
    //     $form->number('stock_after', __('Stock after'));
    //     $form->text('remarks', __('Remarks'));

    //     return $form;
    // }
}

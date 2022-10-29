<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\AgentStock\AgentStockUpdate;
use App\Admin\Actions\AgentStock\Stocktake;
use App\Models\AgentStock;
use App\Models\Fruit;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AgentStockController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Agent库存';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $agent = Administrator::where('id', '!=', 1)->get();
        $fruit = Fruit::all();
        foreach ($agent as $data) {
            foreach ($fruit as $fruitdata) {
                $checkfruit = AgentStock::where('agent_id', $data->id)->where('fruit_id', $fruitdata->id)->first();
                if ($checkfruit === null) {
                    AgentStock::create(
                        [
                            'agent_id' => $data->id,
                            'fruit_id' => $fruitdata->id,
                            'stock_pack' => 0,
                        ]
                    );
                }
            }
        }
        $grid = new Grid(new AgentStock());
        $grid->disableExport();
        if (!Admin::user()->inRoles(['administrator', 'company'])) {
            $grid->model()->where('agent_id', Admin::user()->id);
        }
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            if (Admin::user()->inRoles(['administrator', 'company'])) {
                $filter->in('agent_id', __('Agent'))->multipleSelect(Administrator::pluck('username', 'id'));
            }
            $filter->in('fruit_id', __('Fruit'))->multipleSelect(Fruit::where('status', 1)->pluck('name', 'id'));
            // $filter->equal('status', __('Status'))->select([0 => 'Suspend', 1 => 'Active']);
        });
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->disableActions();
        // actions(function ($actions) {

        //     // 去掉删除
        //     $actions->disableDelete();

        //     // 去掉查看
        //     $actions->disableView();
        // });
        $grid->disableCreateButton();
        // $grid->column('id', __('Id'));
        if (Admin::user()->inRoles(['administrator', 'company'])) {
            $grid->column('agent.username', __('Agent'));
        }
        $grid->column('fruit.name', __('Fruit'));
        if (Admin::user()->inRoles(['administrator', 'company'])) {
            $grid->column('stock_pack', __('Stock pack'))->action(AgentStockUpdate::class);
        } else {
            $grid->column('stock_pack', __('Stock pack'));
        }
        // if (!Admin::user()->inRoles(['administrator', 'company'])) {
        //     $grid->column('action', __('操作'))->action(Stocktake::class);
        // }

        // $grid->column('stock_pack', __('Stock pack'))->action(AgentStockUpdate::class);
        // $states = [
        //     'on'  => ['value' => 1, 'text' => 'On', 'color' => 'primary'],
        //     'off' => ['value' => 0, 'text' => 'Off', 'color' => 'default'],
        // ];
        // $grid->column('status', __('Status'))->switch($states);
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

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
    //     $show = new Show(AgentStock::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('agent_id', __('Agent id'));
    //     $show->field('fruit_id', __('Fruit id'));
    //     $show->field('stock_pack', __('Stock pack'));
    //     $show->field('status', __('Status'));
    //     $show->field('created_at', __('Created at'));
    //     $show->field('updated_at', __('Updated at'));

    //     return $show;
    // }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    // protected function form()
    // {
    //     $form = new Form(new AgentStock());

    //     $form->number('agent_id', __('Agent id'));
    //     $form->number('fruit_id', __('Fruit id'));
    //     $form->number('stock_pack', __('Stock pack'))->required()->rules('gt:-1|numeric');
    //     $states = [
    //         'on'  => ['value' => 1, 'text' => 'On', 'color' => 'success'],
    //         'off' => ['value' => 0, 'text' => 'Off', 'color' => 'danger'],
    //     ];
    //     $form->switch('status', __('Status'))->default('1')->states($states)->required();

    //     return $form;
    // }
}

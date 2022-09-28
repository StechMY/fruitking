<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\AgentStock\AgentStockUpdate;
use App\Models\AgentStock;
use App\Models\Fruit;
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
    protected $title = 'AgentStock';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AgentStock());
        $grid->model()->where('agent_id', Admin::user()->id);
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('fruit_id', __('Fruit'))->select(Fruit::where('status',1)->pluck('name','id'));
            $filter->equal('status', __('Status'))->select([0 => 'Suspend', 1 => 'Active']);
        });
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->actions(function ($actions) {

            // 去掉删除
            $actions->disableDelete();
        
            // 去掉查看
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->column('id', __('Id'));
        $grid->column('fruit.name', __('Fruit'));
        $grid->column('stock_pack', __('Stock pack'))->action(AgentStockUpdate::class);
        $states = [
            'on'  => ['value' => 1, 'text' => 'On', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'Off', 'color' => 'default'],
        ];
        $grid->column('status', __('Status'))->switch($states);
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
    protected function form()
    {
        $form = new Form(new AgentStock());

        // $form->number('agent_id', __('Agent id'));
        // $form->number('fruit_id', __('Fruit id'));
        $form->number('stock_pack', __('Stock pack'))->required()->rules('gt:-1|numeric');
        $states = [
            'on'  => ['value' => 1, 'text' => 'On', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Off', 'color' => 'danger'],
        ];
        $form->switch('status', __('Status'))->default('1')->states($states)->required();

        return $form;
    }
}
<?php

namespace App\Admin\Controllers;

use App\Models\AgentStockRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AgentStockRecordController extends AdminController
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

        $grid->column('id', __('Id'));
        $grid->column('agentstock_id', __('Agentstock id'));
        $grid->column('stock_before', __('Stock before'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('stock_after', __('Stock after'));
        $grid->column('remarks', __('Remarks'));
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
    protected function detail($id)
    {
        $show = new Show(AgentStockRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('agentstock_id', __('Agentstock id'));
        $show->field('stock_before', __('Stock before'));
        $show->field('quantity', __('Quantity'));
        $show->field('stock_after', __('Stock after'));
        $show->field('remarks', __('Remarks'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AgentStockRecord());

        $form->number('agentstock_id', __('Agentstock id'));
        $form->number('stock_before', __('Stock before'));
        $form->number('quantity', __('Quantity'));
        $form->number('stock_after', __('Stock after'));
        $form->text('remarks', __('Remarks'));

        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Fruit;
use App\Models\StockRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class StockController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'StockRecord';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockRecord());
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->between('created_at', 'Time')->datetime();
            $filter->equal('fruit_id', __('Fruit'))->select(Fruit::pluck('name', 'id'));
        });
        $grid->disableActions();
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->disableCreateButton();
        $grid->column('id', __('Id'));
        $grid->column('fruit.id', __('Fruit'))->expand(function ($model) {

            $data = $model->fruit()->get()->map(function ($value) {
                return $value->only(['name']);
            });

            return new Table(['Name'], $data->toArray());
        });
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
        $show = new Show(StockRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('fruit_id', __('Fruit id'));
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
        $form = new Form(new StockRecord());

        $form->number('fruit_id', __('Fruit id'));
        $form->number('stock_before', __('Stock before'));
        $form->number('quantity', __('Quantity'));
        $form->number('stock_after', __('Stock after'));
        $form->text('remarks', __('Remarks'));
       
        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Expense;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ExpenseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Expense';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Expense());
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
            $create->text('name', '名称')->rules('required');
            $create->text('fee', '費用')->rules('required|numeric');
        });
        $grid->actions(function ($actions) {

            $actions->disableEdit();

            // 去掉查看
            $actions->disableView();
        });
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('fee', __('Fee'));
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
    //     $show = new Show(Expense::findOrFail($id));



    //     return $show;
    // }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Expense());

        $form->tools(function (Form\Tools $tools) {

            // 去掉`查看`按钮
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
        });
        $form->text('name', __('Name'))->required();
        $form->currency('fee', __('Fee'))->required();

        return $form;
    }
}

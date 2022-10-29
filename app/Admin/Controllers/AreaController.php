<?php

namespace App\Admin\Controllers;

use App\Models\AreaCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AreaController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '地区设置';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AreaCode());
        $grid->actions(function ($actions) {

            $actions->disableEdit();

            // 去掉查看
            $actions->disableView();
        });
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('code', __('Code'));
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
    //     $show = new Show(AreaCode::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('name', __('Name'));
    //     $show->field('code', __('Code'));
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
        $form = new Form(new AreaCode());
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
        $form->text('code', __('Code'))->required();

        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Fruit;
use App\Models\StockRecord;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;

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
        $grid->model()->whereIn('type', [0, 1])->orderBy('id', 'DESC');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->between('created_at', 'Time')->datetime();
            $filter->in('fruit_id', __('Fruit'))->multipleSelect(Fruit::pluck('name', 'id'));
            if (Admin::user()->inRoles(['administrator', 'company'])) {
                $filter->in('from_id', ' From')->multipleSelect(Administrator::whereExists(function ($query) {
                    $query->select(DB::raw('role_id', 'user_id'))
                        ->from('admin_role_users')
                        ->where('admin_role_users.role_id', 2)
                        ->whereColumn('admin_role_users.user_id', 'admin_users.id');
                })->pluck('username', 'id'));
            }
        });
        $grid->disableActions();
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->disableCreateButton();
        if (!Admin::user()->inRoles(['administrator', 'company'])) {
            $grid->model()->where('from_id', Admin::user()->id);
        }
        $grid->column('id', __('Id'));
        $grid->column('from.username', __('From'));
        $grid->column('fruit.name', __('Fruit'));
        // if (Admin::user()->inRoles(['administrator', 'company'])) {
        //     $grid->column('stock_before', __('Stock before'));
        // }
        $grid->column('quantity', __('Quantity'));
        // if (Admin::user()->inRoles(['administrator', 'company'])) {
        //     $grid->column('stock_after', __('Stock after'));
        // }
        // $grid->column('type', __('Type'));
        $grid->column('total_price', __('Total Price'));
        $grid->column('remarks', __('Remarks'))->filter('like');
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->header(function ($query) {
            $fruits = Fruit::when(!empty(request('fruit_id')), function ($q) {
                return $q->whereIn('id', request('fruit_id'));
            })->get();
            $htmltext = '';
            foreach ($fruits as $data) {
                $quantity = StockRecord::whereIn('type', [0, 1])->where('fruit_id', $data->id)
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
                    ->when(!empty(request('from_id')), function ($q) {
                        return $q->whereIn('from_id', request('from_id'));
                    })->sum('quantity');
                $htmltext .= "<button type='button' class='btn btn-error btn-custom'>"
                    . $data->name . ": " . $quantity .  '<br> * RM' . $data->ori_price . " (?????????) = <span style='color:red;'>RM " . $quantity * $data->ori_price .   "</span></button>";
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
    //     $show = new Show(StockRecord::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('from_id', __('Fruit id'));
    //     $show->field('fruit_id', __('Fruit id'));
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
    //     $form = new Form(new StockRecord());

    //     $form->number('fruit_id', __('Fruit id'));
    //     $form->number('stock_before', __('Stock before'));
    //     $form->number('quantity', __('Quantity'));
    //     $form->number('stock_after', __('Stock after'));
    //     $form->text('remarks', __('Remarks'));

    //     return $form;
    // }
}

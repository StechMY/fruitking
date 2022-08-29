<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Fruits\StockUpdate;
use App\Models\Fruit;
use App\Models\StockRecord;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Exception;
use Illuminate\Http\Request;

class FruitController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Fruit';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Fruit());
        $grid->model()->orderBy('id', 'desc');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('name', __('Name'));
            $filter->equal('status', __('Status'))->select([0 => 'Suspend', 1 => 'Active']);
        });
        $grid->actions(function ($actions) {
            $actions->add(new StockUpdate);
        });
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        // $grid->column('ori_price', __('Ori price'));
        $grid->column('sales_price', __('Sales price'));
        $grid->column('commission_price', __('Commission price'));
        $grid->column('image', __('Image'))->image();
        $grid->column('stock', __('Stock'));
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
    protected function detail($id)
    {
        $show = new Show(Fruit::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        // $show->field('ori_price', __('Ori price'));
        $show->field('sales_price', __('Sales price'));
        $show->field('commission_price', __('Commission price'));
        $show->field('image', __('Image'))->image();
        $show->field('stock', __('Stock'));
        $show->field('status', __('Status'));
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
        $form = new Form(new Fruit());

        $form->text('name', __('Name'))->required();
        $form->hidden('ori_price', __('Ori price'))->default(0);
        $form->decimal('sales_price', __('Sales price'))->required();
        $form->decimal('commission_price', __('Commission price'))->required();
        $form->image('image', __('Image'))->required();
        $form->number('stock', __('Stock'))->default('0')->required()->rules('gt:-1|numeric');
        $states = [
            'on'  => ['value' => 1, 'text' => 'On', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Off', 'color' => 'danger'],
        ];
        $form->switch('status', __('Status'))->default('1')->states($states)->required();
        global $stockbefore;
        $form->saving(function (Form $form) {
            if ($form->isEditing()) {
                $before = Fruit::find($form->model()->id);
                global $stockbefore;
                $stockbefore = $before->stock;
            }else {
                global $stockbefore;
                $stockbefore = 0;
            }
        });
        $form->saved(function (Form $form) {
            global $stockbefore;
            $fruit = Fruit::find($form->model()->id);
            if($fruit){
                if ($form->model()->stock != $stockbefore) {
                    $fruit->record()->create([
                        'from_id' => Admin::user()->id,
                        'stock_before' => $stockbefore,
                        'quantity' => $form->model()->stock - $stockbefore,
                        'stock_after' => $form->model()->stock,
                        'remarks' => 'Admin 更新库存'
                    ]);
                }
            }else {

            }
            
        });
        return $form;
    }

    public function takefruit(Request $request)
    {
        $agent = Administrator::find($request->agent);
        foreach ($request->data as $data) {
            if ($data['number'] > 0) {
                $fruit = Fruit::find($data['id']);
                $stok_before = $fruit->stock;
                $fruit->stock -= $data['number'];
                $fruit->save();
                $stok_after = $fruit->stock;
                StockRecord::create([
                    'fruit_id' => $data['id'],
                    'from_id' => $request->agent,
                    'from_id' => $request->agent,
                    'stock_before' =>  $stok_before,
                    'quantity' =>  - $data['number'],
                    'stock_after' =>  $stok_after,
                    'remarks' => $agent->username . ' 取货',
                ]);
            }
        }
        return response()->json('Ok');
    }
}

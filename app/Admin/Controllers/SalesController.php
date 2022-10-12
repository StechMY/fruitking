<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\SalesRecord\Cancel;
use App\Models\SalesRecord;
use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SalesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SalesRecord';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SalesRecord());
        $grid->export(function ($export) {
            $export->column('products', function ($value, $original) {
                $message = '';
                foreach ($original as $data) {
                    $message .= 'name: ' . $data['fruitname'] . ' quantity: ' . $data['quantity']
                        . ' sales_price: ' . $data['sales_price'] .  ' commission_price: ' . $data['commission_price'] . "\n";
                }
                return $message;
            });
        });
        $grid->model()->orderBy('id', 'DESC');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->between('sales_records.created_at', 'Time')->datetime();
            $filter->in('is_cancel', '被取消')->multipleSelect([0 => '無', 1 => '已取消']);
            if (Admin::user()->inRoles(['administrator', 'company'])) {
                $filter->in('user_id', __('User'))->multipleSelect(User::pluck('username', 'id'));
                $filter->where(function ($query) {
                    $query->whereHas('user', function ($query) {
                        $query->where('agent_id', $this->input);
                    });
                }, __('Agent'))->select(Administrator::pluck('username', 'id'));
            } else {
                $filter->in('user_id', __('User'))->multipleSelect(User::where('agent_id', Admin::user()->id)->pluck('username', 'id'));
            }
        });
        $grid->disableActions();
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->disableCreateButton();
        if (!Admin::user()->inRoles(['administrator', 'company'])) {
            $grid->model()->select('sales_records.*')->join('users', 'sales_records.user_id', '=', 'users.id')->where('users.agent_id', Admin::user()->id)->orderBy('sales_records.id', 'DESC');
        }
        $grid->column('id', __('Id'));
        $grid->column('user.username', __('User'));
        $grid->column('products', __('Products'))->display(function ($data) {
            $htmlrender = '<table class="table table-hover" style="margin-bottom: 0;">
            <thead>
            <tr>
                        <th>水果</th>
                        <th>數量</th>
                        <th>單個售價</th>
                        <th>單個佣金</th>
                    </tr>
            </thead>
            <tbody>';
            foreach ($data as $value) {
                $htmlrender .= '<tr>
                <td>' . $value['fruitname'] . '</td>
                <td>' . $value['quantity'] . '</td>
                <td>' . $value['sales_price'] . '</td>
                <td>' . $value['commission_price'] . '</td>
        </tr>';
            }
            $htmlrender .= '</tbody></table>';
            return $htmlrender;
        });
        $grid->column('total_sales', __('Total sales'));
        $grid->column('total_commission', __('Total commission'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('is_cancel', __('Cancel'))->using([
            0 => '無',
            1 => '已取消',
        ], '未知')->dot([
            0 => 'success',
            1 => 'danger',
        ], 'warning');
        if (Admin::user()->inRoles(['administrator', 'company'])) {
            $grid->column('cancel_action', __('Status'))->action(Cancel::class);
        }
        $grid->header(function ($query) {
            // dd(request()->all());
            $lastkey = array_key_last(request()->query()) ?? '_pjax';
            $totalsales = SalesRecord::when(!empty(request('is_cancel')), function ($q) {
                return $q->whereIn('is_cancel', request('is_cancel'));
            })->when(request('sales_records') != null, function ($q) {
                return $q->when(request('sales_records')['created_at']['start'] != null && request('sales_records')['created_at']['end'] == null, function ($q) {
                    return $q->where('created_at', '>', request('sales_records')['created_at']['start']);
                })
                    ->when(request('sales_records')['created_at']['end'] != null && request('sales_records')['created_at']['start'] == null, function ($q) {
                        return $q->where('created_at', '<', request('sales_records')['created_at']['end']);
                    })
                    ->when(request('sales_records')['created_at']['end'] != null && request('sales_records')['created_at']['start'] != null, function ($q) {
                        return $q->whereBetween('created_at', request('sales_records')['created_at']);
                    });
            })
                ->when(request('user_id') != null, function ($q) {
                    return $q->where('user_id', request('user_id'));
                })
                ->when($lastkey != '_pjax' && request($lastkey) != null && Admin::user()->inRoles(['administrator', 'company']), function ($q) use ($lastkey) {
                    return $q->whereHas('user', function ($query) use ($lastkey) {
                        $query->where('agent_id', request($lastkey));
                    });
                })
                ->when(!Admin::user()->inRoles(['administrator', 'company']), function ($q) {
                    return $q->whereHas('user', function ($query) {
                        $query->where('agent_id', Admin::user()->id);
                    });
                })
                ->sum('total_sales');
            $totalcommission = SalesRecord::when(!empty(request('is_cancel')), function ($q) {
                return $q->whereIn('is_cancel', request('is_cancel'));
            })->when(request('sales_records') != null, function ($q) {
                return $q->when(request('sales_records')['created_at']['start'] != null && request('sales_records')['created_at']['end'] == null, function ($q) {
                    return $q->where('created_at', '>', request('sales_records')['created_at']['start']);
                })
                    ->when(request('sales_records')['created_at']['end'] != null && request('sales_records')['created_at']['start'] == null, function ($q) {
                        return $q->where('created_at', '<', request('sales_records')['created_at']['end']);
                    })
                    ->when(request('sales_records')['created_at']['end'] != null && request('sales_records')['created_at']['start'] != null, function ($q) {
                        return $q->whereBetween('created_at', request('sales_records')['created_at']);
                    });
            })
                ->when(request('user_id') != null, function ($q) {
                    return $q->where('user_id', request('user_id'));
                })
                ->when($lastkey != '_pjax' && request($lastkey) != null && Admin::user()->inRoles(['administrator', 'company']), function ($q) use ($lastkey) {
                    return $q->whereHas('user', function ($query) use ($lastkey) {
                        $query->where('agent_id', request($lastkey));
                    });
                })
                ->when(!Admin::user()->inRoles(['administrator', 'company']), function ($q) {
                    return $q->whereHas('user', function ($query) {
                        $query->where('agent_id', Admin::user()->id);
                    });
                })
                ->sum('total_commission');
            return "<div class='badge bg-yellow' style='padding: 10px;margin-right:10px;'>" . __('Total Sales') . ": " . $totalsales . "</div>" .
                "<div class='badge bg-blue' style='padding: 10px;margin-right:10px;'>" . __('Total Commission') . ": " . $totalcommission . "</div>";
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
    //     $show = new Show(SalesRecord::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('user_id', __('User id'));
    //     $show->field('products', __('Products'));
    //     $show->field('total_sales', __('Total sales'));
    //     $show->field('total_commission', __('Total commission'));
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
    //     $form = new Form(new SalesRecord());

    //     $form->number('user_id', __('User id'));
    //     $form->text('products', __('Products'));
    //     $form->decimal('total_sales', __('Total sales'));
    //     $form->decimal('total_commission', __('Total commission'));

    //     return $form;
    // }
}

<?php

namespace App\Admin\Controllers;

use App\Models\AreaCode;
use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '手机用户';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->export(function ($export) {
            $export->except(['status']);
        });
        if (!Admin::user()->inRoles(['administrator', 'company'])) {
            $grid->model()->where('agent_id', Admin::user()->id);
        }
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('username', __('Username'));
            $filter->equal('status', __('Status'))->select([0 => 'Suspend', 1 => 'Active']);
        });
        $grid->column('id', __('Id'));
        if (Admin::user()->inRoles(['administrator', 'company'])) {
            $grid->column('agent.username', __('Agent'));
        }
        $grid->column('username', __('Username'));
        $grid->column('phone', __('Phone'));
        // $grid->column('password', __('Password'));
        // $grid->column('remember_token', __('Remember token'));
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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        // $show->field('password', __('Password'));
        // $show->field('remember_token', __('Remember token'));
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
        $form = new Form(new User());
        $arr = request()->route()->parameters();
        if (request()->route()->getActionMethod() == 'edit') {
            $user = User::find($arr['user']);
            if ($user) {
                if (!Admin::user()->inRoles(['administrator', 'company']) && Admin::user()->id != $user->agent_id) {
                    abort(403, 'Illegal Access');
                }
            } else {
                abort(404, 'User Not Found');
            }
        }
        if (!$form->isEditing()) {
            $form->select('area_id', __('Area id'))->options(AreaCode::pluck('name', 'id'))->required();
        }
        if (!Admin::user()->inRoles(['administrator', 'company'])) {
            $form->hidden('agent_id')->value(Admin::user()->id);
        } else {
            $form->select('agent_id', __('Agent id'))->options(Administrator::whereExists(function ($query) {
                $query->select(DB::raw('role_id', 'user_id'))
                    ->from('admin_role_users')
                    ->where('admin_role_users.role_id', 2)
                    ->whereColumn('admin_role_users.user_id', 'admin_users.id');
            })->pluck('username', 'id'))->rules('required');
        }
        $form->hidden('username', __('Username'));
        $form->text('phone', __('Phone'))->creationRules(['required', "unique:users,phone", 'regex:/^\d+$/'])->updateRules(['required', 'regex:/^\d+$/']);
        $form->password('password', __('Password'))->creationRules('required');
        // $form->text('remember_token', __('Remember token'));
        $states = [
            'on'  => ['value' => 1, 'text' => 'On', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Off', 'color' => 'danger'],
        ];
        $form->saving(function (Form $form) {
            if (empty($form->input('password'))) {
                $form->input('password', $form->model()->password);
            } else {
                $form->password = Hash::make($form->password);
            }
            if (empty($form->input('withdraw_password'))) {
                $form->input('withdraw_password', $form->model()->withdraw_password);
            } else {
                $form->withdraw_password = Hash::make($form->withdraw_password);
            }
        });
        $form->saved(function (Form $form) {
            $area = AreaCode::find($form->model()->area_id);
            $model = User::find($form->model()->id);
            $model->username = $area->code . sprintf('%03d', $form->model()->id);
            $model->save();
        });
        $form->switch('status', __('Status'))->states($states)->default(1);

        return $form;
    }
}

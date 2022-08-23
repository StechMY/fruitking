<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use App\Models\Fruit;
use Encore\Admin\Facades\Admin;

Encore\Admin\Form::forget(['map', 'editor']);
app('view')->prependNamespace('admin', resource_path('views/admin'));
$stockless = Fruit::where('status', 1)->where('stock', '<', 10)->get();
$message = '';
foreach ($stockless as $data) {
    $message .= $data->name . " 需要及時補貨 所剩: " . $data->stock . '<br>';
}
if ($stockless->count() > 0 && Admin::user()->isAdministrator()) {
    admin_warning('倉庫數量不足', $message);
}

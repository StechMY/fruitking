<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('users', UserController::class);
    $router->resource('fruits', FruitController::class);
    $router->resource('sales-records', SalesController::class);
    $router->resource('stock-records', StockController::class);
    $router->resource('area-codes', AreaController::class);
    $router->resource('agent-stocks', AgentStockController::class);
    $router->resource('agent-stock-records', AgentStockRecordController::class);
    $router->resource('employee-records', EmployeeBuyController::class);
    $router->resource('agent-employee-records', AgentEmployeeBuyController::class);
    // $router->resource('agent-self-records', AgentSelfBuyController::class); //Agent自购记录
    $router->resource('expenses', ExpenseController::class);
    // $router->resource('agent-profiles', AgentController::class);
});

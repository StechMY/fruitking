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
    // $router->resource('agent-profiles', AgentController::class);
});

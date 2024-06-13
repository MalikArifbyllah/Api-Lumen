<?php

$router->get('/ ', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'cors'], function ($router){
    $router->post('/login', 'AuthController@login');
    $router->get('/logout', 'AuthController@logout');
    $router->get('/profile', 'AuthController@me');

$router->group(['prefix' => 'stuff/'], function() use ($router) {

    $router->get('/', 'StuffController@index');
    $router->post('/store', 'StuffController@store');
    $router->get('/trash', 'StuffController@trash');

    $router->get('{id}', 'StuffController@show');
    $router->patch('/{id}', 'StuffController@update');
    $router->delete('/{id}', 'StuffController@destroy');
    $router->get('/restore/{id}', 'StuffController@restore');
    $router->delete('/permanent/{id}', 'StuffController@deletePermanent');
    
});

$router->group(['prefix' => 'user'], function() use ($router) {
    $router->post('/store', 'UserController@store');
    $router->get('/', 'UserController@index');
    $router->get('/update/{id}', 'UserController@update');
    $router->get('/delete/{id}', 'UserController@delete');
 });

$router->group(['prefix' => 'inbound-stuff'], function() use ($router) {
    $router->get('/', 'InboundStuffController@index');
    $router->post('/store', 'InboundStuffController@store');
    $router->get('/detail/{id}', 'InboundStuffController@show');
    $router->patch('/update/{id}', 'InboundStuffController@update');
    $router->delete('/delete/{id}', 'InboundStuffController@destroy');
    // $router->get('recycle-bin', 'InboundStuffController@recycleBin');
    $router->get('/restore/{id}', 'InboundStuffController@restore');
    // $router-get('/force-delete/{id}',
    // 'InboundStuffController@forceDestroy');
});

$router->group(['prefix' => 'lending'], function() use ($router){
    $router->get('/', 'LendingController@index');
    $router->post('/store', 'LendingController@store');
    $router->get('/trash', 'LendingController@trash');

    $router->post('/update/{id}', 'LendingController@update');
    $router->delete('/delete/{id}', 'LendingController@destroy');
    $router->get('/restore/{id}', 'LendingController@restore');
    $router->delete('/permanent/{id}', 'LendingController@deletePermanent');
});

$router->group(['prefix' => 'restoration'], function() use ($router){
    $router->get('/', 'RestorationController@index');
    $router->post('store', 'RestorationController@store');
});
});
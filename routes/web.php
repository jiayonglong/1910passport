<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('/web')->middleware('check.login')->group(function(){
    Route::get('/login','Login\LoginController@logins');       // 登录
    Route::post('/login','Login\LoginController@login');       // 登录
    Route::get('/logout','Login\LoginController@logout');       // 退出登录
    Route::get('/check/token','Login\LoginController@checkToken');    //验证token
});

<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


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
//验证码
Route::get('/verify',                   'Business\HomeController@verify');
//登陆模块
Route::group(['namespace'  => "Auth"], function () {
    Route::get('/login',                'LoginController@showLoginForm')->name('login');
    Route::post('/login',               'LoginController@login');
    Route::get('/logout',               'LoginController@logout')->name('logout');
});
//后台主要模块
Route::group(['namespace' => "Business",'middleware' => ['auth']], function () {
    Route::get('/',                     'HomeController@index');
    Route::get('/index',                'HomeController@welcome');
    Route::resource('/menus',           'MenuController');
    Route::resource('/users',           'UserController');
    Route::get('/buswithdraw/userinfo', 'BuswithdrawController@userInfo');
    Route::resource('/buswithdraw',     'BuswithdrawController');
    Route::resource('/bank',            'BankController');
    Route::post('/buswithdraw/valPwd',  'BuswithdrawController@valPwd');
    Route::post('/buswithdraw/resPwd',  'BuswithdrawController@resPwd');
    Route::post('/buswithdraw/valPaypwd',  'BuswithdrawController@valPaypwd');
    Route::post('/buswithdraw/resPaypwd',  'BuswithdrawController@resPaypwd');
    Route::post('/buswithdraw/resInfo','BuswithdrawController@resInfo');
    Route::resource('/order','OrderController');
    Route::resource('/info','InfoController');
    Route::resource('/billflow','BillflowController');
});
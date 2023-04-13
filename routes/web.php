<?php

use Illuminate\Support\Facades\Route;

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

//weibo静态页面 主页 帮助页 关于页
Route::get('/', 'StaticPagesController@home')->name('home');
Route::get('/help', 'StaticPagesController@help')->name('help');
Route::get('/about', 'StaticPagesController@about')->name('about');

//注册
Route::get('signup', 'UsersController@create')->name('signup');

//用户资源路由
Route::resource('users', 'UsersController');
/**
 * 上面Route::resource('users', 'UsersController');将等同于：
 *Route::get('/users', 'UsersController@index')->name('users.index');
 *Route::get('/users/create', 'UsersController@create')->name('users.create');
 *Route::get('/users/{user}', 'UsersController@show')->name('users.show');
 *Route::post('/users', 'UsersController@store')->name('users.store');
 *Route::get('/users/{user}/edit', 'UsersController@edit')->name('users.edit');
 *Route::patch('/users/{user}', 'UsersController@update')->name('users.update');
 *Route::delete('/users/{user}', 'UsersController@destroy')->name('users.destroy');
 */

 //登录页面
 Route::get('login', 'SessionsController@create')->name('login');
 //执行登录操作(创建新会话)
 Route::post('login', 'SessionsController@store')->name('login');
 //退出(销毁会话)
 Route::delete('logout', 'SessionsController@destroy')->name('logout');

 Route::get('signup/comfirm/{token}','UsersController@confirmEmail')->name('confirm_email');


 Route::get('password/reset', 'PasswordController@showLinkRequestForm')->name('password.request');
 Route::post('password/email', 'PasswordController@sendResetLinkEmail')->name('password.email');
 Route::get('password/reset/{token}', 'PasswordController@showResetForm')->name('password.reset');
 Route::post('password/reset', 'PasswordController@reset')->name('password.update');

 Route::resource('statuses', 'StatusesController', ['only'=>['store', 'destroy']]);


 Route::get('/users/{user}/followings', 'UsersController@followings')->name('users.followings');
 Route::get('/users/{user}/followers', 'UsersController@followers')->name('users.followers');

 Route::Post('/followers/{user}', 'FollowersController@store')->name('followers.store');
 Route::delete('/followers/{user}', 'FollowersController@destroy')->name('followers.destroy');

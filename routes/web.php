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

Route::get('/','HomeController@index')->name('home');
Route::post('/result','HomeController@process')->name('result');

Route::get('/payment','PaymentController@index')->name('home.payment');
Route::post('/payment/result','PaymentController@process')->name('result.payment');

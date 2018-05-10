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

Route::get('reset_password/{token}', ['as' => 'password.reset', function($token)
{
    // implement your reset password route here!
}]);

Route::get('photo/{id}/owner/{owner}/name/{name}/phone/{phone}/rhname/{rhname}/rhphone/{rhphone}/sharing/{sharing}/{token}','LinkController@link')->name('photo.link');
Route::get('photo/{id}/owner/{owner}/name/{name}/phone/{phone}/rhname/{rhname}/rhphone/{rhphone}/sharing/{sharing}/{token}/{response}','LinkController@response')->name('photo.link.response');


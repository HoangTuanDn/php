<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/',function (){
    echo "app is running";
});

Route::group(['prefix'=> 'api/img'], function() {
    Route::get('clone/{id:[a-zA-Z0-9]+}','CloneImgController@index');
    Route::post('/upload/{id:[a-zA-Z0-9]+}','UploadImgController@image');

});


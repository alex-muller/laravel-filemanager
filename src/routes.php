<?php

$namespace = 'AlexMuller\Filemanager\Controllers';

Route::namespace($namespace)->prefix('amfm')->middleware('web')->name('amfm.')->group(function (){
    Route::get('/', 'IndexController@index');
    Route::get('/items', 'ItemsController@getItems')->name('get-items');
    Route::get('/{item_name}', 'ItemsController@getItem')->where('item_name', '.*');
    Route::post('/create-directory', 'ItemsController@createDirectory')->name('create-directory');
    Route::post('/upload-file', 'ItemsController@upload')->name('upload-file');
    Route::post('/remove', 'ItemsController@remove')->name('remove');
});

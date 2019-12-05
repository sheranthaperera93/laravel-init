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

//Unauthorized Routes
Route::group([
    'middleware' => ['cors']
], function(){
    //Login
    Route::post('login', 'Auth\LoginController@authenticate');

    //Testing Functions
    Route::get('test', 'UsersController@testFunction');
});

//Authorized Routes
Route::group([
    'middleware' => ['jwt.auth', 'cors'],
], function(){
    //Logout
    Route::get('logout', 'Auth\LoginController@logout');
    
    //Routing for Users 
    Route::get('getauthuser', 'Auth\LoginController@me');
    Route::get('users', 'UsersController@index');
    Route::get('users/{offset}/{length}/{sortingCol}/{sortingDir}/{name}/{email}/{roleCode}', 'UsersController@getUsers');
    Route::post('users', 'UsersController@createNewUser');
    Route::put('users/{id}', 'UsersController@updateUser');
    Route::delete('users/{id}', 'UsersController@deleteUser');
    
    //Routing for Roles
    Route::get('roles', 'RolesController@index');
    Route::get('roles/{id}', 'RolesController@getPermissionByRoleId');
    Route::get('roles/{offset}/{length}/{sortingCol}/{sortingDir}/{roleName}', 'RolesController@getRolesPaged');
    Route::post('roles', 'RolesController@createRole');
    Route::put('roles/{id}', 'RolesController@updateRole');
    Route::delete('roles/{id}', 'RolesController@deleteRole');

    //Routing for permissions
    Route::get('permissions', 'PermissionsController@index');
    Route::get('permissions/{id}', 'PermissionsController@getPermissionById');
    Route::post('permissions', 'PermissionsController@createPermission');
    Route::put('permissions/{id}', 'PermissionsController@updatePermission');
    Route::delete('permissions/{id}', 'PermissionsController@deletePermission');

});

<?php

Route::group([
    'prefix' => config('develuserroles.slug'),
], function() {
    /**
     * Devel\Models\Auth\Role CRUD
     */
    Route::group([], function () {
        Route::get('/', [
            'as' => 'develuserroles.roles.index',
            'uses' => 'RolesController@index',
            'dashboardSidebar' => 'Manage Users->' . config('develuserroles.display_name'),
            'permissions' => 'user_roles.browse',
        ]);

        Route::get('/list', [
            'as' => 'develuserroles.roles.get',
            'uses' => 'RolesController@get',
            'permissions' => 'user_roles.browse',
        ]);

        Route::get('/add', [
            'as' => 'develuserroles.roles.create',
            'uses' => 'RolesController@create',
            'permissions' => 'user_roles.add',
        ]);

        Route::get('/{id}/edit', [
            'as' => 'develuserroles.roles.edit',
            'uses' => 'RolesController@edit',
            'permissions' => 'user_roles.view || user_roles.edit',
        ]);

        Route::post('/', [
            'as' => 'develuserroles.roles.store',
            'uses' => 'RolesController@store',
            'permissions' => 'user_roles.add',
        ]);

        Route::post('/{id}', [
            'as' => 'develuserroles.roles.update',
            'uses' => 'RolesController@update',
            'permissions' => 'user_roles.edit',
        ]);

        Route::delete('/{id}', [
            'as' => 'develuserroles.roles.destroy',
            'uses' => 'RolesController@destroy',
            'permissions' => 'user_roles.delete',
        ]);
    });
});

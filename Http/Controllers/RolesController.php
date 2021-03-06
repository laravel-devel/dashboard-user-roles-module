<?php

namespace Modules\DevelUserRoles\Http\Controllers;

use Modules\DevelDashboard\Traits\Crud;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Devel\Models\Auth\Permission;
use Devel\Models\Auth\Role;
use Devel\Http\Controllers\Controller;

class RolesController extends Controller
{
    use Crud;

    public function __construct()
    {
        $this->setMeta('title', 'Dashboard');
        $this->setMeta('title', config('develuserroles.display_name'));

        // CRUD setup
        $this->setModel('Devel\Models\Auth\Role');
        $this->setRequest('Modules\DevelUserRoles\Http\Requests\RoleRequest');

        $this->setDatatable([
            'key' => [
                'name' => 'Key',
                'sortable' => true,
            ],
            'name' => [
                'name' => 'Name',
                'sortable' => true,
            ],
            'default' => [
                'name' => 'Default',
                'sortable' => true,
                'format' => "value ? 'yes' : '-'",
            ],
        ], [
            'delete' => ['dashboard.develuserroles.roles.destroy', ':key'],
            'create' => ['dashboard.develuserroles.roles.create'],
            'edit' => ['dashboard.develuserroles.roles.edit', ':key'],
        ]);

        if (request('id')) {
            $role = Role::find(request('id'));
        }

        $this->setForm([
            'Main' => [
                [
                    'type' => 'text',
                    'name' => 'key',
                    'label' => 'Key',
                    'attrs' => [
                        'disabled' => !empty(request('id')),
                    ],
                ],
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'Name',
                ],
                [
                    'type' => 'checkbox',
                    'name' => 'default',
                    'label' => 'Default',
                    'attrs' => [
                        'disabled' => !empty(request('id')) && $role->default,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('develuserroles::dashboard.index', [
            'fields' => $this->datatable(),
            'actions' => $this->actions(),
            'permissions' => $this->permissions(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->setMeta('title', 'Add');

        return view('develuserroles::dashboard.create', [
            'form' => $this->form(),
            'permissions' => $this->getPermissions(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param mixed $id
     * @return Response
     */
    public function edit($id)
    {
        $item = $this->model()::findOrFail($id);

        $this->setMeta('title', 'Edit');

        return view('develuserroles::dashboard.edit', [
            'item' => $item,
            'form' => $this->form(),
            'permissions' => $this->getPermissions($item),
        ]);
    }

    /**
     * Determine whether an item can be deleted.
     *
     * @param Request $request
     * @param mixed $item
     * @return mixed
     */
    protected function canBeDeleted($request, $item)
    {
        if ($item->default) {
            return 'The default role cannot be deleted!';
        }

        if ($item->key === 'root') {
            return 'The root role cannot be deleted!';
        }

        return true;
    }

    /**
     * Alter the values before storing or updating an item.
     *
     * @param Request $request
     * @param array $values
     * @param mixed $item
     * @return array
     */
    protected function alterValues($request, array $values, $item = null): array
    {
        // The root role's permissions cannot be altered
        if ($item && $item->key === 'root') {
            $request->request->remove('permissions');
        }

        if ($item) {
            // The key of an existing role cannot be updated
            unset($values['key']);

            // The default role cannot be made non-default, because there will
            // be no default roles
            if ($item->default) {
                $values['default'] = true;
            }
        }

        return $values;
    }

    /**
     * Get all existing grouped permissions, mark granted ones for a role.
     *
     * @param Role $role
     * @return array
     */
    protected function getPermissions(Role $role = null): array
    {
        $permissions = Permission::getGrouped();

        if ($role) {
            $rolePermissions = $role->permissions;

            foreach ($rolePermissions as $permission) {
                $group = explode('.', $permission->key)[0];

                if (!$permissions[$group]) {
                    continue;
                }

                $index = array_search($permission->toArray(), $permissions[$group]['permissions']);

                if ($index !== false) {
                    $permissions[$group]['permissions'][$index]['granted'] = true;
                }
            }
        }

        return $permissions;
    }
}

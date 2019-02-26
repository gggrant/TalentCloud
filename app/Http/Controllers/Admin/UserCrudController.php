<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Models\UserRole;

class UserCrudController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel("App\Models\User");
        $this->crud->setRoute("admin/user");
        $this->crud->setEntityNameStrings('user', 'users');

        $this->crud->denyAccess('create');
        $this->crud->denyAccess('delete');

        $this->crud->addColumn([
            'name' => 'name',
            'type' => 'text',
            'label' => 'Name'
        ]);
        $this->crud->addColumn([
            'name' => 'email',
            'type' => 'text',
            'label' => 'Email'
        ]);
        $this->crud->addColumn([
            'name' => 'user_role.name',
            'type' => 'text',
            'label' => 'Role'
        ]);
        $this->crud->addColumn([
            'name' => 'applicant.priority',
            'type' => 'check',
            'label' => 'Priority'
        ]);

        $this->crud->addFilter([
            'name' => 'user_role',
            'type' => 'select2',
            'label' => 'Role'
        ], function () {
            return UserRole::all()->keyBy('id')->pluck('name', 'id')->toArray();
        }, function ($value) : void {
            $this->crud->addClause('where', 'user_role_id', $value);
        });

        $this->crud->addField([
            'name' => 'name',
            'label' => "Name",
            'type' => 'text',
            'attributes' => [
                'readonly'=>'readonly'
            ]
        ]);

        // The toggle field doesn't seem to allow running a callback within it's
        // options and hide_when parameters so we need to prep the data here.
        // This creates a couple of arrays to populate a radio button field with the
        // existing saved User Roles in the database, as well as set which option will show
        // or hide the following Priority field.
        $userRoles = UserRole::all()->keyBy('id')->pluck('name', 'id')->toArray();
        $userRolesToggle = UserRole::where('name', '!=', 'applicant')->pluck('id')->toArray();
        $userRolesToggle = array_fill_keys($userRolesToggle, ['applicant.priority']);

        $this->crud->addField([
            'label' => 'Role',
            'name' => 'user_role_id',
            'type' => 'toggle',
            'inline' => true,
            'options' => $userRoles,
            'hide_when' => $userRolesToggle,
            'default' => $this->crud->getCurrentEntry() ? $this->crud->getCurrentEntry()->user_role_id : 0,
        ]);
        $this->crud->addField([
            'name' => 'applicant.priority',
            'label' => 'Priority',
            'type' => 'checkbox'
        ]);
    }

    public function update($request)
    {
        $response = parent::updateCrud();
        return $response;
    }
}

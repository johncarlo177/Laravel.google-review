<?php

namespace App\Http\Controllers;

use App\Interfaces\ModelSearchBuilder;
use App\Models\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index(ModelSearchBuilder $search, Request $request)
    {
        return $search->init(Role::class, $request)
            ->inColumn('name')
            ->withQuery(function ($query) {
                if (request()->user()->isReseller()) {
                    $query->where('name', 'Client');
                }
            })
            ->search()
            ->paginate(fn(Role $role) => $role->resource());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        return $role->resource();
    }

    public function store(Request $request)
    {
        $role = new Role;

        $role->name = $request->input('name');

        $role->home_page = $request->input('home_page');

        if ($role->isReadOnly()) {
            abort(401);
        }

        $role->save();

        $role->setPermssions($request->input('permission_ids'));

        return $role;
    }

    public function update(Role $role, Request $request)
    {
        if ($role->isReadOnly()) {
            abort(401);
        }

        $role->name = $request->input('name');

        $role->home_page = $request->input('home_page');

        $role->save();

        $role->setPermssions($request->input('permission_ids'));


        return $role;
    }

    public function destroy(Role $role)
    {
        if ($role->isReadOnly()) {
            abort(401);
        }

        $role->delete();
    }
}

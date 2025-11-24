<?php

namespace App\Http\Controllers;

use App\Models\Permission;

class PermissionsController extends Controller
{
    public function index()
    {
        return Permission::get();
    }
}

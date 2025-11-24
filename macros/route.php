<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

Route::macro('crud', function ($route, $class, $modelClass, $only = ['index', 'show', 'store', 'update', 'destroy'], $except = [], $paramName = null) {

    if (!$paramName) {
        $paramName = lcfirst(
            Str::studly(
                Str::singular(
                    str_replace('/', '', $route)
                )
            )
        );
    }

    $singularRoute = $route . '/{' . $paramName . '}';

    $actions = [
        'index'     => [
            'method' => 'get',
            'route' => $route,
            'action' => 'index',
        ],
        'show'      => [
            'method' => 'get',
            'route' => $singularRoute,
            'action' => 'show'
        ],
        'store'     => [
            'method' => 'post',
            'route' => $route,
            'action' => 'store'
        ],
        'update'    => [
            'method' => 'put',
            'route' => $singularRoute,
            'action' => 'update'
        ],
        'destroy'   => [
            'method' => 'delete',
            'route' => $singularRoute,
            'action' => 'destroy'
        ]
    ];


    $middlewares = [
        'index'     => 'list',
        'show'      => 'show',
        'store'     => 'store',
        'update'    => 'update',
        'destroy'   => 'destroy'
    ];

    return Route::group([], function () use (
        $only,
        $actions,
        $class,
        $middlewares,
        $paramName,
        $except,
        $modelClass
    ) {

        if (!empty($except)) {
            $except = collect($except);

            $only = collect($only)->filter(fn ($key) => !$except->first(fn ($k) => $k === $key))->all();
        }

        foreach ($only as $item) {
            $action = $actions[$item];

            $bindActionsWithClass = collect(['index', 'store']);

            $canBinding = ',' . $paramName;

            if ($bindActionsWithClass->first(fn ($item) => $item == $action['action'])) {
                $canBinding = ',' . $modelClass;
            }

            $canMiddleware = 'can:' . $middlewares[$action['action']] . $canBinding;

            Route::{$action['method']}(
                $action['route'],
                [$class, $action['action']]
            )
                ->middleware($canMiddleware);
        }
    });
});

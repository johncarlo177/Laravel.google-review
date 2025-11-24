<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Permission;


use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $permissions = $this->generatePermissions(
            'QRCode',
            $this->methods(
                additionalActions: [
                    'archive',
                    'archive-any',
                    'showStats',
                    'showStats-any',
                    'change-user',
                    'pin-code',
                ],
            ),
            displayName: 'QRCode',
            kebabName: 'qrcode',
        );

        $this->save($permissions);

        $permissions = $this->generatePermissions(
            'User',
            $this->methods(
                filterPattern: '/any|all|store/',
                additionalActions: [
                    'invite',
                    'change-any-account-balance',
                    'get-account-balance'
                ]
            ),
        );

        $this->save(
            $permissions
        );

        $permissions = $this->generatePermissions(
            'SubscriptionPlan',
            $this->methods(
                filterPattern: '/any|all|store/'
            )
        );

        $this->save(
            $permissions
        );

        $this->save(
            $this->generatePermissions(
                'Subscription',
                methods: $this->methods()
            )
        );

        $this->save(
            $this->generatePermissions(
                'Transaction',
                methods: $this->methods(
                    only: ['list-all'],
                    additionalActions: ['approve', 'reject']
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                'PaymentGateway',
                methods: $this->methods(
                    only: ['list-all', 'show-any', 'update-any']
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                'File',
                methods: $this->methods(
                    only: ['show', 'show-any', 'destroy', 'destroy-any']
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'BlogPost',
                methods: $this->methods()
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'System',
                methods: $this->methods(
                    only: [
                        'status',
                        'settings',
                        'logs',
                        'cache',
                        'notifications',
                        'sms-portals',
                        'auth-workflow'
                    ]
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'Translation',
                methods: $this->methods()
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'Role',
                methods: $this->methods(
                    only: ['list-all']
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'Currency',
                methods: $this->methods(
                    additionalActions: ['enable']
                ),
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'CustomCode',
                methods: $this->methods(),
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'Page',
                methods: $this->methods(),
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'Domain',
                methods: $this->methods(
                    additionalActions: [
                        'add',
                        'updateStatus',
                        'upadteStatus-any',
                        'setDefault'
                    ]
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'QRCodeRedirect',
                methods: $this->methods(
                    only: [
                        'update',
                        'update-any'
                    ]
                ),
                kebabName: 'qrcode-redirect'
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'Folder',
                methods: $this->methods(
                    only: [
                        'list',
                        'show',
                        'store',
                        'update',
                        'destroy',
                    ]
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'LeadForm',
                methods: $this->methods(
                    only: [
                        'list',
                        'show',
                        'store',
                        'update'
                    ]
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'Plugins',
                methods: $this->methods(
                    only: [
                        'manage',
                        'settings'
                    ]
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'Affiliate',
                methods: $this->methods(
                    only: [
                        'manage',
                        'refer'
                    ]
                )
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'QRCodeTemplate',
                methods: $this->methods(
                    only: [
                        'list',
                        'manage',
                        'manage-all',
                        'use',
                    ]
                ),
                kebabName: 'qrcode-template'
            )
        );

        $this->save(
            $this->generatePermissions(
                name: 'TemplateCategory',
                methods: $this->methods(),
                kebabName: 'template-category'
            )
        );
    }

    public function methods($additionalActions = [], $only = null, $filterPattern = null, $excludePattern = null)
    {
        $methods = collect(
            [
                'list',
                'list-all',
                'show',
                'show-any',
                'store',
                'update',
                'update-any',
                'destroy',
                'destroy-any',
            ]
        );

        if ($only) {
            $methods = collect($only);
        }

        if ($filterPattern) {
            $methods = $methods->filter(fn($m) => preg_match($filterPattern, $m));
        }

        if ($excludePattern) {
            $methods = $methods->filter(fn($m) => !preg_match($excludePattern, $m));
        }

        $methods = $methods->merge(
            $additionalActions
        );

        return $methods;
    }

    public function generatePermissions(
        $name,
        $methods,
        $kebabName = null,
        $displayName = null
    ) {
        $kebabName = $kebabName ?: Str::kebab($name);

        $displayName = Str::title(Str::replace('-', ' ', $kebabName));

        return $methods->map(fn($m) => [
            'name' => Str::title(Str::replace('-', ' ', $m)) . ' ' . $displayName,
            'slug' => $kebabName . '.' . $m
        ])->all();
    }

    public function save(array $permissions)
    {
        foreach ($permissions as $permission) {
            $model = Permission::where('slug', $permission['slug'])->first();

            if (!$model)
                $model = new Permission();

            $model->forceFill($permission);
            $model->save();
        }
    }
}

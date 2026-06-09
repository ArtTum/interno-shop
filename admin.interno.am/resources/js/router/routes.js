const routes = [
    {
        path: '/',
        redirect: '/recommendations',
    },
    {
        path: '/shop-frontend',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/shopFrontend/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'languages',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/shop-categories',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/shopCategories/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_categories',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/shopCategories/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_categories',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id/:languageId?',
                component: () => import('@pages/shopCategories/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_categories',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/settings',
        redirect: '/settings/languages',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: 'languages',
                meta: {
                    requiresAuth: true,
                },
                children: [
                    {
                        path: '',
                        component: () => import('@pages/settings/global/languages/index.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'languages',
                            permission_type: 'can_view',
                        },
                    },
                    {
                        path: 'create',
                        component: () => import('@pages/settings/global/languages/create.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'languages',
                            permission_type: 'can_add',
                        },
                    },
                    {
                        path: 'update/:code',
                        component: () => import('@pages/settings/global/languages/update.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'languages',
                            permission_type: 'can_view',
                        },
                    },
                ],
            },
        ],
    },
    {
        path: '/notes',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/notes/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'notes',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/notes/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'notes',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/notes/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'notes',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/trash',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/trash/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'trash',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/recommendations',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/recommendations/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'recommendations',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/recommendations/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'recommendations',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/recommendations/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'recommendations',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/incomings',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/incomings/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'incomings',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/incomings/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'incomings',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/incomings/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'incomings',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/hospitals-bases',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/hospitalsBases/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'hospitals_bases',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/hospitalsBases/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'hospitals_bases',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/hospitalsBases/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'hospitals_bases',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/hospitals',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/hospitals/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'hospitals',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/hospitals/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'hospitals',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/hospitals/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'hospitals',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/sms-shablons',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/smsShablons/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'sms_shablons',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/smsShablons/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'sms_shablons',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/smsShablons/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'sms_shablons',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/sms-histories',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/smsHistories/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'sms_histories',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/diseases',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/diseases/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'diseases',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/diseases/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'diseases',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/diseases/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'diseases',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/doctors-finals',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/doctorsFinals/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'doctors_finals',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/doctorsFinals/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'doctors_finals',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/doctorsFinals/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'doctors_finals',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/extended-prices',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/extendedPrices/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'extended_prices',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/extendedPrices/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'extended_prices',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/extendedPrices/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'extended_prices',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/clinics',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/clinics/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'clinics',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/clinics/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'clinics',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/clinics/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'clinics',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/sms-bazas',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/smsBazas/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'sms_bazas',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/smsBazas/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'sms_bazas',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/smsBazas/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'sms_bazas',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/outgoings',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/outgoings/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'outgoings',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/outgoings/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'outgoings',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/outgoings/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'outgoings',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/subscribes',
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: '',
                component: () => import('@pages/subscribes/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'subscribes',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/subscribes/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'subscribes',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/subscribes/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'subscribes',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/users',
        redirect: 'users/list',
        children: [
            {
                path: 'list',
                meta: {
                    requiresAuth: true,
                },
                children: [
                    {
                        path: '',
                        component: () => import('@pages/users/list/index.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'users',
                            permission_type: 'can_view',
                        },
                    },
                    {
                        path: 'create',
                        component: () => import('@pages/users/list/create.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'users',
                            permission_type: 'can_add',
                        },
                    },
                    {
                        path: 'update/:id',
                        component: () => import('@pages/users/list/update.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'users',
                            permission_type: 'can_view',
                        },
                    },
                ],
            },
            {
                path: 'user-groups',
                meta: {
                    requiresAuth: true,
                },
                children: [
                    {
                        path: '',
                        component: () => import('@pages/users/userGroups/index.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'users_groups',
                            permission_type: 'can_view',
                        },
                    },
                    {
                        path: 'create',
                        component: () => import('@pages/users/userGroups/create.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'users_groups',
                            permission_type: 'can_add',
                        },
                    },
                    {
                        path: 'update/:id',
                        component: () => import('@pages/users/userGroups/update.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'users_groups',
                            permission_type: 'can_view',
                        },
                    },
                ],
            },
            {
                path: 'permissions',
                meta: {
                    requiresAuth: true,
                },
                children: [
                    {
                        path: '',
                        component: () => import('@pages/users/permissions/index.vue'),
                        meta: {
                            requiresAuth: true,
                            permission_name: 'permissions',
                            permission_type: 'can_view',
                        },
                    },
                ],
            },
        ],
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@pages/auth/Login.vue'),
        meta: {
            requiresGuest: true,
        },
    },
    {
        path: '/:pathMatch(.*)*',
        component: () => import('@pages/NotFound.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/forbidden',
        component: () => import('@pages/NotFound.vue'),
        meta: {
            requiresAuth: true,
            permission_name: 'forbidden',
        },
    },
];

export default routes;

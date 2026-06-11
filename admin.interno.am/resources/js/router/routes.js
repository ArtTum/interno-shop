const routes = [
    {
        path: '/',
        redirect: '/shop-contact',
    },
    {
        path: '/shop-contact',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/shopContact/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'languages',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/shop-privacy',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/shopPrivacy/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'languages',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/shop-social',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/shopSocial/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'languages',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/shop-orders',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/shopOrders/index.vue'),
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
        path: '/shop-products',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/shopProducts/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_products',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/shopProducts/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_products',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id/:languageId?',
                component: () => import('@pages/shopProducts/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_products',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/shop-product-option-types',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/shopProductOptionTypes/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_product_option_types',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/shopProductOptionTypes/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_product_option_types',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/shopProductOptionTypes/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_product_option_types',
                    permission_type: 'can_view',
                },
            },
        ],
    },
    {
        path: '/shop-product-colors',
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('@pages/shopProductColors/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_product_colors',
                    permission_type: 'can_view',
                },
            },
            {
                path: 'create',
                component: () => import('@pages/shopProductColors/create.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_product_colors',
                    permission_type: 'can_add',
                },
            },
            {
                path: 'update/:id',
                component: () => import('@pages/shopProductColors/update.vue'),
                meta: {
                    requiresAuth: true,
                    permission_name: 'shop_product_colors',
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

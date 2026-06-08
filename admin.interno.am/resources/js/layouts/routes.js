export const routes = [
    {
        menuItems: [
            {
                icon: ['far', 'calendar-check'],
                label: 'Հերթագրումներ',
                name: 'recommendations',
                route: '/recommendations',
            },
            {
                icon: ['far', 'users'],
                label: 'Հիվանդների բազա',
                name: 'hospitals_bases',
                route: '/hospitals-bases',
            },
            {
                icon: ['far', 'money-bill-trend-up'],
                label: 'Եկամուտներ',
                name: 'incomings',
                route: '/incomings',
            },
            {
                icon: ['far', 'comment-sms'],
                label: 'SMS Բազա',
                name: 'sms_bazas',
                route: '/sms-bazas',
            },
            {
                icon: ['far', 'hospital'],
                label: 'Կապ հիվանդանոցների',
                name: 'clinics',
                route: '/clinics',
            },
            {
                icon: ['far', 'tags'],
                label: 'Տարածված գներ',
                name: 'extended_prices',
                route: '/extended-prices',
            },
            {
                icon: ['far', 'user-doctor'],
                label: 'Բժիշկներ վերջնական',
                name: 'doctors_finals',
                route: '/doctors-finals',
            },
            {
                icon: ['far', 'file-lines'],
                label: 'SMS շաբլոններ',
                name: 'sms_shablons',
                route: '/sms-shablons',
            },
            {
                icon: ['far', 'paper-plane'],
                label: 'Ուղարկված SMS-ներ',
                name: 'sms_histories',
                route: '/sms-histories',
            },
            {
                icon: ['far', 'disease'],
                label: 'Հիվանդություններ',
                name: 'diseases',
                route: '/diseases',
            },
            {
                icon: ['far', 'house-medical'],
                label: 'Հիվանդանոցներ',
                name: 'hospitals',
                route: '/hospitals',
            },
            {
                icon: ['far', 'user-tie'],
                label: 'Օգտատերեր',
                name: 'users_menu_link',
                route: '/users/list',
                children: [
                    {label: '- Օգտատերեր', route: '/users/list', name: 'users'},
                    {label: '- Groups', route: '/users/user-groups', name: 'users_groups'},
                    {label: '- Permissions', route: '/users/permissions', name: 'permissions'},
                ]
            },
            // {
            //     icon: ['fab', 'telegram'],
            //     label: 'subscribes',
            //     name: 'subscribes',
            //     route: '/subscribes',
            // },
            {
                icon: ['far', 'money-bill-wave'],
                label: 'Ծախսեր',
                name: 'outgoings',
                route: '/outgoings',
            },
            {
                icon: ['far', 'note-sticky'],
                label: 'Նոթեր',
                name: 'notes',
                route: '/notes',
            },
            {
                icon: ['far', 'trash-can'],
                label: 'Ջնջվածներ',
                name: 'trash',
                route: '/trash',
            },
    ]

        //     {
        //         icon: ['fas', 'fa-gauge'],
        //         label: 'Dashboard',
        //         name: 'dashboard',
        //         route: '/dashboard',
        //         children: [
        //             {label: '- Dashboard', route: '/dashboard', name: 'dashboard'},
        //             {label: '- Revenue', route: '/dashboard/revenue', name: 'revenue'},
        //         ]
        //     },
        //     {
        //         icon: ['far', 'bags-shopping'],
        //         label: 'Orders',
        //         name: 'orders',
        //         route: '/orders',
        //         children: [
        //             {label: '- All', route: '/orders', name: 'orders'},
        //             {label: '- Completed', route: '/orders?status=4', name: 'orders'},
        //             {label: '- Processing', route: '/orders?status=2', name: 'orders'},
        //             {label: '- Cancelled', route: '/orders?status=5', name: 'orders'},
        //             {label: '- Refunded', route: '/orders?status=6', name: 'orders'},
        //             {label: '- Reshipment', route: '/orders?reshipment=2', name: 'orders'},
        //             {label: '- Dispute', route: '/orders?dispute=99', name: 'orders'},
        //             {label: '- Archive', route: '/orders?only_actuals=0', name: 'orders'},
        //             {label: '- Trash', route: '/orders?status=8', name: 'orders'},
        //         ]
        //     },
        //     {
        //         icon: 'list',
        //         label: 'Catalog',
        //         name: 'catalog_menu_link',
        //         route: '/catalog/products',
        //         children: [
        //             {label: '- Products', route: '/catalog/products', name: 'products'},
        //             {label: '- Categories', route: '/catalog/categories', name: 'categories'},
        //             {label: '- Attributes', route: '/catalog/attribute-types', name: 'attribute_types'},
        //             {label: '- Attribute values', route: '/catalog/attributes', name: 'attributes'},
        //         ]
        //     },
        //     {
        //         icon: 'arrows-to-circle',
        //         label: 'Contents',
        //         name: 'contents_menu_link',
        //         route: '/contents/pages',
        //         children: [
        //             {label: '- Menu', route: '/contents/menus', name: 'menus'},
        //             {label: '- Pages', route: '/contents/pages', name: 'pages'},
        //             {label: "- Post's categories", route: '/contents/post-categories', name: 'posts'},
        //             {label: '- Posts', route: '/contents/posts', name: 'posts'},
        //             {label: '- A-plus content', route: '/contents/a-plus-contents', name: 'a_plus_contents'},
        //         ]
        //     },
        //     {
        //         icon: ['far', 'store'],
        //         label: 'Marketplaces',
        //         name: 'marketplaces_menu_link',
        //         route: '/marketplaces',
        //         children: [
        //             {
        //                 label: '- Orders',
        //                 route: '/marketplaces/orders',
        //                 name: 'marketplace_orders'
        //             },
        //             {
        //                 label: '- Products',
        //                 route: '/marketplaces/products',
        //                 name: 'marketplace_products'
        //             },
        //             {
        //                 label: '- Users',
        //                 route: '/marketplaces/users',
        //                 name: 'marketplace_users'
        //             },
        //             {
        //                 label: '- Marketplaces settings',
        //                 route: '/marketplaces/marketplace-settings',
        //                 name: 'marketplace_settings'
        //             },
        //         ]
        //     },
        //     {
        //         icon: 'bullhorn',
        //         label: 'Leads',
        //         name: 'leads_menu_link',
        //         route: '/leads/leads',
        //         children: [
        //             {label: '- Leads', route: '/leads/leads', name: 'leads'},
        //             {label: '- Projects', route: '/leads/projects', name: 'leads'},
        //             {label: '- Providers', route: '/leads/providers', name: 'providers'},
        //             {label: '- Settings', route: '/leads/lead-settings', name: 'leads'},
        //         ]
        //     },
        //
        //     {
        //         icon: 'users-line',
        //         label: 'CRM',
        //         name: 'crm_menu_link',
        //         route: '/crm/customers',
        //         children: [
        //             {label: '- Customers', route: '/crm/customers', name: 'customers'},
        //             {label: '- Segments', route: '/crm/segments', name: 'customers_segments'},
        //             {label: '- Reviews', route: '/crm/reviews', name: 'reviews'},
        //         ]
        //     },
        //
        //     {
        //         icon: 'receipt',
        //         label: 'Accounting',
        //         name: 'accounting_menu_link',
        //         route: '/accounting_menu_link/files',
        //         children: [
        //             {
        //                 label: '- Bank transfer processing',
        //                 route: '/accounting/bank-transfer-processing',
        //                 name: 'bank_transfer_processing'
        //             },
        //             {label: '- Tax exporter', route: '/accounting/files', name: 'accounting_files'},
        //             {label: '- Tax exporter Settings', route: '/accounting/settings', name: 'accounting_settings'},
        //             {label: '- Sales by HS Codes', route: '/accounting/hs-codes-sales', name: 'accounting_settings'},
        //         ]
        //     },
        //     {
        //         icon: 'warehouse',
        //         label: 'Warehouse',
        //         name: 'warehouse_menu_link',
        //         route: '/warehouse/items',
        //         children: [
        //             {label: '- Items', route: '/warehouse/items', name: 'items'},
        //         ]
        //     },
        //     {
        //         icon: ['far', 'chart-pie'],
        //         label: 'Reports',
        //         route: '/reports/analytics',
        //         name: 'reports_menu_link',
        //         children: [
        //             {label: 'Analytics', route: '/reports/analytics', name: 'analytics'},
        //             {label: "Customer's reports", route: '/reports/customers', name: 'customers_reports'},
        //             {label: "Controlling", route: '/reports/controlling', name: 'controlling'},
        //         ]
        //     },
        //     {
        //         icon: 'comments-dollar',
        //         label: 'Marketing',
        //         name: 'marketing_menu_link',
        //         route: '/marketing/coupons',
        //         children: [
        //             {label: '- Coupons', route: '/marketing/coupons', name: 'coupons'},
        //             {label: "- My shared carts", route: '/marketing/my-shared-carts', name: 'my_shared_carts'},
        //             {label: '- Shared carts', route: '/marketing/shared-carts', name: 'shared_carts'},
        //             {
        //                 label: "- Shared cart's stats",
        //                 route: '/marketing/shared-carts-stats',
        //                 name: 'shared_carts_stats'
        //             },
        //             {label: "- My offers", route: '/marketing/my-offers', name: 'my_offers'},
        //             {label: "- Offers", route: '/marketing/offers', name: 'offers'},
        //             {label: "- Offer's stats", route: '/marketing/offers-stats', name: 'offers_stats'},
        //             {label: "- Loyalty programs", route: '/marketing/loyalty-programs', name: 'loyalty_programs'},
        //             {label: "- Abandoned emails", route: '/marketing/abandoned-emails', name: 'abandoned_emails'},
        //         ]
        //     },
        //     {
        //         icon: ['fab', 'telegram'],
        //         label: 'Newsletter',
        //         name: 'newsletter_menu_link',
        //         route: '/newsletter/campaigns',
        //         children: [
        //             {label: '- Campaigns', route: '/newsletter/campaigns', name: 'campaigns'},
        //             {label: '- Emails / Ads', route: '/newsletter/email-ads', name: 'email_ads'},
        //             {label: '- Customer segments', route: '/crm/segments', name: 'customers_segments'},
        //             {label: '- Reports', route: '/newsletter/email-ads-reports', name: 'email_ads_reports'},
        //             {label: '- Settings', route: '/settings/newsletter', name: 'newsletter_settings'},
        //             {label: '- Blacklist', route: '/newsletter/blacklists', name: 'newsletter_blacklists'},
        //         ]
        //     },
        //     {
        //         icon: ['fas', 'handshake'],
        //         label: 'Affiliate',
        //         name: 'affiliate_menu_link',
        //         route: '/affiliate/campaigns',
        //         children: [
        //             {label: '- Campaigns', route: '/affiliate/campaigns', name: 'affiliate_campaigns'},
        //             {label: '- Member groups', route: '/affiliate/member-groups', name: 'affiliate_member_groups'},
        //             {label: '- Members', route: '/affiliate/members', name: 'members'},
        //             {label: '- Programs', route: '/affiliate/programs', name: 'programs'},
        //             {label: '- Products', route: '/affiliate/products', name: 'affiliate_products'},
        //         ]
        //     },
        //     {
        //         icon: ['far', 'screwdriver-wrench'],
        //         label: 'Tools',
        //         name: 'tools_general_link',
        //         route: '/tools/uploads',
        //         children: [
        //             {label: '- Uploads', route: '/tools/file-uploads', name: 'uploads'},
        //             {label: '- Feeds', route: '/tools/feeds', name: 'feeds'},
        //             {label: '- Reminder email', route: '/tools/reminder-emails', name: 'reminder_emails'},
        //             {label: '- Calculators', route: '/tools/calculators', name: 'calculators'},
        //             {label: '- Shipping costs uploader', route: '/tools/shipping-costs-uploader', name: 'shipping_costs_uploader'},
        //             {label: '- Database restore', route: '/tools/database-restore', name: 'database_restore_tools'},
        //         ]
        //     },
        //     {
        //         icon: 'gear',
        //         label: 'Settings',
        //         name: 'settings_menu_link',
        //         route: '/settings/general',
        //         children: [
        //             {label: '- General', route: '/settings/general', name: 'general_settings'},
        //             {label: '- Shipping countries', route: '/settings/shipping-countries', name: 'store_countries'},
        //             {label: '- ZIP rules', route: '/settings/zip-rules', name: 'zip_rules'},
        //             {label: '- Media', route: '/settings/media-settings', name: 'media_settings'},
        //             {label: '- Tax', route: '/settings/tax', name: 'tax'},
        //             {label: '- Payment methods', route: '/settings/payment-methods', name: 'payment_methods'},
        //             {label: '- Shipping zones', route: '/settings/shipping-zones', name: 'shipping_zones'},
        //             {
        //                 label: '- Shipping label',
        //                 route: '/settings/shipping-label-settings',
        //                 name: 'shipping_label_settings'
        //             },
        //             {label: '- Spedition settings', route: '/settings/spedition-settings', name: 'spedition_settings'},
        //             {label: '- Socials', route: '/settings/socials', name: 'socials_settings'},
        //             {label: '- DGD', route: '/settings/dgd-settings', name: 'dgd_settings'},
        //             {label: '- Trustpilot', route: '/settings/trustpilot-settings', name: 'trustpilot_settings'},
        //             {label: '- Email', route: '/settings/email-settings', name: 'email_settings'},
        //             {label: '- Documents', route: '/settings/general/1/-1', name: 'document_settings_general'},
        //             {label: '- Currencies', route: '/settings/currencies', name: 'currencies'},
        //             {label: '- Languages', route: '/settings/languages', name: 'languages'},
        //             {label: '- Translations', route: '/settings/translations', name: 'translations'},
        //             {label: '- Permalinks', route: '/settings/permalinks', name: 'permalinks'},
        //             {label: '- Cookie settings', route: '/settings/cookie-settings', name: 'cookie_settings'},
        //             {label: '- Cookie scripts', route: '/settings/cookie-scripts', name: 'cookie_settings'},
        //             {
        //                 label: '- TNT Cons. Numbers',
        //                 route: '/settings/tnt-consignment-note-numbers',
        //                 name: 'tnt_consignment_note_numbers'
        //             },
        //             {label: '- Newsletter settings', route: '/settings/email_ads_reports', name: 'newsletter_settings'},
        //         ]
        //     },
        //     {
        //         icon: ['far', 'business-time'],
        //         label: 'B2B',
        //         name: 'b2b_menu_link',
        //         route: '/b2b/customer-groups',
        //         children: [
        //             {label: '- Customer\'s groups', route: '/b2b/customer-groups', name: 'customers_groups'},
        //         ]
        //     },
        // ],
    },
];

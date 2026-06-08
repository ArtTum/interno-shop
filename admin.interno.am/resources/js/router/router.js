import {createRouter, createWebHistory} from "vue-router";
import routes from "@router/routes.js";
import store from "@store";

const router = createRouter({
    history: createWebHistory(),
    linkActiveClass: 'active',
    routes
})

router.beforeEach(async (to, from, next) => {
    const isAuthenticated = store.getters['auth/isAuthenticated'];

    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login');
    } else if (to.meta.requiresGuest && isAuthenticated) {
        next('/');
    } else {
        if (to.meta.requiresGuest === undefined) {
            var auth = store.getters['auth/getUser'];

            if (!auth) {
                await store.dispatch('auth/fetchUser');
                auth = store.getters['auth/getUser'];
            }
        }

        if (auth && auth.superadmin) {
            next();
            return;
        }

        if (auth && auth.id == to.params.id && to.fullPath === '/users/list/update/' + to.params.id ) {
            next();
            return;
        }

        if (
            to.name !== 'login' &&
            to.meta.permission_name !== 'forbidden' &&
            (
                auth.user_group.permissions_by_name[to.meta.permission_name] === undefined ||
                !auth.user_group.permissions_by_name[to.meta.permission_name][0][to.meta.permission_type]
            )
        ) {
            router.push('/forbidden');
        }

        next();
    }
});

export default router;

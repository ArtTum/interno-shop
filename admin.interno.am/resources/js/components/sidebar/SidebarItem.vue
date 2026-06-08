<script setup>
import {useStore} from "vuex";

const store = useStore();

import {useRoute, useRouter} from 'vue-router'
import SidebarDropdown from '@components/sidebar/SidebarDropdown.vue'
import {computed} from "vue";

const page = computed(() => store.getters['sideBar/page']);
const auth = computed(() => store.getters['auth/getUser']);

const currentRoute = useRoute();
const currentPage = currentRoute.name
const router = useRouter();

const props = defineProps({
    item: Object,
})

const handleItemClick = () => {
    const pageName = store.getters['sideBar/page'] === props.item.label ? '' : props.item.label;
    store.dispatch('sideBar/setPage', pageName);

    if (props.item.children) {
        return props.item.children.some(child => store.getters['sideBar/selected'] === child.label);
    }

    // Only commit navTimestamp when clicking the same route (refresh), not when navigating away
    if (currentRoute.path === props.item.route) {
        store.commit('sideBar/UPDATE_NAV_TIMESTAMP');
    }

    router.push(props.item.route);
};
const isHovered = computed(() => store.getters['sideBar/isHovered']);
const isSidebarOpenMobile = computed(() => store.getters['sideBar/isSidebarOpen']);
const showFull = computed(() => isHovered.value || isSidebarOpenMobile.value);

</script>

<template>
    <template
        v-if="(auth && auth.superadmin) || (auth.user_group.permissions_by_name[item.name] && auth.user_group.permissions_by_name[item.name][0].can_view)">
        <li>
            <router-link
                style="height: 40px"
                :to="!item.children ? item.route : ''"
                @click.prevent="handleItemClick"
                :title="item.label"
                class="group cursor-pointer relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark"
                :class="{
                'bg-graydark': page === item.label,
                'justify-center px-0': !showFull,
            }"
            >
                <font-awesome-icon class="left-icon size-4.5" :icon="item.icon" v-if="item.icon"/>
                <span v-show="showFull" class="transition-opacity duration-300">
                    {{ item.label }}
                </span>
                <span
                    v-if="item.message && showFull"
                    class="absolute right-14 top-1/2 -translate-y-1/2 rounded bg-primary py-1 px-2.5 text-xs font-medium text-white"
                >
                {{ item.message }}
                </span>
                <font-awesome-icon
                    class="absolute right-4 top-1/2 -translate-y-1/2 fill-current"
                    :class="{ 'rotate-180':page === item.label }"
                    v-if="item.children && showFull"
                    icon="chevron-down"/>
            </router-link>

            <!-- Dropdown Menu Start -->

            <div v-if="showFull" class="translate transform overflow-hidden" v-show="page === item.label">
                <SidebarDropdown
                    v-if="item.children"
                    :items="item.children"
                    :currentPage="currentPage"
                    :page="item.label"
                />
                <!-- Dropdown Menu End -->
            </div>
        </li>
    </template>
</template>

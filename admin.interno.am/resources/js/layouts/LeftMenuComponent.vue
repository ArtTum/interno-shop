<script setup>
import {useStore} from "vuex";
import {computed, ref} from 'vue'
import SidebarItem from '@components/sidebar/SidebarItem.vue'
import { routes } from '@layouts/routes.js';

const store = useStore();
const menuGroups = ref(routes)
const isSidebarOpenMobile = computed(() => store.getters['sideBar/isSidebarOpen']);
const isHovered = computed(() => store.getters['sideBar/isHovered']);
const setSidebarState = (value) => {
    store.dispatch("sideBar/setSidebarState", value);
};

</script>

<template>
    <aside
        class="absolute left-0 top-0 z-9999 flex h-screen flex-col overflow-y-hidden bg-black duration-300 ease-linear lg:static lg:translate-x-0"
       :class="{
          'translate-x-0': isSidebarOpenMobile,
          '-translate-x-full': !isSidebarOpenMobile,
          'w-72.5': (isHovered || isSidebarOpenMobile),
          'w-20': (!isHovered && !isSidebarOpenMobile),
        }"
    >
        <!-- SIDEBAR HEADER -->
        <div style="background: white" class="flex items-center justify-center gap-2 px-2 py-4.5 lg:py-4.5">
            <router-link to="/" style="height: 31px" @click="store.commit('sideBar/UPDATE_NAV_TIMESTAMP')">
                <img
                    width="170px"
                    height="31px"
                    v-show="isHovered"
                    src="@assets/images/1logo.png"
                    alt="Logo"
                    class=" transition-all duration-300"
                />
                <img
                    width="31px"
                    height="31px"
                    v-show="!isHovered"
                    src="@assets/images/1logo.png"
                    alt="Logo"
                    class=" transition-all duration-300"
                />
            </router-link>

            <button class="block lg:hidden"  @click="store.dispatch('sideBar/toggleSidebar');">
                <font-awesome-icon icon="arrow-left" class="size-6" />
            </button>
        </div>
        <!-- SIDEBAR HEADER -->

        <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
            <!-- Sidebar Menu -->
            <nav class="mt-5 pb-4 pt-0 px-3 lg:mt-9">
                <template v-for="menuGroup in menuGroups" :key="menuGroup.name">
                    <div>
                        <ul class="mb-6 flex flex-col gap-1.5">
                            <template v-for="(menuItem, index) in menuGroup.menuItems" :key="index">
                                <SidebarItem
                                    :item="menuItem"
                                />
                            </template>
                        </ul>
                    </div>
                </template>
            </nav>
            <!-- Sidebar Menu -->
        </div>
    </aside>
</template>
<style>
* {
    font-size: 14px;
    th * {
        font-weight: bold !important;
    }
    td * {
        font-weight: bold !important;
    }
}
</style>

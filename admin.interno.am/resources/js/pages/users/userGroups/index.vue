<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import TableActions from "@components/global/TableActions.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";

import {computed, ref} from 'vue'

import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const store = useStore();
const route = useRoute();
const router = useRouter();

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 25,
    search: route.query.search || '',
    status: route.query.status === undefined ? -1 : Number(route.query.status),
    ordering_field: route.query.ordering_field || 'id',
    ordering_direction: route.query.ordering_direction || 'asc'
});

const updateQueryParams = async () => {
    await router.push({
        query: {
            ...route.query,
            ...params.value
        },
    });
};

const fetchPageData = async () => {
    await store.dispatch('userGroup/fetchPageData', params.value);
};

fetchPageData();

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await updateQueryParams();
    await fetchPageData();
};

const pageData = computed(() => store.getters['userGroup/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);

</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="User groups" :breadcrumb="[
            {path: '/dashboard', title: 'Dashboard'},
        ]"/>
        <TableActions
            :createRoute="auth.user_group.permissions_by_name.users_groups[0].can_add ? '/users/user-groups/create' : ''"
            :showFilter="false"
            @applyFilters="doPageFetching"
        />

        <CustomTable
            @do-page-fetching="doPageFetching"
            v-model="params"
            :main-search="{
                visibility: true,
                placeholder: 'Search ...',
                tooltip: {
                    button: {showingType: 'info'},
                    text: 'Name'
                }
            }"
            :pagination="pageData.pagination"
            :columns="[
              { title: 'ID', key: 'id' },
              { title: 'Name', key: 'name' },
              { title: 'Action' },
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">#{{ item.id }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.name }}</span>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="'user-groups/update/' + item.id">
                                <button
                                    class="hover:text-primary"
                                    title="Edit"
                                >
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>

                            <template v-if="auth.user_group.permissions_by_name.users_groups[0].can_delete">
                                <button
                                    @click="store.commit('userGroup/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: item.id
                                });"
                                    class="hover:text-primary"
                                    title="Delete"
                                >
                                    <font-awesome-icon :icon="['fas', 'trash-can']"/>
                                </button>
                            </template>

                        </div>
                    </td>
                </tr>
            </template>
        </CustomTable>

        <DeleteModal
            @fetch="fetchPageData()"
            action-variable="userGroup/delete"
            getter-variable="userGroup/getDeleteModelValue"
            mutation-variable="userGroup/SET_DELETE_MODAL_VALUE"
        />

    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';
</style>

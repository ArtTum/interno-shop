<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import TableActions from "@components/global/TableActions.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";

import {computed, ref, watch} from 'vue'
import {useDebouncedSearch} from '@/utils/useDebounce.js';

import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const store = useStore();
const route = useRoute();
const router = useRouter();

const getDefaultParams = () => ({
    page: 1,
    per_page: 200,
    search: '',
    status: -1,
    ordering_field: 'id',
    ordering_direction: 'desc',
});

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 200,
    search: route.query.search || '',
    status: route.query.status === undefined ? -1 : Number(route.query.status),
    ordering_field: route.query.ordering_field || 'id',
    ordering_direction: route.query.ordering_direction || 'desc'
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
    await store.dispatch('smsShablon/fetchPageData', params.value);
};

fetchPageData();

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await updateQueryParams();
    await fetchPageData();
};

const pageData = computed(() => store.getters['smsShablon/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);

// Auto-search when user types
useDebouncedSearch(() => params.value.search, () => doPageFetching(), 500);

// Reset filters when navigating fresh to this page (e.g. clicking menu link)
watch(() => route.query, (newQuery) => {
    if (Object.keys(newQuery).length === 0) {
        params.value = getDefaultParams();
        doPageFetching();
    }
}, { deep: true });

</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="SMS շաբլոններ" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
        ]"/>
        <TableActions
            :createRoute="(auth?.superadmin || auth?.user_group?.permissions_by_name.sms_shablons[0].can_add) ? '/sms-shablons/create' : null"
            :showFilter="false"
            @applyFilters="doPageFetching"
        />

        <CustomTable
            @do-page-fetching="doPageFetching"
            v-model="params"
            :main-search="{
                visibility: true,
                placeholder: 'Փնտրել...',
                tooltip: {
                    button: {showingType: 'info'},
                    text: 'Անուն'
                }
            }"
            :pagination="pageData.pagination"
            :columns="[
              { title: 'ID', key: 'id' },
              { title: 'Անունը', key: 'name' },
              { title: 'SMS', key: 'sms_text' },
              { title: 'Գործողություն' },
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
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[400px] break-words whitespace-normal font-medium text-black" v-html="item.sms_text"></span>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="'sms-shablons/update/' + item.id">
                                <button
                                    class="hover:text-primary"
                                    title="Edit"
                                >
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>


                                <button
                                    v-if="auth.user_group.permissions_by_name.sms_shablons[0].can_delete"
                                    @click="store.commit('smsShablon/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: item.id
                                });"
                                    class="hover:text-primary"
                                    title="Delete"
                                >
                                    <font-awesome-icon :icon="['fas', 'trash-can']"/>
                                </button>


                        </div>
                    </td>
                </tr>
            </template>
        </CustomTable>

        <DeleteModal
            @fetch="fetchPageData()"
            action-variable="smsShablon/delete"
            getter-variable="smsShablon/getDeleteModelValue"
            mutation-variable="smsShablon/SET_DELETE_MODAL_VALUE"
        />

    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';
</style>

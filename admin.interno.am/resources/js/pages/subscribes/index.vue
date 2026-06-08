<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import TableActions from "@components/global/TableActions.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, watch} from 'vue'
import {useDebouncedSearch} from '@/utils/useDebounce.js';

import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const store = useStore();
const route = useRoute();
const router = useRouter();

const now = new Date();

const getDefaultParams = () => ({
    page: 1,
    per_page: 200,
    search: '',
    status: -1,
    ordering_field: 'id',
    ordering_direction: 'desc',
    year: now.getFullYear(),
    month: now.getMonth() + 1,
});

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 200,
    search: route.query.search || '',
    status: route.query.status === undefined ? -1 : Number(route.query.status),
    ordering_field: route.query.ordering_field || 'id',
    ordering_direction: route.query.ordering_direction || 'desc',
    year: route.query.year ? Number(route.query.year) : now.getFullYear(),
    month: route.query.month ? Number(route.query.month) : now.getMonth() + 1,
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
    await store.dispatch('subscribe/fetchPageData', params.value);
};

const indexParams = computed(() => store.getters['subscribe/getIndexParams']);

const fetchPageParams = async () => {
    await store.dispatch('subscribe/fetchIndexParams');
};

fetchPageParams();
fetchPageData();

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await updateQueryParams();
    await fetchPageData();
};

const pageData = computed(() => store.getters['subscribe/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);

// Auto-search when user types (debounced)
useDebouncedSearch(() => params.value.search, () => {
    doPageFetching();
}, 500);

// Reset filters when navigating fresh to this page (e.g. clicking menu link)
watch(() => route.query, (newQuery) => {
    if (Object.keys(newQuery).length === 0) {
        params.value = getDefaultParams();
    }
}, { deep: true });

// Auto-filter when year/month changes
watch(() => [
    params.value.year,
    params.value.month,
], () => {
    doPageFetching();
}, { deep: true });

</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="ՀԵՐԹԱԳՐՎԱԾՆԵՐ doctor911.am միջոցով" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
        ]"/>
        <TableActions
            :createRoute="(auth?.superadmin || auth?.user_group?.permissions_by_name.subscribes[0].can_add) ? '/subscribes/create' : null"
            :showFilter="true"
            :filterMenuInitialValue="true"
            :hideApplyButton="true"
        >
            <div class="grid grid-cols-4 gap-4 p-4 max-xl:grid-cols-3 max-md:grid-cols-2 max-md:gap-4 max-md:p-4 max-sm:grid-cols-1 max-sm:p-1">
                <div class="flex flex-col">
                    <CustomSelect
                        v-model="params.year"
                        mode="single"
                        label="Տարի"
                        :options="indexParams.years"
                        :searchable="true"
                        :canClear="false"
                    />
                </div>
                <div class="flex flex-col">
                    <CustomSelect
                        v-model="params.month"
                        mode="single"
                        label="Ամիս"
                        :options="indexParams.months"
                        :searchable="true"
                        :canClear="false"
                    />
                </div>
            </div>
        </TableActions>

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
              { title: 'Հերթագրման ամսաթիվ' },
              { title: 'Անուն Ազգանուն' },
              { title: 'Հեռախոս' },
              { title: 'Ինֆորմացիա' },
              { title: 'Բժիշկ' },
              { title: 'Բուժհաստատություն' },
              { title: 'Գործողություն' },
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black" :style="{ color: item.color }">
                            {{ item.date ? item.date.slice(0, 10).split('-').reverse().join('.') : '' }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black" :style="{ color: item.color }">{{ item.full_name }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black" :style="{ color: item.color }">{{ item.phone }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[160px] break-words whitespace-normal font-medium text-black" :style="{ color: item.color }">{{ item.description }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black" :style="{ color: item.color }">{{ item.doctor }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black" :style="{ color: item.color }">{{ item.hospital }}</span>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="'subscribes/update/' + item.id">
                                <button class="hover:text-primary" title="Edit">
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>
                            <button
                                v-if="auth.user_group.permissions_by_name.subscribes[0].can_delete"
                                @click="store.commit('subscribe/SET_DELETE_MODAL_VALUE', {
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
            action-variable="subscribe/delete"
            getter-variable="subscribe/getDeleteModelValue"
            mutation-variable="subscribe/SET_DELETE_MODAL_VALUE"
        />

    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';
</style>

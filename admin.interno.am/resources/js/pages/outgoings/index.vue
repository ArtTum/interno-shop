<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import TableActions from "@components/global/TableActions.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, watch} from 'vue'

import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const store = useStore();
const route = useRoute();
const router = useRouter();

const now = new Date();

const getDefaultParams = () => {
    const n = new Date();
    return {
        page: 1,
        per_page: 200,
        search: '',
        ordering_field: 'id',
        ordering_direction: 'desc',
        year: n.getFullYear(),
        month: n.getMonth() + 1,
    };
};

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 200,
    search: route.query.search || '',
    ordering_field: route.query.ordering_field || 'id',
    ordering_direction: route.query.ordering_direction || 'desc',

    year: route.query.year
        ? Number(route.query.year)
        : now.getFullYear(),

    month: route.query.month
        ? Number(route.query.month)
        : now.getMonth() + 1,
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
    await store.dispatch('outgoing/fetchPageData', params.value);
};

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await updateQueryParams();
    await fetchPageData();
};

const indexParams = ref([]);

const fetchIndexParams = async () => {
    indexParams.value = await store.dispatch('outgoing/fetchIndexParams', {
        dontNeedLoading: true
    });
};

const pageData = computed(() => store.getters['outgoing/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);

fetchIndexParams();
fetchPageData();

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
        <BreadcrumbDefault pageTitle="Ծախսեր" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
        ]"/>
        <TableActions
            :createRoute="(auth?.superadmin || auth?.user_group?.permissions_by_name.outgoings[0].can_add) ? '/outgoings/create' : null"
            :showFilter="true"
            :filterMenuInitialValue="true"
            @applyFilters="doPageFetching"
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
                        @change="doPageFetching(true)"
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
                        @change="doPageFetching(true)"
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
                    text: 'Անվանում'
                }
            }"
            :pagination="pageData.pagination"
            :columns="[
              { title: '#', key: 'id' },
              { title: 'Ամսաթիվ' },
              { title: 'Անվանում' },
              { title: 'Գումար' },
              { title: 'Գործողություն' },
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">#{{ item.id }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.date }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.info }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.price }}</span>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="'outgoings/update/' + item.id">
                                <button
                                    class="hover:text-primary"
                                    title="Edit"
                                >
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>

                            <button
                                v-if="auth.user_group.permissions_by_name.outgoings[0].can_delete"
                                @click="store.commit('outgoing/SET_DELETE_MODAL_VALUE', {
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
            action-variable="outgoing/delete"
            getter-variable="outgoing/getDeleteModelValue"
            mutation-variable="outgoing/SET_DELETE_MODAL_VALUE"
        />

    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';
</style>
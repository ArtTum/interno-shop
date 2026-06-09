<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import TableActions from "@components/global/TableActions.vue";

import {computed, ref} from 'vue'
import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const route = useRoute();
const router = useRouter();
const store = useStore();

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 25,
    search: route.query.search || '',
    status: route.query.status === undefined ? -1 : Number(route.query.status),
    ordering_field: route.query.ordering_field || 'name',
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
    await store.dispatch('language/fetchPageData', params.value);
};

fetchPageData();

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await updateQueryParams();
    await fetchPageData();
};

const pageData = computed(() => store.getters['language/getPageData']);

const options = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Active'},
    {value: 0, label: 'Inactive'},
];

const auth = computed(() => store.getters['auth/getUser']);
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Languages" :breadcrumb="[
            {path: '/', title: 'Dashboard'},
        ]"/>
        <TableActions
            :createRoute="auth.user_group.permissions_by_name.languages[0].can_add ? '/settings/languages/create' : ''"
            :showFilter="true"
            @applyFilters="doPageFetching"
        >
            <div class="grid grid-cols-4 gap-6 p-6 max-xl:grid-cols-2 max-sm:grid-cols-1 max-md:gap-4 max-md:p-4 max-sm:p-1">
                <div>
                    <CustomSelect
                        v-model="params.status"
                        mode="single"
                        label="Status"
                        :options="options"
                        :searchable="false"
                        :canClear="false"
                    />
                </div>
            </div>
        </TableActions>

        <CustomTable
            :main-search="{
                visibility: true,
                placeholder: 'Search ...',
                tooltip: {
                    button: {showingType: 'info'},
                    text: 'Name, code'
                }
            }"
            @do-page-fetching="doPageFetching"
            v-model="params"
            :pagination="pageData.pagination"
            :columns="[
              { title: 'ID', key: 'languages.id' },
              { title: 'Icon'},
              { title: 'Code', key: 'languages.code' },
              { title: 'Name', key: 'languages.name' },
              { title: 'Currency' },
              { title: 'Status', key: 'status' },
              { title: 'Draft status', key: 'draft' },
              { title: 'Action' },
            ]"
        >
            <template
                v-for="(item, index) in pageData.data"
                :key="index"
            >
                <tr :draggable="false">
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">#{{ item.id }}</h5>
                    </td>
                    <td class="py-5 px-4">
                        <img
                            width="30px"
                            :src="`/flags/${item.code.toLowerCase()}.svg`"
                            :alt="item.code"
                            loading="lazy"
                            fetchpriority="low"
                        >
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.code }}</span>
                        <span
                            v-if="item.base"
                            class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium bg-success text-success"
                        >
                            Base
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item.name }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item.currency_code }}</h5>
                    </td>
                    <td class="py-5 px-4">
                        <p
                            class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium"
                            :class="{
                                'bg-danger text-danger': !item.status,
                                'bg-success text-success': item.status
                            }"
                        >
                            {{ item.status_text }}
                        </p>
                    </td>
                    <td class="py-5 px-4">
                        <p
                            class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium"
                            :class="{
                                'bg-danger text-danger': item.draft,
                                'bg-success text-success': !item.draft
                            }"
                        >
                            {{ item.draft_text }}
                        </p>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="'languages/update/' + item.code">
                                <button class="hover:text-primary" title="Edit">
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>

                            <template v-if="auth.user_group.permissions_by_name.languages[0].can_delete">
                                <button
                                    @click="store.commit('language/SET_DELETE_MODAL_VALUE', {
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
            action-variable="language/delete"
            getter-variable="language/getDeleteModelValue"
            mutation-variable="language/SET_DELETE_MODAL_VALUE"
        />
    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';
.thead > th:first-child,
.data-table-common .datatable-table > tbody > tr > td:first-child,
.data-table-common .datatable-table > thead > tr > th:first-child {
    padding-left: 15px !important;
}
</style>
<style lang="scss" scoped>
.index tr td:last-child {
    min-width: 70px;
}
</style>

<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import TableActions from "@components/global/TableActions.vue";

import {computed, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const route = useRoute();
const router = useRouter();

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 25,
    search: route.query.search || '',
    language_id: Number(route.query.language_id) || 0,
    parent_id: route.query.parent_id ?? '',
    status: route.query.status === undefined ? -1 : Number(route.query.status),
    ordering_field: route.query.ordering_field || 'sort_order',
    ordering_direction: route.query.ordering_direction || 'asc',
});

const statusOptions = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Active'},
    {value: 0, label: 'Inactive'},
];

const pageData = computed(() => store.getters['shopCategory/getPageData']);
const parentOptions = computed(() => [
    {value: '', label: 'All parents'},
    {value: 'root', label: 'Root only'},
    ...(pageData.value.parents || []).filter((parent) => parent.value),
]);

const auth = computed(() => store.getters['auth/getUser']);
const permission = computed(() => auth.value?.user_group?.permissions_by_name?.shop_categories?.[0] || {});
const canAdd = computed(() => auth.value?.superadmin || permission.value.can_add);
const canDelete = computed(() => auth.value?.superadmin || permission.value.can_delete);

const updateQueryParams = async () => {
    await router.push({
        query: {
            ...route.query,
            ...params.value,
        },
    });
};

const fetchPageData = async () => {
    const data = await store.dispatch('shopCategory/fetchPageData', params.value);

    if (!params.value.language_id && data.base_language_id) {
        params.value.language_id = data.base_language_id;
    }
};

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }

    await updateQueryParams();
    await fetchPageData();
};

fetchPageData();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Shop Categories" :breadcrumb="[
            {path: '/', title: 'Dashboard'},
        ]"/>

        <TableActions
            :createRoute="canAdd ? '/shop-categories/create' : ''"
            :showFilter="true"
            @applyFilters="doPageFetching"
        >
            <div class="grid grid-cols-4 gap-6 p-6 max-xl:grid-cols-2 max-sm:grid-cols-1 max-md:gap-4 max-md:p-4 max-sm:p-1">
                <div>
                    <CustomSelect
                        v-model="params.language_id"
                        mode="single"
                        label="Language"
                        :options="pageData.languages"
                        :searchable="true"
                        :canClear="false"
                    />
                </div>
                <div>
                    <CustomSelect
                        v-model="params.parent_id"
                        mode="single"
                        label="Parent"
                        :options="parentOptions"
                        :searchable="true"
                        :canClear="false"
                    />
                </div>
                <div>
                    <CustomSelect
                        v-model="params.status"
                        mode="single"
                        label="Status"
                        :options="statusOptions"
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
                    text: 'Name, slug, meta title'
                }
            }"
            @do-page-fetching="doPageFetching"
            v-model="params"
            :pagination="pageData.pagination"
            :columns="[
                {title: 'ID', key: 'shop_categories.id'},
                {title: 'Name'},
                {title: 'Slug'},
                {title: 'Parent'},
                {title: 'Meta title'},
                {title: 'Children'},
                {title: 'Products'},
                {title: 'Status', key: 'status'},
                {title: 'Action'},
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr :draggable="false">
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">#{{ item.id }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.name || '-' }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.slug || item.global_slug }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.parent_name || '-' }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.meta_title || '-' }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.children_count }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.products_count }}</span>
                    </td>
                    <td class="py-5 px-4">
                        <p
                            class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium"
                            :class="{
                                'bg-danger text-danger': !item.status,
                                'bg-success text-success': item.status
                            }"
                        >
                            {{ item.status ? 'Active' : 'Inactive' }}
                        </p>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="`/shop-categories/update/${item.id}/${params.language_id}`">
                                <button class="hover:text-primary" title="Edit">
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>

                            <template v-if="canDelete">
                                <button
                                    @click="store.commit('shopCategory/SET_DELETE_MODAL_VALUE', {
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
            action-variable="shopCategory/delete"
            getter-variable="shopCategory/getDeleteModelValue"
            mutation-variable="shopCategory/SET_DELETE_MODAL_VALUE"
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

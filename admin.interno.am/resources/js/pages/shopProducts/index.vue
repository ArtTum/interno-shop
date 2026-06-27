<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import TableActions from "@components/global/TableActions.vue";
import CustomCopyButton from "@components/global/CustomCopyButton.vue";

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
    category_id: route.query.category_id === undefined ? -1 : Number(route.query.category_id),
    status: route.query.status === undefined ? -1 : Number(route.query.status),
    availability: route.query.availability === undefined ? -1 : Number(route.query.availability),
    is_new: route.query.is_new === undefined ? -1 : Number(route.query.is_new),
    kind: route.query.kind || '',
    option_type_id: route.query.option_type_id === undefined ? -1 : Number(route.query.option_type_id),
    option_color_id: route.query.option_color_id === undefined ? -1 : Number(route.query.option_color_id),
    ordering_field: route.query.ordering_field || 'sort_order',
    ordering_direction: route.query.ordering_direction || 'asc',
});

const statusOptions = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Active'},
    {value: 0, label: 'Inactive'},
];

const newOptions = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Is new'},
    {value: 0, label: 'Is not new'},
];

const availabilityOptions = [
    {value: -1, label: 'All'},
    {value: 0, label: 'Available'},
    {value: 1, label: 'Temporarily unavailable'},
];

const pageData = computed(() => store.getters['shopProduct/getPageData']);
const categoryOptions = computed(() => [
    {value: -1, label: 'All categories'},
    ...(pageData.value.categories || []).filter((category) => category.value),
]);
const optionTypeOptions = computed(() => [
    {value: -1, label: 'All types'},
    ...(pageData.value.optionTypes || []).filter((type) => type.value),
]);
const optionColorOptions = computed(() => [
    {value: -1, label: 'All colors'},
    ...(pageData.value.optionColors || []).filter((color) => color.value),
]);

const auth = computed(() => store.getters['auth/getUser']);
const permission = computed(() => auth.value?.user_group?.permissions_by_name?.shop_products?.[0] || {});
const canAdd = computed(() => auth.value?.superadmin || permission.value.can_add);
const canEdit = computed(() => auth.value?.superadmin || permission.value.can_edit);
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
    const data = await store.dispatch('shopProduct/fetchPageData', params.value);

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

const moveProduct = async (id, direction) => {
    await store.dispatch('shopProduct/reorder', {id, direction});
    await fetchPageData();
};

fetchPageData();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Shop Products" :breadcrumb="[
            {path: '/', title: 'Dashboard'},
        ]"/>

        <TableActions
            :createRoute="canAdd ? '/shop-products/create' : ''"
            :showFilter="true"
            @applyFilters="doPageFetching"
        >
            <div class="grid grid-cols-5 gap-6 p-6 max-xl:grid-cols-3 max-md:grid-cols-2 max-sm:grid-cols-1 max-md:gap-4 max-md:p-4 max-sm:p-1">
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
                        v-model="params.category_id"
                        mode="single"
                        label="Category"
                        :options="categoryOptions"
                        :searchable="true"
                        :canClear="false"
                    />
                </div>
                <div>
                    <CustomSelect
                        v-model="params.option_type_id"
                        mode="single"
                        label="Type"
                        :options="optionTypeOptions"
                        :searchable="true"
                        :canClear="false"
                    />
                </div>
                <div>
                    <CustomSelect
                        v-model="params.option_color_id"
                        mode="single"
                        label="Color"
                        :options="optionColorOptions"
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
                <div>
                    <CustomSelect
                        v-model="params.availability"
                        mode="single"
                        label="Availability"
                        :options="availabilityOptions"
                        :searchable="false"
                        :canClear="false"
                    />
                </div>
                <div>
                    <CustomSelect
                        v-model="params.is_new"
                        mode="single"
                        label="New"
                        :options="newOptions"
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
                    text: 'ID, name, slug, kind, meta title'
                }
            }"
            @do-page-fetching="doPageFetching"
            v-model="params"
            :pagination="pageData.pagination"
            :columns="[
                {title: 'ID', key: 'shop_products.id'},
                {title: 'Image'},
                {title: 'Name'},
                {title: 'Slug'},
                {title: 'Category'},
                {title: 'Type'},
                {title: 'Color'},
                {title: 'Min price', key: 'shop_products.price'},
                {title: 'Sort', key: 'sort_order'},
                {title: 'New'},
                {title: 'Qty limit'},
                {title: 'Availability', key: 'availability'},
                {title: 'Status', key: 'status'},
                {title: 'Action'},
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr :draggable="false">
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <div class="flex">
                            <RouterLink :to="`/shop-products/update/${item.id}/${params.language_id}`">
                                <h5 class="font-medium text-primary">#{{ item.id }}</h5>
                            </RouterLink>
                            <div class="ml-2">
                                <CustomCopyButton dynamic-cclass="text-black" :text="item.id"/>
                            </div>
                        </div>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <template v-if="item.image">
                            <img
                                width="80px"
                                :src="item.image"
                                :alt="item.title"
                                loading="lazy"
                                fetchpriority="low"
                            >
                        </template>
                        <template v-else>-</template>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.title || '-' }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.slug || item.global_slug }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.category_name || '-' }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.option_type_name || '-' }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span v-if="item.option_colors?.length" class="inline-flex flex-wrap items-center gap-2 font-medium text-black">
                            <span
                                v-for="color in item.option_colors"
                                :key="color.id"
                                class="inline-flex items-center gap-1"
                                :title="color.name"
                            >
                                <span
                                    class="h-4 w-4 rounded border border-stroke"
                                    :style="{backgroundColor: color.value || '#ffffff'}"
                                ></span>
                                <span>{{ color.name }}</span>
                                <small v-if="Number(color.id) === Number(item.option_color_id)" class="text-primary">(main)</small>
                            </span>
                        </span>
                        <span v-else class="inline-flex items-center gap-2 font-medium text-black">
                            <span
                                v-if="item.option_color_value"
                                class="h-4 w-4 rounded border border-stroke"
                                :style="{backgroundColor: item.option_color_value}"
                            ></span>
                            {{ item.option_color_name || '-' }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.price }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-black">{{ item.sort_order }}</span>
                            <div v-if="canEdit" class="inline-flex flex-col gap-1">
                                <button
                                    type="button"
                                    class="leading-none hover:text-primary"
                                    title="Move up"
                                    @click="moveProduct(item.id, 'up')"
                                >
                                    <font-awesome-icon :icon="['fas', 'angle-up']"/>
                                </button>
                                <button
                                    type="button"
                                    class="leading-none hover:text-primary"
                                    title="Move down"
                                    @click="moveProduct(item.id, 'down')"
                                >
                                    <font-awesome-icon :icon="['fas', 'angle-down']"/>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="py-5 px-4">
                        <p
                            class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium"
                            :class="{
                                'bg-gray text-black': !item.is_new,
                                'bg-success text-success': item.is_new
                            }"
                        >
                            {{ item.is_new ? 'New' : 'No' }}
                        </p>
                    </td>
                    <td class="py-5 px-4">
                        <p
                            class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium"
                            :class="{
                                'bg-gray text-black': !item.purchase_quantity_limited,
                                'bg-warning text-warning': item.purchase_quantity_limited
                            }"
                        >
                            {{ item.purchase_quantity_limited ? 'Limited' : 'No' }}
                        </p>
                    </td>
                    <td class="py-5 px-4">
                        <p
                            class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium"
                            :class="{
                                'bg-success text-success': !item.is_temporarily_unavailable,
                                'bg-warning text-warning': item.is_temporarily_unavailable
                            }"
                        >
                            {{ item.is_temporarily_unavailable ? 'Temporarily unavailable' : 'Available' }}
                        </p>
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
                            <RouterLink :to="`/shop-products/update/${item.id}/${params.language_id}`">
                                <button class="hover:text-primary" title="Edit">
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>

                            <template v-if="canDelete">
                                <button
                                    @click="store.commit('shopProduct/SET_DELETE_MODAL_VALUE', {
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
            action-variable="shopProduct/delete"
            getter-variable="shopProduct/getDeleteModelValue"
            mutation-variable="shopProduct/SET_DELETE_MODAL_VALUE"
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

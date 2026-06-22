<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomInput from "@components/global/CustomInput.vue";
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
    status: route.query.status === undefined ? -1 : Number(route.query.status),
    work_region: route.query.work_region || '',
    work_city: route.query.work_city || '',
    work_field: route.query.work_field || '',
    ordering_field: route.query.ordering_field || 'sort_order',
    ordering_direction: route.query.ordering_direction || 'asc',
});

const statusOptions = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Active'},
    {value: 0, label: 'Inactive'},
];

const pageData = computed(() => store.getters['shopCraftsman/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);
const permission = computed(() => auth.value?.user_group?.permissions_by_name?.shop_craftsmen?.[0] || {});
const canAdd = computed(() => auth.value?.superadmin || permission.value.can_add);
const canDelete = computed(() => auth.value?.superadmin || permission.value.can_delete);

const updateQueryParams = async () => {
    await router.push({query: {...route.query, ...params.value}});
};

const fetchPageData = async () => {
    await store.dispatch('shopCraftsman/fetchPageData', params.value);
};

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) params.value.page = 1;
    await updateQueryParams();
    await fetchPageData();
};

fetchPageData();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Craftsmen" :breadcrumb="[{path: '/', title: 'Dashboard'}]"/>
        <TableActions
            :createRoute="canAdd ? '/shop-craftsmen/create' : ''"
            :showFilter="true"
            @applyFilters="doPageFetching"
        >
            <div class="grid grid-cols-4 gap-6 p-6 max-xl:grid-cols-2 max-sm:grid-cols-1 max-md:gap-4 max-md:p-4 max-sm:p-1">
                <CustomSelect
                    v-model="params.status"
                    mode="single"
                    label="Status"
                    :options="statusOptions"
                    :searchable="false"
                    :canClear="false"
                />
                <CustomInput v-model="params.work_region" name="work_region" label="Main region" type="text" placeholder="Filter region"/>
                <CustomInput v-model="params.work_city" name="work_city" label="City" type="text" placeholder="Filter city"/>
                <CustomInput v-model="params.work_field" name="work_field" label="Work field" type="text" placeholder="Filter field"/>
            </div>
        </TableActions>

        <CustomTable
            :main-search="{visibility: true, placeholder: 'Search ...', tooltip: {button: {showingType: 'info'}, text: 'Code, name, phone'}}"
            @do-page-fetching="doPageFetching"
            v-model="params"
            :pagination="pageData.pagination"
            :columns="[
                {title: 'ID', key: 'id'},
                {title: 'Photo'},
                {title: 'Code', key: 'code'},
                {title: 'Name', key: 'first_name'},
                {title: 'Phone'},
                {title: 'Region', key: 'work_region'},
                {title: 'City', key: 'work_city'},
                {title: 'Field', key: 'work_field'},
                {title: 'Sort order', key: 'sort_order'},
                {title: 'Status', key: 'status'},
                {title: 'Action'},
            ]"
        >
            <template v-for="item in pageData.data" :key="item.id">
                <tr>
                    <td class="py-5 px-4 pl-9 xl:pl-11"><h5 class="font-medium text-black">#{{ item.id }}</h5></td>
                    <td class="py-5 px-4">
                        <img v-if="item.image" :src="item.image" :alt="item.full_name" class="h-14 w-14 rounded object-cover border border-stroke"/>
                        <span v-else class="inline-flex h-14 w-14 items-center justify-center rounded border border-stroke bg-gray text-sm">-</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11"><span class="font-medium text-black">{{ item.code }}</span></td>
                    <td class="py-5 px-4 pl-9 xl:pl-11"><span class="font-medium text-black">{{ item.full_name }}</span></td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.phone || '-' }}</span>
                        <div v-if="item.phone" class="mt-1 flex gap-1 text-xs">
                            <span v-if="item.has_whatsapp" class="rounded bg-success bg-opacity-10 px-2 py-0.5 text-success">WhatsApp</span>
                            <span v-if="item.has_viber" class="rounded bg-primary bg-opacity-10 px-2 py-0.5 text-primary">Viber</span>
                        </div>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11"><span class="font-medium text-black">{{ item.work_region || '-' }}</span></td>
                    <td class="py-5 px-4 pl-9 xl:pl-11"><span class="font-medium text-black">{{ item.work_city || '-' }}</span></td>
                    <td class="py-5 px-4 pl-9 xl:pl-11"><span class="font-medium text-black">{{ item.work_field || '-' }}</span></td>
                    <td class="py-5 px-4 pl-9 xl:pl-11"><span class="font-medium text-black">{{ item.sort_order }}</span></td>
                    <td class="py-5 px-4">
                        <p class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium" :class="{'bg-danger text-danger': !item.status, 'bg-success text-success': item.status}">
                            {{ item.status ? 'Active' : 'Inactive' }}
                        </p>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="`/shop-craftsmen/update/${item.id}`">
                                <button class="hover:text-primary" title="Edit">
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>
                            <button
                                v-if="canDelete"
                                @click="store.commit('shopCraftsman/SET_DELETE_MODAL_VALUE', {value: true, id: item.id})"
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
            action-variable="shopCraftsman/delete"
            getter-variable="shopCraftsman/getDeleteModelValue"
            mutation-variable="shopCraftsman/SET_DELETE_MODAL_VALUE"
        />
    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';
</style>

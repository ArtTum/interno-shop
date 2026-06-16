<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import TableActions from "@components/global/TableActions.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, onMounted, ref, watch} from 'vue'
import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const store = useStore();
const route = useRoute();
const router = useRouter();

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 25,
    search: route.query.search || '',
    blocked: route.query.blocked === undefined ? -1 : Number(route.query.blocked),
    user_group: route.query.user_group === undefined ? -1 : Number(route.query.user_group),
    only_actives: route.query.only_actives === undefined ? 1 : Number(route.query.only_actives),
    ordering_field: route.query.ordering_field || 'id',
    ordering_direction: route.query.ordering_direction || 'asc',
    type: 'employees',
});

store.dispatch('user/fetchParams');

const userGroupOptions = ref([]);
const customerGroupOptions = ref([]);

const paramsData = computed(() => store.getters['user/getParams']);
watch(
    () => paramsData,
    (newValue) => {
        if (newValue?.value?.userGroups && Array.isArray(newValue.value.userGroups)) {
            userGroupOptions.value = [{ value: -1, label: 'All' }, ...newValue.value.userGroups]
        }
        if (newValue?.value?.customerGroups && Array.isArray(newValue.value.customerGroups)) {
            customerGroupOptions.value = [{ value: -1, label: 'All' }, ...newValue.value.customerGroups]
        }
    }, {immediate: true, deep: true}
);

const updateQueryParams = async () => {
    await router.push({
        query: {
            ...route.query,
            ...params.value
        },
    });
};

const fetchPageData = async () => {
    await store.dispatch('user/fetchPageData', params.value);
};

fetchPageData();

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await updateQueryParams();
    await fetchPageData();
};

onMounted(() => {
    Echo.channel("update-users-page")
        .listen("ReloadPagePublic", e => {
            fetchPageData();
        });
});

const pageData = computed(() => store.getters['user/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);

const options = [
    {value: -1, label: 'All'},
    {value: 0, label: 'General'},
    {value: 1, label: 'Blocked'},
];

const statusOptions = [
    {value: -1, label: 'All'},
    {value: 1, label: 'Active'},
    {value: 0, label: 'Inactive'},
];
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Employees" :breadcrumb="[
            {path: '/dashboard', title: 'Dashboard'},
        ]"/>
        <TableActions
            :createRoute="auth.user_group.permissions_by_name.users[0].can_add ? '/users/list/create' : ''"
            :showFilter="true"
            :filterMenuInitialValue="true"
            @applyFilters="doPageFetching"
        >
            <div class="grid grid-cols-4 gap-9">
                <div class="flex flex-col p-2.5">
                    <CustomSelect
                        v-model="params.blocked"
                        mode="single"
                        label="Blocked"
                        :options="options"
                        :searchable="false"
                        :canClear="false"
                    />
                </div>
                <div class="flex flex-col p-2.5">
                    <CustomSelect
                        v-model="params.user_group"
                        mode="single"
                        label="User group"
                        :options="userGroupOptions"
                        :searchable="false"
                        :canClear="false"
                    />
                </div>
                <div class="flex flex-col p-2.5">
                    <CustomSelect
                        v-model="params.only_actives"
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
            class="text-sm"
            @do-page-fetching="doPageFetching"
            v-model="params"
            :main-search="{
                visibility: true,
                placeholder: 'Search...',
                tooltip: {
                    button: {showingType: 'info'},
                    text: 'First name, last name, email'
                }
            }"
            :page-data="pageData.data"

            permission-name="users"
            :pagination="pageData.pagination"
            :columns="[
              { title: 'ID', key: 'id' },
              { title: 'Full name', key: 'name' },
              { title: 'Email', key: 'email' },
              { title: 'Employee group', key: 'group_name' },
              { title: 'Blocked', key: 'blocked' },
              { title: 'Action' },
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">#{{ item.id }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.name }} {{ item.last_name }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.email }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.group_name }}</span>
                    </td>
                    <td class="py-5 px-4">
                        <p
                            class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium"
                            :class="{'bg-danger text-danger': item.blocked == true}"
                        >
                            {{ item.status_text }}
                        </p>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="'/users/list/update/' + item.id">
                                <button
                                    class="hover:text-primary"
                                    title="Edit"
                                >
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>

<!--                            <template v-if="auth.user_group.permissions_by_name.users[0].can_delete && !item.superadmin">-->
<!--                                <button-->
<!--                                    @click="store.commit('user/SET_DELETE_MODAL_VALUE', {-->
<!--                                    value: true,-->
<!--                                    id: item.id-->
<!--                                });"-->
<!--                                    class="hover:text-primary"-->
<!--                                    title="Delete"-->
<!--                                >-->
<!--                                    <font-awesome-icon :icon="['fas', 'trash-can']"/>-->
<!--                                </button>-->
<!--                            </template>-->
                        </div>
                    </td>
                </tr>
            </template>
        </CustomTable>

        <DeleteModal
            @fetch="fetchPageData()"
            action-variable="user/delete"
            getter-variable="user/getDeleteModelValue"
            mutation-variable="user/SET_DELETE_MODAL_VALUE"
        />

    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';
</style>

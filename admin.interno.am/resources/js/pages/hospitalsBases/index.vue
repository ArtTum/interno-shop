<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import TableActions from "@components/global/TableActions.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";

import {computed, ref, watch} from 'vue'
import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const formatDate = (date) => date ? date.slice(0, 10).split('-').reverse().join('.') : ''

const store = useStore();
const route = useRoute();
const router = useRouter();
const now = new Date()

const lastOpenedId = ref(Number(localStorage.getItem('lastOpenedHospitalsBaseId')) || null);

const openRecord = (id) => {
    lastOpenedId.value = id;
    localStorage.setItem('lastOpenedHospitalsBaseId', id);
};

const getDefaultParams = () => {
    const n = new Date();
    return {
        page: 1,
        per_page: 100,
        search: '',
        ordering_field: 'id',
        ordering_direction: 'desc',
        year: -1,
        month: -1,
        color: -1,
        find_about_us: -1,
        day_surgery_start: '',
        day_surgery_end: '',
        call_date: '',
        next_call_date: '',
        user: '',
        hospital: '',
        diseases: '',
        konsultacia: '',
    };
};

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 100,
    search: route.query.search || '',
    ordering_field: route.query.ordering_field || 'id',
    ordering_direction: route.query.ordering_direction || 'desc',

    year: route.query.year ? Number(route.query.year) : -1,
    month: route.query.month ? Number(route.query.month) : -1,

    color: route.query.color === undefined ? -1 : route.query.color,
    find_about_us: route.query.find_about_us === undefined ? -1 : route.query.find_about_us,
    day_surgery_start: route.query.day_surgery_start || '',
    day_surgery_end: route.query.day_surgery_end || '',
    call_date: route.query.call_date || '',
    next_call_date: route.query.next_call_date || '',
    user: route.query.user || '',
    hospital: route.query.hospital || '',
    diseases: route.query.diseases || '',
    konsultacia: route.query.konsultacia || '',
});

const updateQueryParams = async () => {
    await router.push({query: {...route.query, ...params.value}});
};

const fetchPageData = async () => {
    await store.dispatch('hospitalsBase/fetchPageData', params.value);
};

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) params.value.page = 1;
    await updateQueryParams();
    await fetchPageData();
};

const indexParams = ref([]);

const fetchPageParams = async () => {
    indexParams.value = await store.dispatch('hospitalsBase/fetchIndexParams', {dontNeedLoading: true});
};

const pageData = computed(() => store.getters['hospitalsBase/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);

fetchPageParams();
fetchPageData();

// Normalize period-like characters (Armenian ․ and others) to English .
watch(() => params.value.search, (val) => {
    const normalized = val.replace(/[\u0589\u00B7\u2024\u2027\u22C5]/g, '.');
    if (normalized !== val) params.value.search = normalized;
}, { flush: 'sync' });

// Auto-filter when any filter changes
watch(() => [
    params.value.year,
    params.value.month,
    params.value.color,
    params.value.find_about_us,
    params.value.day_surgery_start,
    params.value.day_surgery_end,
    params.value.call_date,
    params.value.next_call_date,
    params.value.user,
    params.value.hospital,
    params.value.diseases,
    params.value.konsultacia,
], () => {
    doPageFetching();
}, { deep: true });

// Reset filters when navigating fresh to this page (e.g. clicking menu link)
watch(() => route.query, (newQuery) => {
    if (Object.keys(newQuery).length === 0) {
        params.value = getDefaultParams();
    }
}, { deep: true });
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="ՀԻՎԱՆԴՆԵՐԻ ԲԱԶԱ" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
        ]"/>
        <TableActions
            :createRoute="(auth?.superadmin || auth?.user_group?.permissions_by_name.hospitals_bases[0].can_add) ? '/hospitals-bases/create' : null"
            :showFilter="true"
            :hideApplyButton="true"
            :filterMenuInitialValue="true"
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
                <div class="flex flex-col">
                    <CustomSelect
                        v-model="params.color"
                        mode="single"
                        label="Գույն"
                        :options="indexParams.colors"
                        :searchable="true"
                        :canClear="false"
                    />
                </div>
                <div class="flex flex-col">
                    <CustomSelect
                        v-model="params.find_about_us"
                        mode="single"
                        label="Որտեղից է մեր մասին իմացել"
                        :options="indexParams.findAboutUS"
                        :searchable="true"
                        :canClear="false"
                    />
                </div>
                <div class="flex w-full col-span-2 max-sm:col-span-1 max-xsm:flex-wrap">
                    <div class="w-full">
                        <CustomDatePicker
                            placeholder="dd/mm/yyyy"
                            :tableInput="true"
                            label="վիրահատության օր սկիզբ"
                            format="Y-m-d"
                            v-model="params.day_surgery_start"
                        />
                    </div>
                    <div class="flex items-end justify-center px-2 xsm:py-[12px] max-sm:px-1 max-xsm:w-100">
                        <div class="text-center">-</div>
                    </div>
                    <div class="w-full">
                        <CustomDatePicker
                            placeholder="dd/mm/yyyy"
                            :tableInput="true"
                            label="վիրահատության օր վերջ"
                            format="Y-m-d"
                            v-model="params.day_surgery_end"
                        />
                    </div>
                </div>
                <div class="flex flex-col">
                    <CustomDatePicker
                        placeholder="dd/mm/yyyy"
                        :tableInput="true"
                        label="Զանգի ամսաթիվ"
                        format="Y-m-d"
                        v-model="params.call_date"
                    />
                </div>
                <div class="flex flex-col">
                    <CustomDatePicker
                        placeholder="dd/mm/yyyy"
                        :tableInput="true"
                        label="Հաջորդ զանգի ամսաթիվ"
                        format="Y-m-d"
                        v-model="params.next_call_date"
                    />
                </div>
                <div class="flex flex-col">
                    <CustomSelect
                        v-model="params.user"
                        mode="single"
                        label="Օգտատերեր"
                        :options="indexParams.users"
                        :searchable="true"
                        :canClear="true"
                    />
                </div>
                <div class="flex flex-col">
                    <CustomSelect
                        v-model="params.hospital"
                        mode="single"
                        label="Հիվանդանոցներ"
                        :options="indexParams.hospitals"
                        :searchable="true"
                        :canClear="true"
                    />
                </div>
                <div class="flex flex-col">
                    <CustomSelect
                        v-model="params.diseases"
                        mode="single"
                        label="Հիվանդություններ"
                        :options="indexParams.diseases"
                        :searchable="true"
                        :canClear="true"
                    />
                </div>
                <div class="flex flex-col">
                    <CustomSelect
                        v-model="params.konsultacia"
                        mode="single"
                        label="կոնսուլտացիա"
                        :options="[{value: 1, label: 'Ոչ'}, {value: 2, label:'Այո'}]"
                        :searchable="true"
                        :canClear="true"
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
                    text: 'Անուն, հեռախոս'
                }
            }"
            :pagination="pageData.pagination"
            :columns="[
              { title: '#', key: 'id' },
              { title: 'Զանգի ամսաթիվ' },
              { title: 'Հիվանդի անուն<br>ազգանուն' },
              { title: 'Հեռախոս' },
              { title: 'Հիվանդություն' },
              { title: 'Լրացուցիչ տվյալներ' },
              { title: 'Գործողություն' },
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr :style="item.id === lastOpenedId ? 'background-color: #e0f0ff;' : ''"  >
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">#{{ pageData.pagination.showing.from + index }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black" :style="{ color: item.color }">
                            {{ formatDate(item.call_date) }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[140px] break-words whitespace-normal font-medium" :style="{ color: item.color }">
                            {{ item.patient_full_name }}
                            <br v-if="item.age">
                            <small v-if="item.age">{{ item.age }} տ.</small>
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium" :style="{ color: item.color }">
                            {{ item.phone }}
                            <br v-if="item.other_phone">
                            {{ item.other_phone }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[140px] break-words whitespace-normal font-medium" :style="{ color: item.color }">
                            {{ item.disease }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <div
                            class="force-color block w-[160px] break-words whitespace-normal font-medium"
                            :style="{ '--item-color': item.color }"
                            v-html="item.additional_data"
                        ></div>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="'hospitals-bases/update/' + item.id" @click="openRecord(item.id)">
                                <button
                                    :style="item.id === lastOpenedId ? 'color: #0070C0;' : ''"
                                    class="hover:text-primary" title="Edit">
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>
                            <button
                                v-if="auth.user_group.permissions_by_name.hospitals_bases[0].can_delete"
                                class="hover:text-primary"
                                title="Delete"
                                @click="store.commit('hospitalsBase/SET_DELETE_MODAL_VALUE', {value: true, id: item.id}); openRecord(item.id);"
                            >
                                <font-awesome-icon :icon="['far', 'trash-can']"/>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </CustomTable>

        <DeleteModal
            @fetch="fetchPageData()"
            action-variable="hospitalsBase/delete"
            getter-variable="hospitalsBase/getDeleteModelValue"
            mutation-variable="hospitalsBase/SET_DELETE_MODAL_VALUE"
        />

    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';

.force-color,
.force-color * {
    color: var(--item-color) !important;
}
</style>

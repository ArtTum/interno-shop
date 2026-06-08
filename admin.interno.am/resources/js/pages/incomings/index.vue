<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import TableActions from "@components/global/TableActions.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";
import EADModal from "@components/order/EADModal.vue";

import {computed, reactive, ref, watch} from 'vue'

const formatDate = (date) => date ? date.slice(0, 10).split('-').reverse().join('.') : ''
import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CustomInput from "@components/global/CustomInput.vue";

const store = useStore();
const route = useRoute();
const router = useRouter();
const now = new Date()

const lastOpenedId = ref(Number(localStorage.getItem('lastOpenedIncomingId')) || null);

const openRecord = (id) => {
    lastOpenedId.value = id;
    localStorage.setItem('lastOpenedIncomingId', id);
};

const getDefaultParams = () => {
    const n = new Date();
    return {
        page: 1,
        per_page: 200,
        search: '',
        ordering_field: 'updated_at',
        ordering_direction: 'desc',
        year: n.getFullYear(),
        month: n.getMonth() + 1,
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
    per_page: Number(route.query.per_page) || 200,
    search: route.query.search || '',
    ordering_field: route.query.ordering_field || 'updated_at',
    ordering_direction: route.query.ordering_direction || 'desc',


    year: route.query.year
        ? Number(route.query.year)
        : now.getFullYear(),

    month: route.query.month
        ? Number(route.query.month)
        : now.getMonth() + 1,

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
    await router.push({
        query: {
            ...route.query,
            ...params.value
        },
    });
};

const fetchPageData = async () => {
    await store.dispatch('incoming/fetchPageData', params.value);
};

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await updateQueryParams();
    await fetchPageData();
};

const indexParams = ref([]);

const fetchPageParams = async () => {
    indexParams.value = await store.dispatch('incoming/fetchIndexParams', {
        dontNeedLoading: true
    });
};

const isModalOpen = ref(false);
const statsData = ref({ sum: 0, sale_price: 0, a_d: 0 });

const closeModal = () => {
    isModalOpen.value = false;
};
const emitChanges = async () => {
    const result = await store.dispatch('incoming/fetchStats', params.value);
    statsData.value = result;
    isModalOpen.value = true;
};
const handleConfirm = async () => {

};
const packaging = reactive({
    order_id: 2,
    count: '',
    type: '',
    length: '',
    width: '',
    height: '',
    orderLanguage: false,
});

const handleExport = async () => {
    try {
        const response = await store.dispatch('incoming/export', params.value);
        const blob = new Blob([response.data], {type: response.headers['content-type']});
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = 'incomings.xlsx';
        link.click();
    } catch (error) {
        console.error('Export error:', error);
    }
};

const pageData = computed(() => store.getters['incoming/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);
fetchPageParams();
params.value.page = 1;
params.value.ordering_field = 'updated_at';
params.value.ordering_direction = 'desc';
fetchPageData();

// Normalize period-like characters (Armenian ․ and others) to English .
watch(() => params.value.search, (val) => {
    const normalized = val.replace(/[\u0589\u00B7\u2024\u2027\u22C5]/g, '.');
    if (normalized !== val) params.value.search = normalized;
}, { flush: 'sync' });

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
        <BreadcrumbDefault pageTitle="ԵԿԱՄՈՒՏՆԵՐ" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
        ]"/>
        <EADModal
            :isOpen="isModalOpen"
            @close="closeModal"
            title="ԵԿԱՄՈՒՏՆԵՐ"
        >
            <div class="p-2 ead-form-fields">
                <div class="modal-body">
                    <div class="m-datatable m-datatable--default m-datatable--loaded" id="local_data1">
                        <table class="table w-full table-auto datatable-table">
                            <thead>
                            <tr class="bg-gray-2 text-left thead2" style="height: 53px;">
                                <th class="m-datatable__cell">
                                    <span style="width: 30px;">Հ/Հ</span>
                                </th>
                                <th class="m-datatable__cell">
                                    <span style="width: 120px;">Չզեղչված գին</span>
                                </th>
                                <th class="m-datatable__cell">
                                    <span style="width: 120px;">Զեղչված գին</span>
                                </th>
                                <th class="m-datatable__cell">
                                    <span style="width: 120px;">AD</span>
                                </th>

                            </tr>
                            </thead>
                            <tbody class="swim-lane">
                            <tr>
                                <td><span style="width: 30px;" class="">1</span></td>
                                <td><span style="width: 120px;">{{ statsData.sum }}</span></td>
                                <td><span style="width: 120px;">{{ statsData.sale_price }}</span></td>
                                <td><span style="width: 120px;">{{ statsData.a_d }}</span></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </EADModal>
        <TableActions
            :buttonName="'Հաշվել'"
            :showFilter="true"
            :filterMenuInitialValue="true"
            @emit-changes="emitChanges"
            @applyFilters="doPageFetching"
        >
            <template #actions>
                <button
                    @click="handleExport"
                    class="flex items-center gap-2 rounded bg-green-600 py-2 px-4 font-medium text-white hover:bg-opacity-80"
                >
                    <font-awesome-icon :icon="['far', 'file-excel']" /> Excel
                </button>
            </template>
            <div
                class="grid grid-cols-4  gap-4  p-4 max-xl:grid-cols-3 max-md:grid-cols-2 max-md:gap-4 max-md:p-4 max-sm:grid-cols-1 max-sm:p-1">
                <div class="flex flex-col ">
                    <CustomSelect
                        v-model="params.year"
                        mode="single"
                        label="Տարի"
                        :options="indexParams.years"
                        :searchable="true"
                        :canClear="false"
                        @change="doPageFetching"
                    />
                </div>
                <div class="flex flex-col ">
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
                <div class="flex flex-col ">
                    <CustomSelect
                        v-model="params.color"
                        mode="single"
                        label="Գույն"
                        :options="indexParams.colors"
                        :searchable="true"
                        :canClear="false"
                        @change="doPageFetching(true)"
                    />
                </div>
                <div class="flex flex-col ">
                    <CustomSelect
                        v-model="params.find_about_us"
                        mode="single"
                        label="Որտեղից է մեր մասին իմացել"
                        :options="indexParams.findAboutUS"
                        :searchable="true"
                        :canClear="false"
                        @change="doPageFetching(true)"
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
                            @change="doPageFetching(true)"
                        />
                    </div>
                    <div class="flex items-end justify-center px-2 xsm:py-[12px] max-sm:px-1 max-xsm:w-100">
                        <div class="text-center">
                            -
                        </div>
                    </div>
                    <div class="w-full">
                        <CustomDatePicker
                            placeholder="dd/mm/yyyy"
                            :tableInput="true"
                            label="վիրահատության օր վերջ"
                            format="Y-m-d"
                            v-model="params.day_surgery_end"
                            @change="doPageFetching(true)"
                        />
                    </div>
                </div>
                <div class="flex flex-col ">
                    <CustomDatePicker
                        placeholder="dd/mm/yyyy"
                        :tableInput="true"
                        label="Զանգի ամսաթիվ"
                        format="Y-m-d"
                        v-model="params.call_date"
                        @change="doPageFetching(true)"
                    />
                </div>
                <div class="flex flex-col ">
                    <CustomDatePicker
                        placeholder="dd/mm/yyyy"
                        :tableInput="true"
                        label="Հաջորդ զանգի ամսաթիվ"
                        format="Y-m-d"
                        v-model="params.next_call_date"
                        @change="doPageFetching(true)"
                    />
                </div>
                <div class="flex flex-col ">
                    <CustomSelect
                        v-model="params.user"
                        mode="single"
                        label="Օգտատերեր"
                        :options="indexParams.users"
                        :searchable="true"
                        :canClear="true"
                        @change="doPageFetching(true)"
                    />
                </div>
                <div class="flex flex-col ">
                    <CustomSelect
                        v-model="params.hospital"
                        mode="single"
                        label="Հիվանդանոցներ"
                        :options="indexParams.hospitals"
                        :searchable="true"
                        :canClear="true"
                        @change="doPageFetching(true)"
                    />
                </div>
                <div class="flex flex-col ">
                    <CustomSelect
                        v-model="params.diseases"
                        mode="single"
                        label="Հիվանդություններ"
                        :options="indexParams.diseases"
                        :searchable="true"
                        :canClear="true"
                        @change="doPageFetching(true)"
                    />
                </div>
                <div class="flex flex-col ">
                    <CustomSelect
                        v-model="params.konsultacia"
                        mode="single"
                        label="կոնսուլտացիա"
                        :options="[{value: 1, label: 'Ոչ'}, {value: 2, label:'Այո'}]"
                        :searchable="true"
                        :canClear="true"
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
                    text: 'Անուն'
                }
            }"
            :pagination="pageData.pagination"
            :columns="[
              { title: '#', key: 'id' },
              { title: 'վիրահատության օր' },
              { title: 'Հիվանդի անուն <br> ազգանուն' },
              { title: 'Հեռախոս' },
              { title: 'Հիվանդություն' },
              { title: 'Բուժհաստատություն<br>և բժիշկ' },
              { title: 'Չզեղչված գին' },
              { title: 'Զեղչված գին' },
              { title: 'AD' },
              { title: 'Զեղչ' },
              { title: 'Գործողություն' },
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr :style="item.id === lastOpenedId ? 'background-color: #e0f0ff;' : ''"  >
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">#{{ pageData.pagination.showing.from + index }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[140px] break-words whitespace-normal font-medium text-black"
                              :style="{ color: item.incoming_color }">{{
                                formatDate(item.day_surgery)
                            }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[140px] break-words whitespace-normal font-medium text-black"
                              :style="{ color: item.incoming_color }">
                            {{ item.patient_full_name }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block  w-[110px] break-words whitespace-normal font-medium text-black"
                              :style="{ color: item.incoming_color }">
                            {{ item.phone }}
                            <hr v-if="item.other_phone ">
                             {{ item.other_phone }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[130px] break-words whitespace-normal font-medium text-black" :style="{ color: item.incoming_color }">
                            {{ item.disease }}
                            <hr v-if="item.disease && item.disease_record">
                            {{ item.disease_record?.name }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[160px] break-words whitespace-normal font-medium text-black" :style="{ color: item.incoming_color }">
                            {{ item.medical_and_doctor }}
                            <hr v-if="item.medical_and_doctor && item.hospital_record">
                            {{ item.hospital_record?.name }}
                        </span>
                    </td>

                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block  break-words whitespace-normal font-medium text-black"
                              :style="{ color: item.incoming_color }">{{
                                item.price
                            }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block break-words whitespace-normal font-medium text-black"
                              :style="{ color: item.incoming_color }">{{
                                item.sale_price
                            }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block break-words whitespace-normal font-medium text-black"
                              :style="{ color: item.incoming_color }">{{
                                item.a_d
                            }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block  break-words whitespace-normal font-medium text-black"
                              :style="{ color: item.incoming_color }">{{
                                item.sale
                            }}
                        </span>
                    </td>

                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="'incomings/update/' + item.id" @click="openRecord(item.id)">
                                <button
                                    :style="item.id === lastOpenedId ? 'color: #0070C0;' : ''"
                                    class="hover:text-primary"
                                    title="Edit"
                                >
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>
                            <button
                                v-if="auth.user_group.permissions_by_name.incomings[0].can_delete"
                                @click="store.commit('incoming/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: item.id
                                }); openRecord(item.id);"
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
            action-variable="incoming/delete"
            getter-variable="incoming/getDeleteModelValue"
            mutation-variable="incoming/SET_DELETE_MODAL_VALUE"
        />

    </DefaultLayoutComponent>
</template>

<style lang="scss">
@import '@assets/scss/tables';

.thead:first-child > th {
    border-color: transparent;
    padding: 1rem 0.65rem;
    font-weight: 700;
    background: #00e93a;
}

.thead2:first-child > th {
    border-color: transparent;
    padding: 1rem 0.65rem !important;
    font-weight: 700;
    background: #00e93a;
}

.force-color,
.force-color * {
    color: var(--item-color) !important;
}
</style>

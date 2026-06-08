<script setup>

import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import TableActions from "@components/global/TableActions.vue";
import CustomTable from "@components/global/CustomTable.vue";
import DeleteModal from "@components/global/DeleteModal.vue";

import {computed, ref, watch} from 'vue'

const formatDate = (date) => date ? date.slice(0, 10).split('-').reverse().join('.') : ''
import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";

const store = useStore();
const route = useRoute();
const router = useRouter();

const lastOpenedId = ref(Number(localStorage.getItem('lastOpenedRecommendationId')) || null);

const openRecord = (id) => {
    lastOpenedId.value = id;
    localStorage.setItem('lastOpenedRecommendationId', id);
};

const smsModal = ref(false);
const smsSending = ref(false);
const smsForm = ref({ phone: '', sms_text: '' });

const formatPhoneForSms = (phone = '') => {
    const digits = phone.replace(/\D/g, '');
    if (digits.startsWith('374')) return '+' + digits;
    if (digits.startsWith('0')) return '+374' + digits.slice(1);
    return '+374' + digits;
};

const openSmsModal = (phone = '') => {
    smsForm.value = { phone: formatPhoneForSms(phone), sms_text: '' };
    smsModal.value = true;
};

const closeSmsModal = () => {
    smsModal.value = false;
};

const submitSms = async () => {
    if (!smsForm.value.phone || !smsForm.value.sms_text) return;
    smsSending.value = true;
    try {
        await store.dispatch('smsHistory/sendSms', smsForm.value);
        closeSmsModal();
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Հաջողություն',
            message: 'SMS-ը հաջողությամբ ուղարկվել է'
        });
    } catch (error) {
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Սխալ',
            message: 'SMS-ը չհաջողվեց ուղարկել'
        });
    } finally {
        smsSending.value = false;
    }
};

const now = new Date()

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
    ordering_field: route.query.ordering_field || 'id',
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
    await store.dispatch('recommendation/fetchPageData', params.value);
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
    indexParams.value = await store.dispatch('recommendation/fetchIndexParams', {
        dontNeedLoading: true
    });
};

const pageData = computed(() => store.getters['recommendation/getPageData']);
const auth = computed(() => store.getters['auth/getUser']);
fetchPageParams();
fetchPageData();

const handleExport = async () => {
    try {
        const response = await store.dispatch('recommendation/export', params.value);
        const blob = new Blob([response.data], {type: response.headers['content-type']});
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = 'recommendations.xlsx';
        link.click();
    } catch (error) {
        console.error('Export error:', error);
    }
};

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

// Refresh when menu item or logo is clicked (even on same route)
watch(() => store.getters['sideBar/navTimestamp'], () => {
    if (route.path === '/recommendations') {
        params.value = getDefaultParams();
        doPageFetching();
    }
});

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
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Հերթագրումներ" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
        ]"/>
        <TableActions
            :createRoute="(auth?.superadmin || auth?.user_group?.permissions_by_name.recommendations[0].can_add) ? '/recommendations/create' : null"
            :showFilter="true"
            :filterMenuInitialValue="true"
            :hideApplyButton="true"
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
                    />
                </div>
                <div class="flex flex-col ">
                    <CustomDatePicker
                        placeholder="dd/mm/yyyy"
                        :tableInput="true"
                        label="Հաջորդ զանգի ամսաթիվ"
                        format="Y-m-d"
                        v-model="params.next_call_date"
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
              { title: 'Օգտատեր' },
              { title: 'Գնալու ամսաթիվ<br> և ժամ' },
              { title: 'Հիվանդի անուն <br> ազգանուն' },
              { title: 'Լրացուցիչ տվյալներ' },
              { title: 'Հիվանդություն' },
              { title: 'Բուժհաստատություն<br> և բժիշկ' },
              { title: 'վիրահատության օր' },
              { title: 'Գործողություն' },
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">#{{ pageData.pagination.showing.from + index }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[130px] break-words whitespace-normal font-medium text-black" :style="{ color: item.color }">
                            {{ formatDate(item.call_date) }}
                            <hr>
                            {{ item.user?.name }} {{ item.user?.last_name }}
                            <hr v-if="item.user?.name">
                            {{ item.find_aboutus }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[120px] break-words whitespace-normal font-medium text-black" :style="{ color: item.color }">{{
                                item.departure_datetime
                            }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[160px] break-words whitespace-normal font-medium text-black" :style="{ color: item.color }">
                            {{ item.patient_full_name }}
                            <hr v-if="item.patient_full_name">
                            {{ item.phone }} <br v-if="item.other_phone ">
                            {{ item.other_phone }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <div
                            class="force-color block w-[200px] break-words whitespace-normal font-medium"
                            :style="{ '--item-color': item.color }"
                            v-html="item.additional_data"
                        ></div>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[130px] break-words whitespace-normal font-medium text-black" :style="{ color: item.color }">
                            {{ item.disease }}
                            <hr v-if="item.disease && item.disease_record">
                            {{ item.disease_record?.name }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[160px] break-words whitespace-normal font-medium text-black" :style="{ color: item.color }">
                            {{ item.medical_and_doctor }}
                            <hr v-if="item.medical_and_doctor && item.hospital_record">
                            {{ item.hospital_record?.name }}
                        </span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[110px] break-words whitespace-normal font-medium text-black" :style="{ color: item.color }">{{
                                formatDate(item.day_surgery)
                            }}</span>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <RouterLink :to="'recommendations/update/' + item.id" @click="openRecord(item.id)">
                                <button
                                    :style="item.id === lastOpenedId ? 'color: #0070C0;' : ''"
                                    class="hover:text-primary"
                                    title="Edit"
                                >
                                    <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                                </button>
                            </RouterLink>
                            <a v-if="auth?.superadmin" :href="'/incomings/update/' + item.id" target="_blank" @click="openRecord(item.id)">
                                <button
                                    class="hover:text-primary"
                                    title="Եկամուտ"
                                >
                                    <font-awesome-icon :icon="['far', 'fa-user-plus']"/>
                                </button>
                            </a>
                            <button
                                @click="openSmsModal(item.phone); openRecord(item.id);"
                                class="hover:text-primary"
                                title="SMS ուղարկել"
                            >
                                <font-awesome-icon :icon="['fas', 'mobile-screen-button']"/>
                            </button>
                            <button
                                v-if="auth.user_group.permissions_by_name.recommendations[0].can_delete"
                                @click="store.commit('recommendation/SET_DELETE_MODAL_VALUE', {
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
            action-variable="recommendation/delete"
            getter-variable="recommendation/getDeleteModelValue"
            mutation-variable="recommendation/SET_DELETE_MODAL_VALUE"
        />

        <!-- SMS Send Modal -->
        <div
            v-if="smsModal"
            class="fixed inset-0 z-9999 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="closeSmsModal"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-black">SMS Ուղարկել</h3>

                <div class="mb-4">
                    <label class="mb-1 block text-sm font-medium text-black">Հեռախոս</label>
                    <input
                        v-model="smsForm.phone"
                        type="text"
                        class="w-full rounded border border-stroke px-4 py-2 text-black outline-none focus:border-primary"
                        placeholder="+374XXXXXXXX"
                    />
                </div>

                <div class="mb-6">
                    <label class="mb-1 block text-sm font-medium text-black">SMS</label>
                    <textarea
                        v-model="smsForm.sms_text"
                        rows="4"
                        class="w-full rounded border border-stroke px-4 py-2 text-black outline-none focus:border-primary"
                        placeholder="SMS տեքստ..."
                    ></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="closeSmsModal"
                        class="rounded border border-stroke px-5 py-2 text-sm font-medium text-black hover:bg-gray-100"
                    >
                        Չեղարկել
                    </button>
                    <button
                        @click="submitSms"
                        :disabled="smsSending"
                        class="rounded bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-opacity-90 disabled:opacity-60"
                    >
                        {{ smsSending ? 'Ուղարկվում է...' : 'Ուղարկել SMS' }}
                    </button>
                </div>
            </div>
        </div>

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
.force-color,
.force-color * {
    color: var(--item-color) !important;
}
</style>

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

const smsModal = ref(false);
const smsSending = ref(false);
const smsForm = ref({ phone: '', sms_text: '' });

const openSmsModal = (phone = '') => {
    smsForm.value = { phone: phone, sms_text: '' };
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
        await fetchPageData();
    } finally {
        smsSending.value = false;
    }
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
    await store.dispatch('smsHistory/fetchPageData', params.value);
};

fetchPageData();

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) {
        params.value.page = 1;
    }
    await updateQueryParams();
    await fetchPageData();
};

const indexParams = ref([]);

const fetchIndexParams = async () => {
    indexParams.value = await store.dispatch('smsHistory/fetchIndexParams', {
        dontNeedLoading: true
    });
};

const pageData = computed(() => store.getters['smsHistory/getPageSmsHistoryData']);
const auth = computed(() => store.getters['auth/getUser']);

fetchIndexParams();

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
        <BreadcrumbDefault pageTitle="ՈԻՂԱԻԿՎԱԾ SMS-ՆԵՐ" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
        ]"/>
        <TableActions
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
                    text: 'Անուն'
                }
            }"
            :pagination="pageData.pagination"
            :columns="[
              { title: 'ID', key: 'id' },
              { title: 'SMS ամսաթիվը' },
              { title: 'Հեռախոս' },
              { title: 'SMS' },
              { title: 'Գործողություն' },
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="index">
                <tr>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">#{{ item.id }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.sms_date }}</span>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ item.phone }}</span>
                    </td>
                    <td  class="py-5 px-4 pl-9 xl:pl-11">
                        <span class="block w-[322px] break-words whitespace-normal font-medium text-black">
                            {{ item.sms_text }}
                        </span>
                    </td>
                    <td class="py-5 px-4">
                        <div class="flex items-center space-x-3.5">
                            <button
                                class="hover:text-primary"
                                title="Send"
                                @click="openSmsModal(item.phone)"
                            >
                                <font-awesome-icon :icon="['fas', 'mobile-screen-button']"/>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </CustomTable>

        <DeleteModal
            @fetch="fetchPageData()"
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
</style>

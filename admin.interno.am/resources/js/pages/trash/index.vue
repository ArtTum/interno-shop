<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CustomTable from "@components/global/CustomTable.vue";

import {computed, ref, watch} from 'vue'
import {useStore} from "vuex";
import {useRoute, useRouter} from "vue-router";

const store = useStore();
const route = useRoute();
const router = useRouter();

const getDefaultParams = () => ({
    page: 1,
    per_page: 200,
    search: '',
    ordering_field: 'id',
    ordering_direction: 'desc',
});

const params = ref({
    page: Number(route.query.page) || 1,
    per_page: Number(route.query.per_page) || 200,
    search: route.query.search || '',
    ordering_field: route.query.ordering_field || 'id',
    ordering_direction: route.query.ordering_direction || 'desc',
});

const confirmModal = ref({show: false, id: null, type: null}); // type: 'restore' | 'force'

const fetchPageData = async () => {
    await store.dispatch('trash/fetchPageData', params.value);
};

const doPageFetching = async (isPagination = false) => {
    if (!isPagination) params.value.page = 1;
    await router.push({query: {...route.query, ...params.value}});
    await fetchPageData();
};

const openRestore = (id) => {
    confirmModal.value = {show: true, id, type: 'restore'};
};

const openForceDelete = (id) => {
    confirmModal.value = {show: true, id, type: 'force'};
};

const handleConfirm = async () => {
    if (confirmModal.value.type === 'restore') {
        await store.dispatch('trash/restore', confirmModal.value.id);
    } else {
        await store.dispatch('trash/forceDelete', confirmModal.value.id);
    }
    confirmModal.value = {show: false, id: null, type: null};
    fetchPageData();
};

const pageData = computed(() => store.getters['trash/getPageData']);

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
        <BreadcrumbDefault pageTitle="Ջնջվածներ" :breadcrumb="[
            {path: '/dashboard', title: 'Վահանակ'},
        ]"/>

        <!-- Confirm Modal -->
        <div v-if="confirmModal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm">
                <p class="text-base font-semibold mb-4 text-gray-800">
                    {{ confirmModal.type === 'restore' ? 'Վերականգնե՞լ այս գրառումը' : 'Ամբողջությամ՞բ ջնջել (անդառնալի)' }}
                </p>
                <div class="flex justify-end gap-3">
                    <button @click="confirmModal.show = false"
                        class="px-4 py-2 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">
                        Չեղարկել
                    </button>
                    <button @click="handleConfirm"
                        :class="confirmModal.type === 'restore'
                            ? 'bg-green-600 hover:bg-green-700 text-white'
                            : 'bg-red-600 hover:bg-red-700 text-white'"
                        class="px-4 py-2 rounded font-medium">
                        {{ confirmModal.type === 'restore' ? 'Վերականգնել' : 'Ջնջել' }}
                    </button>
                </div>
            </div>
        </div>

        <CustomTable
            @do-page-fetching="doPageFetching"
            v-model="params"
            :main-search="{
                visibility: true,
                placeholder: 'Փնտրել...',
                tooltip: { button: { showingType: 'info' }, text: 'Անուն' }
            }"
            :pagination="pageData.pagination"
            :columns="[
                { title: '#', key: 'id' },
                { title: 'Հիվանդ' },
                { title: 'Հեռախոս' },
                { title: 'Տեսակ' },
                { title: 'Ով ջնջել' },
                { title: 'Ջնջման ամսաթիվ' },
                { title: 'Գործողություններ' },
            ]"
        >
            <template v-for="(item, index) in pageData.data" :key="item.id">
                <tr>
                    <td class="py-3 px-4 pl-9 xl:pl-11">
                        <span class="font-medium text-black">{{ pageData.pagination.showing.from + index }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="block w-[160px] break-words whitespace-normal font-medium text-black">
                            {{ item.patient_full_name }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-medium text-black">{{ item.phone }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-medium text-black">
                            {{ item.type === 'recommendations' || !item.type ? 'Հերթագրում' : 'Եկամուտ' }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-medium text-black">
                            {{ item.deletedByUser?.name }} {{ item.deletedByUser?.last_name }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-medium text-black">{{ new Date(item.deleted_at).toLocaleString('ru-RU', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <button
                                @click="openRestore(item.id)"
                                class="rounded bg-green-500 px-3 py-1 text-sm text-white hover:bg-green-600"
                            >
                                Վերականգնել
                            </button>
                            <button
                                @click="openForceDelete(item.id)"
                                class="rounded bg-red-500 px-3 py-1 text-sm text-white hover:bg-red-600"
                            >
                                Ջնջել
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </CustomTable>
    </DefaultLayoutComponent>
</template>
<script setup>
import {ref, watch} from "vue";
import CustomTable from "@components/global/CustomTable.vue";

const props = defineProps({
    pageData: {
        type: Object,
        required: true
    },
    params: {
        type: Object,
        required: true
    },
    modelValue: {
        type: Object,
        required: true
    },
});

const paramsModel = ref(props.modelValue);
watch(paramsModel.value, () => {
    emits('update:modelValue', paramsModel.value)
});

const emits = defineEmits([
    'export-datatable', 'do-page-fetching', 'update:modelValue', 'export-full-list'
]);

const dataTableDropDown = ref(false);
const tableHeaders = ref([]);

watch(() => props.pageData.data, () => {
    tableHeaders.value = [];
    if (props.pageData.data?.dataTable) {
        for (let i = 0; i < props.pageData.data?.dataTable.header.length; i++) {
            tableHeaders.value.push({title: props.pageData.data?.dataTable.header[i]});
        }
        tableHeaders.value.push('');
    }
}, {immediate: true, deep: true});

</script>

<template>
    <div>
        <div class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="relative">
                <div class="flex justify-end pr-5 pt-2">
                    <button @click="dataTableDropDown = !dataTableDropDown">
                        <font-awesome-icon :icon="['far', 'ellipsis']" class="size-7"/>
                    </button>
                </div>
                <div
                    v-show="dataTableDropDown"
                    ref="target"
                    class="absolute right-0 top-full z-40 w-40 space-y-1 rounded-sm border border-stroke bg-white p-1.5 shadow-default dark:border-strokedark dark:bg-boxdark"
                >
                    <button
                        @click="emits('export-datatable', 'margin'), dataTableDropDown = false"
                        type="button"
                        class="flex w-full items-center gap-2 rounded-sm py-1.5 px-4 text-left text-sm hover:bg-gray dark:hover:bg-meta-4"
                    >
                        <font-awesome-icon :icon="['far', 'file-export']"/>
                        Export table
                    </button>
                    <button
                        @click="emits('export-full-list', 'margin'), dataTableDropDown = false"
                        type="button"
                        class="flex w-full items-center gap-2 rounded-sm py-1.5 px-4 text-left text-sm hover:bg-gray dark:hover:bg-meta-4"
                    >
                        <font-awesome-icon :icon="['far', 'file-export']"/>
                        Export all
                    </button>
                </div>
            </div>

        </div>
        <CustomTable
            class="text-sm"
            @do-page-fetching="emits('do-page-fetching')"
            v-model="paramsModel"
            :pagination="pageData.data?.pagination"
            :columns="tableHeaders"
            :main-search="{visibility: false}"
        >
            <template v-for="(item, index) in pageData.data?.dataTable.data" :key="index">
                <tr>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">
                            <a :href="`/orders/show/${item[0]}`" target="_blank">
                            <span class="text-sm font-medium text-black hover:text-primary"> #{{ item[0] }}</span>
                                <font-awesome-icon
                                    class="text-primary ml-2"
                                    :icon="['fass', 'up-right-from-square']"
                                />
                            </a>
                        </h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[1] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[2] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[3] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[4] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[5] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[6] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[7] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[8] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[9] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[10] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[11] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[12] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <h5 class="font-medium text-black">{{ item[13] }}</h5>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11">
                        <template v-if="item[14] === 'Not'">
                            Not
                        </template>
                        <template v-else>
                            <p
                                class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium bg-danger text-danger"
                            >
                                {{ item[14] }}
                            </p>
                        </template>
                    </td>
                    <td class="py-5 px-4 pl-9 xl:pl-11"></td>
                </tr>
            </template>
        </CustomTable>
    </div>
</template>

<style scoped>

</style>

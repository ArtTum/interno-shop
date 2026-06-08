<script setup>
import ChartSix from "@components/Charts/ChartSix.vue";
import ChartTabsCoupon from "@components/analytics/ChartTabsCoupon.vue";
import CustomTableSecond from "@components/global/CustomTableSecond.vue";

import {ref} from "vue";

const currentComparingTabIndex = ref(0);
const currentComparingTabValues = [
    'order_count_series', 'total_amount'
];

defineProps({
    pageData: {
        type: Object,
        required: true
    },
    params: {
        type: Object,
        required: true
    },
});

const emits = defineEmits([
    'export-datatable'
]);

const dataTableDropDown = ref(false);

</script>

<template>
    <div class="mt-4" v-if="pageData.data?.chartDataLabels && pageData.data?.chartDataSeries?.[currentComparingTabValues[currentComparingTabIndex]]">
        <ChartTabsCoupon
            @updateCurrentComparingTabIndex="(newIndex) => {
                currentComparingTabIndex = newIndex
            }"
            :current-comparing-tab-index="currentComparingTabIndex"
        >
        </ChartTabsCoupon>
        <ChartSix
            :labels="pageData.data?.chartDataLabels"
            :series="pageData.data?.chartDataSeries?.[currentComparingTabValues[currentComparingTabIndex]]"
        >
        </ChartSix>

        <div class="mt-3" v-if="pageData.data?.dataTable">

            <CustomTableSecond
                title=""
                class="text-sm"
            >
                <template #header>
                    <div>
                        <div class="relative">
                            <button @click="dataTableDropDown = !dataTableDropDown">
                                <font-awesome-icon :icon="['far', 'ellipsis']" class="size-7"/>
                            </button>
                            <div
                                v-show="dataTableDropDown"
                                ref="target"
                                class="absolute left-0 top-full z-40 w-40 space-y-1 rounded-sm border border-stroke bg-white p-1.5 shadow-default dark:border-strokedark dark:bg-boxdark"
                            >
                                <button
                                    @click="emits('export-datatable', 'revenue'), dataTableDropDown = false"
                                    type="button"
                                    class="flex w-full items-center gap-2 rounded-sm py-1.5 px-4 text-left text-sm hover:bg-gray dark:hover:bg-meta-4"
                                >
                                    <font-awesome-icon :icon="['far', 'file-export']"/>
                                    Export
                                </button>
                            </div>
                        </div>

                    </div>
                </template>

                <template #content>
                    <div
                        class="grid grid-cols-3 border-t border-stroke py-4.5 px-7 text-black font-bold"
                        :class="{'!font-bold text-title-md': index === pageData.data?.dataTable.data.length - 1}"
                    >
                        <div class="col-span-1 flex items-center">
                            <p>{{ pageData.data?.dataTable.header[0] }}</p>
                        </div>
                        <div class="col-span-1 items-center sm:flex">
                            <p>{{ pageData.data?.dataTable.header[1] }}</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p>{{ pageData.data?.dataTable.header[2] }}</p>
                        </div>
                    </div>

                    <template
                        v-for="(row, index) in pageData.data?.dataTable.data" :key="index"
                    >
                        <div
                            class="grid grid-cols-3 border-t border-stroke py-4.5 px-7 text-black font-medium">
                            <div class="col-span-1 flex items-center">
                                {{ row[0] }}
                            </div>
                            <div class="col-span-1 flex items-center">
                                {{ row[1] }}
                            </div>
                            <div class="col-span-1 flex items-center">
                                {{ row[2] }}
                            </div>
                        </div>
                    </template>
                </template>
            </CustomTableSecond>
        </div>
    </div>
</template>

<style scoped>

</style>


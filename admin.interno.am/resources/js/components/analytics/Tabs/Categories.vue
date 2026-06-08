<script setup>
import {computed, ref} from "vue";
import ChartTabsProduct from "@components/analytics/ChartTabsProduct.vue";
import ChartSix from "@components/Charts/ChartSix.vue";
import CustomTableSecond from "@components/global/CustomTableSecond.vue";

const currentComparingTabIndex = ref(0);
const currentComparingTabValues = [
    'total_sold', 'total_subtotal', 'total_revenue'
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

const usePercentageChange = (newValue, oldValue) => {
    if (oldValue === 0) {
        return newValue > 0 ? 100 : newValue < 0 ? -100 : 0;
    }

    return parseFloat(((newValue - oldValue) / Math.abs(oldValue) * 100).toFixed(2));
}
</script>

<template>
    <div class="mt-4" v-if="params.category_id > 0 && pageData.data?.chartDataLabels">
        <ChartTabsProduct
            @updateCurrentComparingTabIndex="(newIndex) => {
                currentComparingTabIndex = newIndex
            }"
            :current-comparing-tab-index="currentComparingTabIndex"
        >
        </ChartTabsProduct>
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
                                    @click="emits('export-datatable', `${pageData.data?.selectedCategorySlug}`), dataTableDropDown = false"
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
                        class="grid grid-cols-4 border-t border-stroke py-4.5 px-7 text-black font-bold"
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
                        <div class="col-span-1 flex items-center">
                            <p>{{ pageData.data?.dataTable.header[3] }}</p>
                        </div>
                    </div>

                    <template
                        v-for="(row, index) in pageData.data?.dataTable.data" :key="index"
                    >
                        <div
                            class="grid grid-cols-4 border-t border-stroke py-4.5 px-7 text-black font-medium">
                            <div class="col-span-1 flex items-center">
                                {{ row[0] }}
                            </div>
                            <div class="col-span-1 flex items-center">
                                {{ row[1] }}
                            </div>
                            <div class="col-span-1 flex items-center">
                                {{ row[2] }}
                            </div>
                            <div class="col-span-1 flex items-center">
                                {{ row[3] }}
                            </div>
                        </div>
                    </template>
                </template>
            </CustomTableSecond>
        </div>
    </div>
    <div v-else-if="!params.category_id || params.category_id <= 0">
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
                                    @click="emits('export-datatable', 'categories'), dataTableDropDown = false"
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
                        class="grid grid-cols-5 border-t border-stroke py-4.5 px-7 text-black font-bold"
                        :class="{'grid-cols-9': params.compared_order_date_from}"
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
                        <div class="col-span-1 flex items-center">
                            <p>{{ pageData.data?.dataTable.header[3] }}</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p>{{ pageData.data?.dataTable.header[4] }}</p>
                        </div>
                        <div class="col-span-1 flex items-center">
                            <p>{{ pageData.data?.dataTable.header[5] }}</p>
                        </div>
                        <template v-if="params.compared_order_date_from">
                            <div class="col-span-1 flex items-center">
                                <p>{{ pageData.data?.dataTable.header[6] }}</p>
                            </div>
                            <div class="col-span-1 flex items-center">
                                <p>{{ pageData.data?.dataTable.header[7] }}</p>
                            </div>
                            <div class="col-span-1 flex items-center">
                                <p>{{ pageData.data?.dataTable.header[8] }}</p>
                            </div>
                        </template>
                    </div>

                    <template
                        v-for="(row, index) in pageData.data?.dataTable.data" :key="index"
                    >
                        <div
                            class="grid grid-cols-5 border-t border-stroke py-4.5 px-7 text-black font-medium"
                            :class="{'grid-cols-9': params.compared_order_date_from, '!font-bold text-title-md': !params.compared_order_date_from && index === pageData.data?.dataTable.data.length - 1}"
                        >
                            <div class="col-span-1 flex items-center">
                                {{ row[0] }}
                            </div>
                            <div class="col-span-1 flex items-center">
                                {{ row[1] }}
                                <template v-if="params.compared_order_date_from">
                                     <span
                                         class="flex items-center gap-1 text-sm font-medium ml-2"
                                         :class="{ 'text-meta-3': usePercentageChange(row[1], row[2]) > 0, 'text-danger': usePercentageChange(row[1], row[2]) < 0 , 'text-warning': usePercentageChange(row[1], row[2]) === 0 }"
                                     >
                                        ({{ usePercentageChange(row[1], row[2]) }}%)

                                        <svg
                                            v-if="usePercentageChange(row[1], row[2]) !== 0"
                                            width="15"
                                            height="15"
                                            viewBox="0 0 15 15"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            :class="{'deg-180': usePercentageChange(row[1], row[2]) < 0}"
                                        >
                                          <g clip-path="url(#clip0_554_43030)">
                                            <path
                                                d="M13.4731 5.62415H9.95118C9.68868 5.62415 9.46993 5.8429 9.46993 6.1054C9.46993 6.3679 9.68868 6.58665 9.95118 6.58665H12.1168L9.3168 8.4679C9.20743 8.5554 9.0543 8.5554 8.92305 8.4679L6.03555 6.56477C5.57618 6.25852 5.00743 6.25852 4.54805 6.56477L1.1793 8.8179C0.960552 8.97102 0.894927 9.27727 1.04805 9.49602C1.13555 9.62727 1.28868 9.71477 1.46368 9.71477C1.55118 9.71477 1.66055 9.6929 1.72618 9.62727L5.1168 7.37415C5.22618 7.28665 5.3793 7.28665 5.51055 7.37415L8.39805 9.29915C8.85743 9.6054 9.42618 9.6054 9.88555 9.29915L12.9699 7.22102V9.64915C12.9699 9.91165 13.1887 10.1304 13.4512 10.1304C13.7137 10.1304 13.9324 9.91165 13.9324 9.64915V6.1054C13.9762 5.8429 13.7356 5.62415 13.4731 5.62415Z"
                                                :fill="usePercentageChange(row[1], row[2]) > 0 ? '#10B981' : '#dc3545'"
                                            />
                                          </g>
                                          <defs>
                                            <clipPath id="clip0_554_43030">
                                              <rect
                                                  width="14"
                                                  height="14"
                                                  fill="white"
                                                  transform="translate(0.45752 0.877319)"
                                              />
                                            </clipPath>
                                          </defs>
                                        </svg>
                                  </span>
                                </template>
                            </div>
                            <div class="col-span-1 flex items-center">
                                {{ row[2] }}
                            </div>
                            <div class="col-span-1 flex items-center">
                                {{ row[3] }}
                                <template v-if="params.compared_order_date_from">
                                     <span
                                         class="flex items-center gap-1 text-sm font-medium ml-2"
                                         :class="{ 'text-meta-3': usePercentageChange(row[3], row[4]) > 0, 'text-danger': usePercentageChange(row[3], row[4]) < 0 , 'text-warning': usePercentageChange(row[3], row[4]) === 0 }"
                                     >
                                        ({{ usePercentageChange(row[3], row[4]) }}%)

                                        <svg
                                            v-if="usePercentageChange(row[3], row[4]) !== 0"
                                            width="15"
                                            height="15"
                                            viewBox="0 0 15 15"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            :class="{'deg-180': usePercentageChange(row[3], row[4]) < 0}"
                                        >
                                          <g clip-path="url(#clip0_554_43030)">
                                            <path
                                                d="M13.4731 5.62415H9.95118C9.68868 5.62415 9.46993 5.8429 9.46993 6.1054C9.46993 6.3679 9.68868 6.58665 9.95118 6.58665H12.1168L9.3168 8.4679C9.20743 8.5554 9.0543 8.5554 8.92305 8.4679L6.03555 6.56477C5.57618 6.25852 5.00743 6.25852 4.54805 6.56477L1.1793 8.8179C0.960552 8.97102 0.894927 9.27727 1.04805 9.49602C1.13555 9.62727 1.28868 9.71477 1.46368 9.71477C1.55118 9.71477 1.66055 9.6929 1.72618 9.62727L5.1168 7.37415C5.22618 7.28665 5.3793 7.28665 5.51055 7.37415L8.39805 9.29915C8.85743 9.6054 9.42618 9.6054 9.88555 9.29915L12.9699 7.22102V9.64915C12.9699 9.91165 13.1887 10.1304 13.4512 10.1304C13.7137 10.1304 13.9324 9.91165 13.9324 9.64915V6.1054C13.9762 5.8429 13.7356 5.62415 13.4731 5.62415Z"
                                                :fill="usePercentageChange(row[3], row[4]) > 0 ? '#10B981' : '#dc3545'"
                                            />
                                          </g>
                                          <defs>
                                            <clipPath id="clip0_554_43030">
                                              <rect
                                                  width="14"
                                                  height="14"
                                                  fill="white"
                                                  transform="translate(0.45752 0.877319)"
                                              />
                                            </clipPath>
                                          </defs>
                                        </svg>
                                  </span>
                                </template>
                            </div>
                            <div class="col-span-1 flex items-center">
                                {{ row[4] }}
                            </div>
                            <template v-if="params.compared_order_date_from">
                                <div class="col-span-1 flex items-center">
                                    <p>{{ row[5] }}</p>
                                    <template v-if="params.compared_order_date_from">
                                     <span
                                         class="flex items-center gap-1 text-sm font-medium ml-2"
                                         :class="{ 'text-meta-3': usePercentageChange(row[5], row[6]) > 0, 'text-danger': usePercentageChange(row[5], row[6]) < 0 , 'text-warning': usePercentageChange(row[5], row[6]) === 0 }"
                                     >
                                        ({{ usePercentageChange(row[5], row[6]) }}%)

                                        <svg
                                            v-if="usePercentageChange(row[5], row[6]) !== 0"
                                            width="15"
                                            height="15"
                                            viewBox="0 0 15 15"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            :class="{'deg-180': usePercentageChange(row[5], row[6]) < 0}"
                                        >
                                          <g clip-path="url(#clip0_554_43030)">
                                            <path
                                                d="M13.4731 5.62415H9.95118C9.68868 5.62415 9.46993 5.8429 9.46993 6.1054C9.46993 6.3679 9.68868 6.58665 9.95118 6.58665H12.1168L9.3168 8.4679C9.20743 8.5554 9.0543 8.5554 8.92305 8.4679L6.03555 6.56477C5.57618 6.25852 5.00743 6.25852 4.54805 6.56477L1.1793 8.8179C0.960552 8.97102 0.894927 9.27727 1.04805 9.49602C1.13555 9.62727 1.28868 9.71477 1.46368 9.71477C1.55118 9.71477 1.66055 9.6929 1.72618 9.62727L5.1168 7.37415C5.22618 7.28665 5.3793 7.28665 5.51055 7.37415L8.39805 9.29915C8.85743 9.6054 9.42618 9.6054 9.88555 9.29915L12.9699 7.22102V9.64915C12.9699 9.91165 13.1887 10.1304 13.4512 10.1304C13.7137 10.1304 13.9324 9.91165 13.9324 9.64915V6.1054C13.9762 5.8429 13.7356 5.62415 13.4731 5.62415Z"
                                                :fill="usePercentageChange(row[5], row[6]) > 0 ? '#10B981' : '#dc3545'"
                                            />
                                          </g>
                                          <defs>
                                            <clipPath id="clip0_554_43030">
                                              <rect
                                                  width="14"
                                                  height="14"
                                                  fill="white"
                                                  transform="translate(0.45752 0.877319)"
                                              />
                                            </clipPath>
                                          </defs>
                                        </svg>
                                  </span>
                                    </template>
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <p>{{ row[6] }}</p>
                                </div>
                            </template>
                            <template v-if="params.compared_order_date_from">
                                <div class="col-span-1 flex items-center">
                                    <p>{{ row[7] }}</p>
                                    <template v-if="params.compared_order_date_from">
                                     <span
                                         class="flex items-center gap-1 text-sm font-medium ml-2"
                                         :class="{ 'text-meta-3': usePercentageChange(row[7], row[8]) > 0, 'text-danger': usePercentageChange(row[7], row[8]) < 0 , 'text-warning': usePercentageChange(row[7], row[8]) === 0 }"
                                     >
                                        ({{ usePercentageChange(row[7], row[8]) }}%)

                                        <svg
                                            v-if="usePercentageChange(row[7], row[8]) !== 0"
                                            width="15"
                                            height="15"
                                            viewBox="0 0 15 15"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            :class="{'deg-180': usePercentageChange(row[7], row[8]) < 0}"
                                        >
                                          <g clip-path="url(#clip0_554_43030)">
                                            <path
                                                d="M13.4731 5.62415H9.95118C9.68868 5.62415 9.46993 5.8429 9.46993 6.1054C9.46993 6.3679 9.68868 6.58665 9.95118 6.58665H12.1168L9.3168 8.4679C9.20743 8.5554 9.0543 8.5554 8.92305 8.4679L6.03555 6.56477C5.57618 6.25852 5.00743 6.25852 4.54805 6.56477L1.1793 8.8179C0.960552 8.97102 0.894927 9.27727 1.04805 9.49602C1.13555 9.62727 1.28868 9.71477 1.46368 9.71477C1.55118 9.71477 1.66055 9.6929 1.72618 9.62727L5.1168 7.37415C5.22618 7.28665 5.3793 7.28665 5.51055 7.37415L8.39805 9.29915C8.85743 9.6054 9.42618 9.6054 9.88555 9.29915L12.9699 7.22102V9.64915C12.9699 9.91165 13.1887 10.1304 13.4512 10.1304C13.7137 10.1304 13.9324 9.91165 13.9324 9.64915V6.1054C13.9762 5.8429 13.7356 5.62415 13.4731 5.62415Z"
                                                :fill="usePercentageChange(row[7], row[8]) > 0 ? '#10B981' : '#dc3545'"
                                            />
                                          </g>
                                          <defs>
                                            <clipPath id="clip0_554_43030">
                                              <rect
                                                  width="14"
                                                  height="14"
                                                  fill="white"
                                                  transform="translate(0.45752 0.877319)"
                                              />
                                            </clipPath>
                                          </defs>
                                        </svg>
                                  </span>
                                    </template>
                                </div>
                                <div class="col-span-1 flex items-center">
                                    <p>{{ row[8] }}</p>
                                </div>
                            </template>
                        </div>
                    </template>
                </template>
            </CustomTableSecond>
        </div>
    </div>
</template>

<style scoped>

</style>


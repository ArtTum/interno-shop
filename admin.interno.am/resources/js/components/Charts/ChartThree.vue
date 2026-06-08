<script setup>
import {computed, ref, watch} from 'vue'

import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
    series: {
        type: Array,
        required: true
    },
    labels: {
        type: Array,
        required: true
    },
    table: {
        type: Array,
        required: true
    }
});

const chart = ref(null)

const labelsRef = ref(props.labels);
const seriesRef = ref(props.series);

watch(() => props.labels, () => {
    labelsRef.value = props.labels;
});

watch(() => props.series, () => {
    seriesRef.value = props.series;
});

const apexOptions = computed(() => ({
    chart: {
        type: 'donut',
        width: 380
    },
    colors: ['#3C50E0', '#6577F3', '#8FD0EF', '#0FADCF', '#13C296', '#639792', '#2d6e54', '#fea60b', '#e7ca33', '#f5c77f'],
    labels: labelsRef.value,
    legend: {
        show: false,
        position: 'bottom'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                background: 'transparent'
            }
        }
    },
    dataLabels: {
        enabled: false
    },
    responsive: [
        {
            breakpoint: 640,
            options: {
                chart: {
                    width: 200
                }
            }
        }
    ]
}));
</script>

<template>
    <div
        class="col-span-12 rounded-sm border border-stroke bg-white px-5 pt-7.5 pb-5 shadow-default dark:border-strokedark dark:bg-boxdark xl:col-span-5 max-xl:px-3 max-xl:py-3"
    >
        <div class="mb-2">
            <div id="chartThree" class="mx-auto flex justify-center">
                <VueApexCharts
                    type="donut"
                    width="340"
                    :options="apexOptions"
                    :series="seriesRef"
                    ref="chart"
                />
            </div>
        </div>
        <div class="-mx-8 flex flex-wrap items-center justify-center gap-y-3">
            <div class="w-full px-8 sm:w-1/2" v-for="(row, index) in table" :key="index">
                <div class="flex w-full items-center">
                    <span
                        class="mr-2 block h-3 w-full max-w-3 rounded-full bg-primary"
                        :style="`background-color: ${apexOptions.colors[index <= apexOptions.colors.length - 1 ? index : (index % (apexOptions.colors.length - 1)) - 1]}`"
                    ></span>
                    <p class="flex w-full justify-between text-sm font-medium text-black dark:text-white">
                        <span> {{ row.label }} </span>
                        <span> {{ row.value }}% </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

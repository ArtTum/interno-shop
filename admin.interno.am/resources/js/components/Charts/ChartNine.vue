<script setup>
import {ref} from 'vue'

import VueApexCharts from 'vue3-apexcharts'
import CustomSelect from "@components/global/CustomSelect.vue";

const props = defineProps({
    title: {
        type: String
    },
    chartData: {
        type: Object
    },
    params: {
        type: Object
    },
    needAddressTypeDropwdown: {
        type: Boolean,
        default: false
    },
});

const chart = ref(null)

const charByCountry = [
    {value: 'order_billing_addresses', label: 'Billing'},
    {value: 'order_shipping_addresses', label: 'Shipping'},
];

const apexOptions = {
    colors: ['#3C50E0', '#80CAEE', '#13C296', '#fea60b', '#6577f3'],
    chart: {
        fontFamily: 'Satoshi, sans-serif',
        type: 'bar',
        height: 250,
        toolbar: {
            show: true
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '35%',
            endingShape: 'rounded',
            borderRadius: 0
        }
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 4,
        colors: ['transparent']
    },
    xaxis: {
        categories: props.chartData.labels,
        axisBorder: {
            show: false
        },
        axisTicks: {
            show: false
        }
    },
    legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'center',
        fontFamily: 'Satoshi',

        markers: {
            radius: 99
        }
    },
    yaxis: {
        title: false
    },
    grid: {
        strokeDashArray: 7,
        yaxis: {
            lines: {
                show: true
            }
        }
    },
    fill: {
        opacity: 1
    },

    tooltip: {
        x: {
            show: false
        },
        y: {
            formatter: function (val) {
                return val
            }
        }
    }
}
</script>

<template>
    <div
        class="rounded-sm border border-stroke bg-white shadow-default"
    >
        <div
            class="flex flex-col gap-2 border-b border-stroke py-5 px-6 sm:flex-row sm:justify-between"
        >
            <div class="flex min-w-[400px]">
                <div class="flex-shrink-0">
                    <h2 class="text-title-md2 font-bold text-black">{{ title }}</h2>
                </div>
                <div class="w-full ml-3">
                    <CustomSelect
                        v-if="needAddressTypeDropwdown"
                        v-model="params.chart_by_country"
                        mode="single"
                        label=""
                        :options="charByCountry"
                        :searchable="false"
                        :canClear="false"
                        :close-on-select="true"
                        :invalid-feedback-place="false"
                        placeholder="Select type"
                    />
                </div>
            </div>
        </div>

        <div class="px-6 pt-4">
            <div id="chartNine" class="-ml-5">
                <VueApexCharts
                    type="bar"
                    height="400"
                    :options="apexOptions"
                    :series="chartData.series"
                    ref="chart"
                />
            </div>
        </div>
    </div>
</template>

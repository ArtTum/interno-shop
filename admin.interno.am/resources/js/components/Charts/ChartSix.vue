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
    legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'center'
    },
    colors: ['#13C296', '#3C50E0'],
    chart: {
        fontFamily: 'Satoshi, sans-serif',
        height: 400,
        type: 'area',
        toolbar: {
            show: true
        },
        zoom: {
            enabled: false
        }
    },
    fill: {
        gradient: {
            enabled: true,
            opacityFrom: 0.55,
            opacityTo: 0
        }
    },
    responsive: [
        {
            breakpoint: 1024,
            options: {
                chart: {
                    height: 400
                }
            }
        },
        {
            breakpoint: 1366,
            options: {
                chart: {
                    height: 400
                }
            }
        }
    ],
    stroke: {
        width: [2, 2],
        curve: 'smooth'
    },

    markers: {
        size: 0
    },
    labels: {
        show: true,
        position: 'top'
    },
    grid: {
        strokeDashArray: 7,
        xaxis: {
            lines: {
                show: true
            }
        },
        yaxis: {
            lines: {
                show: true
            }
        }
    },
    dataLabels: {
        enabled: true
    },
    xaxis: {
        type: 'category',
        categories: labelsRef.value,
        axisBorder: {
            show: true
        },
        axisTicks: {
            show: true
        }
    },
    yaxis: {
        title: {
            style: {
                fontSize: '0px'
            }
        }
    }
}));
</script>

<template>
    <div
        class="col-span-12 rounded-sm border border-t-0 border-stroke bg-white px-5 pb-5 shadow-default"
    >
        <div>
            <div id="chartSix" class="-ml-5"
                 v-if="seriesRef && ((Array.isArray && seriesRef.length) || Object.keys(seriesRef).length)">
                <VueApexCharts
                    type="area"
                    height="400"
                    :options="apexOptions"
                    :series="seriesRef"
                    ref="chart"
                />
            </div>
        </div>
    </div>
</template>

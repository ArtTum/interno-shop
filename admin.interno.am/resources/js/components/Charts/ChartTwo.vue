<script setup>
import {computed, ref, watch} from 'vue'
// @ts-ignore
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
    chartLabel: {
        type: String,
        required: false
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
    colors: ['#3056D3', '#80CAEE'],
    chart: {
        type: 'bar',
        height: 400,
        stacked: true,
        toolbar: {
            show: true
        },
        zoom: {
            enabled: false
        }
    },
    responsive: [
        {
            breakpoint: 1536,
            options: {
                plotOptions: {
                    bar: {
                        borderRadius: 0,
                        columnWidth: '25%'
                    }
                }
            }
        }
    ],
    plotOptions: {
        bar: {
            horizontal: false,
            borderRadius: 0,
            columnWidth: '25%',
            borderRadiusApplication: 'end',
            borderRadiusWhenStacked: 'last'
        }
    },
    dataLabels: {
        enabled: false
    },
    xaxis: {
        type: 'category',
        categories: labelsRef.value
    },
    legend: {
        position: 'top',
        horizontalAlign: 'left',
        fontFamily: 'Satoshi',
        fontWeight: 500,
        fontSize: '14px',

        markers: {
            radius: 99
        }
    },
    fill: {
        opacity: 1
    }
}));
</script>

<template>
  <div
      class="col-span-12 rounded-sm border border-t-0 border-stroke bg-white px-5 pb-5 shadow-default"
  >
    <div class="mb-4 justify-between gap-4 sm:flex" v-if="chartLabel">
      <div>
        <h4 class="text-xl font-bold text-black dark:text-white">{{ chartLabel }}</h4>
      </div>
      <div>
        <div class="relative z-20 inline-block">
        </div>
      </div>
    </div>

    <div>
      <div id="chartTwo" class="-ml-5" v-if="seriesRef && ((Array.isArray && seriesRef.length) || Object.keys(seriesRef).length)">
        <VueApexCharts
          type="bar"
          height="400"
          :options="apexOptions"
          :series="seriesRef"
          ref="chart"
        />
      </div>
    </div>
  </div>
</template>

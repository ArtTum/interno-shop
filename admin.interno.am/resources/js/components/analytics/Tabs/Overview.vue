<script setup>
import InfoBox from "@components/analytics/InfoBox.vue";
import ChartSix from "@components/Charts/ChartSix.vue";
import ChartTabs from "@components/Charts/ChartTabs.vue";
import ChartNine from "@components/Charts/ChartNine.vue";
import {ref} from "vue";

const currentComparingTabIndex = ref(0);
const currentComparingTabValues = ['total_revenue_series', 'total_revenue_net_series', 'order_count_series', 'total_customers_series'];

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

</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
        <InfoBox
            title="Total revenue (total sales)"
            :box-icon="['far', 'euro-sign']"
            :total="pageData.data?.boxesInfo?.total_revenue"
            :daily-avg="pageData.data?.boxesInfo?.total_revenue_daily"
            :compared-info="!!pageData.data?.comparedBoxInfo"
            :total-compared="pageData.data?.comparedBoxInfo?.total_revenue"
            :daily-avg-compared="pageData.data?.comparedBoxInfo?.total_revenue_daily"
            daily-avg-text="Daily avg"
            :currency-symbol="pageData.base_currency_symbol"
            :compared-percent="pageData.data?.cumpareingNumbers?.total_revenue_comp"
        >
        </InfoBox>
        <InfoBox
            title="Total revenue (net sales)"
            :box-icon="['far', 'euro-sign']"
            :total="pageData.data?.boxesInfo?.total_revenue_net"
            :daily-avg="pageData.data?.boxesInfo?.total_revenue_net_daily"
            :compared-info="!!pageData.data?.comparedBoxInfo"
            :total-compared="pageData.data?.comparedBoxInfo?.total_revenue_net"
            :daily-avg-compared="pageData.data?.comparedBoxInfo?.total_revenue_net_daily"
            daily-avg-text="Daily avg"
            :currency-symbol="pageData.base_currency_symbol"
            :compared-percent="pageData.data?.cumpareingNumbers?.total_revenue_net_comp"
        >
        </InfoBox>
        <InfoBox
            title="Number of orders"
            :box-icon="['far', 'bags-shopping']"
            :total="pageData.data?.boxesInfo?.order_count"
            :daily-avg="pageData.data?.boxesInfo?.order_count_daily"
            :compared-info="!!pageData.data?.comparedBoxInfo"
            :total-compared="pageData.data?.comparedBoxInfo?.order_count"
            :daily-avg-compared="pageData.data?.comparedBoxInfo?.order_count_daily"
            daily-avg-text="Daily avg"
            :currency-symbol="null"
            :compared-percent="pageData.data?.cumpareingNumbers?.order_count_comp"
        >
        </InfoBox>
        <InfoBox
            title="Number of orders customers"
            :box-icon="['far', 'user']"
            :total="pageData.data?.boxesInfo?.total_customers"
            :daily-avg="pageData.data?.boxesInfo?.total_customers_daily"
            :compared-info="!!pageData.data?.comparedBoxInfo"
            :total-compared="pageData.data?.comparedBoxInfo?.total_customers"
            :daily-avg-compared="pageData.data?.comparedBoxInfo?.total_customers_daily"
            daily-avg-text="Daily avg"
            :currency-symbol="null"
            :compared-percent="pageData.data?.cumpareingNumbers?.total_customers_comp"
        >
        </InfoBox>
    </div>

    <template v-if="!!pageData.data?.comparedBoxInfo">
        <div class="mt-4">
            <ChartTabs
                @updateCurrentComparingTabIndex="(newIndex) => {
                currentComparingTabIndex = newIndex
            }"
                :current-comparing-tab-index="currentComparingTabIndex"
            >
            </ChartTabs>
            <ChartSix :labels="pageData.data?.chartDataLabels"
                      :series="pageData.data?.chartDataSeries[currentComparingTabValues[currentComparingTabIndex]]"></ChartSix>
        </div>
    </template>
    <template v-else>
        <div class="mt-4">
            <template v-if="pageData.data?.byCountryCharInfo">
                <ChartNine
                    title="By countries:"
                    :chart-data="pageData.data.byCountryCharInfo.chartData"
                    :params="params"
                    :need-address-type-dropwdown="true"
                ></ChartNine>
            </template>
        </div>

        <div class="mt-4">
            <template v-if="pageData.data?.byLanguageCharInfo">
                <ChartNine
                    title="By languages:"
                    :chart-data="pageData.data.byLanguageCharInfo.chartData"
                    :params="params"
                ></ChartNine>
            </template>
        </div>
    </template>
</template>

<style scoped>

</style>


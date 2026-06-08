<script setup>
import ChartTabSingle from "@components/Charts/ChartTabSingle.vue";

defineProps({
    currentComparingTabIndex: {
        type: Number
    }
});

const emits = defineEmits([
    'update-current-comparing-tab-index'
]);
</script>

<template>
    <div
        class="col-span-12 rounded-sm border border-b-0 border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark xl:col-span-8"
    >
        <div class="flex justify-between overflow-x-auto">
            <ChartTabSingle
                title="Orders"
                :tab-index="0"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 0)"
            >
            </ChartTabSingle>
            <ChartTabSingle
                title="Total sales"
                :tab-index="1"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 1)"
            >
            </ChartTabSingle>
            <ChartTabSingle
                title="Gross sales"
                :tab-index="2"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 2)"
            >
            </ChartTabSingle>
            <ChartTabSingle
                title="Net sales"
                :tab-index="3"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 3)"
            >
            </ChartTabSingle>
            <ChartTabSingle
                title="Taxes"
                :tab-index="4"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 4)"
            >
            </ChartTabSingle>
            <ChartTabSingle
                title="Shipping"
                :tab-index="5"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 5)"
            >
            </ChartTabSingle>
            <ChartTabSingle
                title="Coupons"
                :tab-index="6"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 6)"
            >
            </ChartTabSingle>
            <ChartTabSingle
                title="Returns"
                :tab-index="7"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 7)"
            >
            </ChartTabSingle>
        </div>
    </div>
</template>

<style scoped>

</style>

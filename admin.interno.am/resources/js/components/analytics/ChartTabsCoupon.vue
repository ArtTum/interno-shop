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
        <div class="flex justify-between">
            <ChartTabSingle
                title="Orders"
                :tab-index="0"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 0)"
            >
            </ChartTabSingle>
            <ChartTabSingle
                title="Total discounted amount"
                :tab-index="1"
                :current-index="currentComparingTabIndex"
                @click="emits('update-current-comparing-tab-index', 1)"
            >
            </ChartTabSingle>
        </div>
    </div>
</template>

<style scoped>

</style>

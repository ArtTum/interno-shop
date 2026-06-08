<script setup>
import ClipLoader from "vue-spinner/src/ClipLoader.vue";

defineProps({
    comparedInfo: {
        type: Boolean
    },
    title: {
        type: String
    },
    total: {
        type: [Number, String]
    },
    totalCompared: {
        type: Number
    },
    dailyAvg: {
        type: Number
    },
    dailyAvgCompared: {
        type: Number
    },
    dailyAvgText: {
        type: String
    },
    currencySymbol: {
        type: String
    },
    boxIcon: {
        type: Array
    },
    comparedPercent: {
        type: Number
    },
    loading: {
        type: Boolean,
        required: false
    },
});

import { formatPrice } from '@/utils/formatPrice';

</script>

<template>
    <div
        class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark max-xl:px-4 max-md:px-2 max-md:py-2"
    >
        <template v-if="loading !== null">
            <template v-if="loading">
                <ClipLoader
                    color="#3C50E0"
                    size="80px"
                />
            </template>
        </template>
        <template v-if="loading === null || loading === false">
            <div class="text-center text-title-sm font-bold text-black">
                {{ title }}
            </div>
            <div class="flex justify-between items-center mt-3">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 ">
                    <font-awesome-icon :icon="boxIcon" class="size-6 text-primary"/>
                </div>
                <div>
                    <h4 class="text-title-md font-bold text-black text-right">{{ currencySymbol }}{{ formatPrice(total) }} <br>
                        <span class="opacity-60 text-title-xsm">
                        <template v-if="comparedInfo"> ({{formatPrice(totalCompared)}})</template>
                    </span>
                    </h4>
                </div>
            </div>

            <div class="mt-4 flex items-end justify-between">
                <div>
                    <template v-if="dailyAvg !== null">
                        <h4 class="text-title-md font-bold text-black dark:text-white">{{ currencySymbol }}{{formatPrice(dailyAvg)}}
                            <template v-if="comparedInfo">
                                <span class="opacity-60 text-title-sm"><template v-if="comparedInfo"> ({{formatPrice(dailyAvgCompared)}})</template></span>
                            </template>
                        </h4>
                        <span class="text-sm font-medium">{{ dailyAvgText }}</span>
                    </template>
                </div>

                <template v-if="comparedInfo">
                <span
                    class="flex items-center gap-1 text-sm font-medium"
                    :class="{ 'text-meta-3': comparedPercent > 0, 'text-danger': comparedPercent < 0 , 'text-warning': comparedPercent === 0 }"
                >
                <svg
                    v-if="comparedPercent !== 0"
                    width="15"
                    height="15"
                    viewBox="0 0 15 15"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    :class="{'deg-180': comparedPercent < 0}"
                >
                  <g clip-path="url(#clip0_554_43030)">
                    <path
                        d="M13.4731 5.62415H9.95118C9.68868 5.62415 9.46993 5.8429 9.46993 6.1054C9.46993 6.3679 9.68868 6.58665 9.95118 6.58665H12.1168L9.3168 8.4679C9.20743 8.5554 9.0543 8.5554 8.92305 8.4679L6.03555 6.56477C5.57618 6.25852 5.00743 6.25852 4.54805 6.56477L1.1793 8.8179C0.960552 8.97102 0.894927 9.27727 1.04805 9.49602C1.13555 9.62727 1.28868 9.71477 1.46368 9.71477C1.55118 9.71477 1.66055 9.6929 1.72618 9.62727L5.1168 7.37415C5.22618 7.28665 5.3793 7.28665 5.51055 7.37415L8.39805 9.29915C8.85743 9.6054 9.42618 9.6054 9.88555 9.29915L12.9699 7.22102V9.64915C12.9699 9.91165 13.1887 10.1304 13.4512 10.1304C13.7137 10.1304 13.9324 9.91165 13.9324 9.64915V6.1054C13.9762 5.8429 13.7356 5.62415 13.4731 5.62415Z"
                        :fill="comparedPercent > 0 ? '#10B981' : '#dc3545'"
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
                {{ comparedPercent }}%
          </span>
                </template>
            </div>
        </template>
    </div>
</template>

<style scoped>

</style>

<script setup>
const props = defineProps({
    buttonParams: {
        type: Object
    },
    tooltipText: {
        type: String
    },
    tooltipClass: {
        type: String,
        default: 'rounded-tr rounded-br bg-primary py-3.5 px-3.5'
    }
});
</script>

<template>
    <div class="">
        <div class="group relative inline-block">
            <button :class="['inline-flex font-medium text-white', tooltipClass]">
                <template v-if="buttonParams.showingType === 'info'">
                    <font-awesome-icon icon="circle-info" class="size-4.5" />
                </template>
                <template v-if="buttonParams.showingType === 'text'">
                    {{ buttonParams.text }}
                </template>
            </button>
            <div class="absolute events-none z-99 min-w-[200px] rounded bg-black py-1.5 px-4.5 text-sm font-medium text-white opacity-0 group-hover:opacity-100 top-full right-1/2 mt-3 sm:translate-x-1/2 max-sm:left-unset max-sm:right-0 ">
                <span class="absolute -z-10 h-2 w-2 rotate-45 rounded-sm bg-black top-[-3px] right-1/2 translate-x-1/2 max-sm:right-[25px]"></span>
                <span v-html="tooltipText"></span>
            </div>
        </div>
    </div>
</template>

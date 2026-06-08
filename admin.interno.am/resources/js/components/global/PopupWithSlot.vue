<script setup>

import CustomButton from "@components/global/CustomButton.vue";

const props = defineProps({
    classes: {
        type: String,
        required: false
    }
});

const emits = defineEmits([
    'close'
]);
</script>

<template>
    <div
        class="fixed top-0 left-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5"
    >
        <div
            ref="target"
            class="w-full max-w-203 rounded-lg bg-white text-center max-h-[80%] overflow-y-scroll"
            :class="classes"
        >
            <div class="relative">
                <CustomButton
                    @click="emits('close')"
                    class="absolute top-3 right-3"
                >
                    <font-awesome-icon :icon="['fas', 'xmark']" class="size-6 ml-auto"/>
                </CustomButton>
            </div>
            <div class="py-12 px-8 max-md:py-9 max-md:px-2 ">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

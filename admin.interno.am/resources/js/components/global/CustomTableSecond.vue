<script setup>
import CustomButton from "@components/global/CustomButton.vue";

const props = defineProps({
    title: {
        type: String,
    },
    buttonInfo: {
        type: Object,
        default: null
    },
})

const emits = defineEmits(['add-item', 'save', 'create-reshipment']);
</script>

<template>
    <div
        class="rounded-sm border border-stroke bg-white shadow-default table-default-holder"
    >
        <template v-if="title !== 'none'">
            <div class="py-6 px-4 md:px-6 xl:px-7.5 flex justify-between">
                <div class="min-w-[33%] text-left">
                    <slot name="header"></slot>
                    <h4 class="text-xl font-bold text-black">{{ title }}</h4>
                </div>
                <div class="min-w-[33%]">
                    <slot name="headerFeature"></slot>
                </div>
                <div class="min-w-[33%]">
                    <template v-if="buttonInfo">
                        <CustomButton
                            :disabled="buttonInfo.disabled"
                            @click="emits(buttonInfo.emitName)"
                            type="button"
                            :class="buttonInfo.classes"
                            class="flex items-center gap-2 rounded py-2 px-4.5 font-medium text-white hover:bg-opacity-80 ml-auto disabled:cursor-default disabled:bg-whiter"
                        >
                            <font-awesome-icon :icon="buttonInfo.icon"/>
                            {{ buttonInfo.title }}
                        </CustomButton>
                    </template>
                </div>
            </div>
        </template>
        <slot name="content"></slot>
    </div>
</template>

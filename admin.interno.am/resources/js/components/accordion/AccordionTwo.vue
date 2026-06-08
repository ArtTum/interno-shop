<script setup>
const props = defineProps({
    parent: Object,
    invalid: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div class="sm:pt-6.5">
        <div class="flex flex-col">
            <div
                :class="{'shadow-danger': invalid}"
                class="rounded-md border border-stroke shadow-md p-5"
            >
                <button
                    type="button"
                    @click="parent.open = !parent.open"
                    class="flex w-full justify-between gap-2"
                >
                    <div class="w-full">
                        <slot name="header"></slot>
                    </div>

                    <div
                        :class="{'border-danger': invalid, 'border-primary': !invalid }"
                        class="flex h-9 w-full max-w-9 items-center justify-center rounded-full border"
                    >
                        <font-awesome-icon
                            :class="{ 'rotate-180': parent.open, 'text-danger': invalid, 'text-primary': !invalid }"
                            icon="chevron-down"/>
                    </div>
                </button>

                <div v-show="parent.open" class="mt-5 duration-200 ease-in-out">
                    <hr class="text-gray my-6">
                    <slot name="content"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

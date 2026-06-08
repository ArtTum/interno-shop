<script setup>
defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    title: {
        type: String,
        default: "Modal Title",
    },
});

const emit = defineEmits(["close", "confirm"]);
const closeModal = () => emit("close");
const confirmAction = () => emit("confirm");

</script>
<template>
    <div v-if="isOpen" class="fixed top-0 left-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5">
        <!-- Modal Box -->
        <div class="bg-white rounded-lg shadow-lg max-w-[1730px] w-full flex flex-col max-h-[99vh]">
            <!-- Modal Header -->
            <div class="flex justify-between items-center px-4 border-b">
                <div class="grid grid-cols-12 gap-4 w-full">
                    <div class="col-span-9 p-4">
                        <h3 class="text-lg font-semibold">{{ title }}</h3>
                    </div>
                    <div class="col-span-3  p-4 flex">
                        <button @click="closeModal" class="text-gray-500 hover:text-gray-700 ml-auto">
                            <font-awesome-icon :icon="['fas', 'xmark']" class="size-6 text-black"/>
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6  min-h-[60vh] overflow-y-auto max-xl:px-0">
                <slot />
            </div>
            <!-- Modal Footer -->
            <div class="flex justify-end p-4 border-t">
                <button
                    @click="closeModal"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
                >
                    Close
                </button>
                <button
                    type="button"
                    @click="confirmAction"
                    class="ml-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Generate Document
                </button>
            </div>
        </div>
    </div>
</template>
<style lang="scss" >
.adb-input {
    font-size: 0.875rem;
    line-height: 1.25rem;
    margin-bottom: 0 !important;
    label {
        margin-bottom: 0 !important;
    }
    input {
        margin: 3px 0;
        padding: 8px;
    }
    .invalid-feedback {
        display: none;
    }
}
</style>

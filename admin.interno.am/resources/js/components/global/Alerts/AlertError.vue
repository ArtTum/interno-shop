<script setup>
const props = defineProps({
    errors: Object
})
</script>
<template>
    <div
        class="flex w-full border-l-6 border-[#F87171] bg-[#F87171] bg-opacity-[15%] px-4 py-5 shadow-md md:p-5"
    >
        <div
            class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#F87171]"
        >
            <font-awesome-icon :icon="['fas', 'xmark']" class="text-danger size-5" />
        </div>
        <div class="w-full">
            <h5
                v-if="Object.keys(errors).length > 1"
                class="mb-3 font-bold text-[#B45454]"
            >
                There were {{ Object.keys(errors).length }} errors with your submission
            </h5>
            <h5 v-else
                class="mb-3 font-bold text-[#B45454]">
                There is a error with your submission
            </h5>
            <ul>
                <template
                    v-for="(error, key) in errors"
                    :key="key"
                >
                    <li
                        :key="index"
                        v-for="(errorEach, index) in error"
                        class="leading-relaxed text-[#CD5D5D]"
                    >
                        {{ errorEach }}
                    </li>
                </template>
            </ul>
        </div>
    </div>
</template>

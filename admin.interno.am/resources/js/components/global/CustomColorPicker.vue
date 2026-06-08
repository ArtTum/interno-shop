<script setup>
import {ref, toRefs, watch} from 'vue';

const props = defineProps({
    modelValue: [String],
});

const {modelValue} = toRefs(props);
const value = ref(modelValue.value);

watch(modelValue, (newVal) => {
    value.value = newVal;
});

const emits = defineEmits(['update:modelValue']);

watch(value, (newVal) => {
    emits('update:modelValue', newVal);
});

const updateColorCode = () => {
    if (/^#[0-9A-Fa-f]{6}$/.test(value.value)) {
    } else {
        alert("Invalid color code! Please use a valid hex code (e.g., #123ABC).");
    }
};
</script>

<template>
    <div>
        <label class="mb-2.5 block font-medium text-black">
            Color code
        </label>
    </div>
    <div class="w-full flex items-center">
        <div>
            <input
                type="color"
                v-model="value"
                @input="updateColorCode"
            />
        </div>

        <div class="w-full">
            <input
                class="w-[200px] uppercase rounded-l-none rounded-lg border border-stroke"
                type="text"
                v-model="value"
                @input="updateColorCode"
                placeholder="#000000"
            />
        </div>
    </div>
</template>

<style scoped>
input[type="color"] {
    width: 40px;
    height: 40px;
    padding: 0;
    border: none;
    cursor: pointer;
}

input[type="text"] {
    padding: 5px;
    font-size: 1rem;
}
</style>

<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomSelect from "@components/global/CustomSelect.vue";

const newItem = ref(null);
const emits = defineEmits([
    'update:modelValue'
]);
import {useStore} from "vuex";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const props = defineProps({
    modelValue: {
        type: Object,
        default: []
    },
    calculators: {
        type: Array,
        default: []
    },
    isUpdate: {
        type: Boolean,
        default: false
    },
});

const {modelValue} = toRefs(props);
const items = ref(modelValue.value);

watch(modelValue, (newVal) => {
    items.value = newVal;

    if (!items.value.length) {
        items.value[0] = {
            calculator_translation_id: null
        }
    }
}, {immediate: true});

watch(
    () => items.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

</script>

<template>
    <div class="grid grid-cols-2 gap-9 text-left mt-5 px-6.5">
        <div class="flex flex-col gap-9">
            <CustomSelect
                label="Related calculator"
                v-model="items[0].calculator_translation_id"
                mode="single"
                :canClear="true"
                placeholder="Select"
                :options="calculators"
                :searchable="true"
                class="py-2 rounded-lg border-stroke bg-transparent"
            />
        </div>
    </div>
</template>

<style scoped>

</style>

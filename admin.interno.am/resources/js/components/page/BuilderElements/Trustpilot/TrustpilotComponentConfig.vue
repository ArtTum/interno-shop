<script setup>

import {computed, ref, toRefs, watch} from "vue";
import Switch from "@components/global/Switch.vue";

const emits = defineEmits([
    'update:modelValue',
    'save'
]);

import {useStore} from "vuex";
const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const props = defineProps({
    modelValue: {
        type: Object,
        default: []
    },
    isUpdate: {
        type: Boolean,
        default: false
    },
    pageTypeName: {
        type: String,
    },
});

const {modelValue} = toRefs(props);

const component = ref(modelValue.value);

watch(modelValue, (newVal) => {
    component.value = newVal;

    if (Object.keys(component.value.config).length === 0) {
        component.value.config = {
            version_light: true,
        }
    }
}, {immediate: true});

watch(
    () => component.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

</script>

<template>
    <div>
        <div class="grid grid-cols-3 gap-9 text-left mt-5">
            <div class="flex flex-col gap-9">
                <Switch
                    @change="(value) => {
                       component.config.version_light = value;
                    }"
                    class="w-fit"
                    :disabled="isUpdate && !auth.user_group.permissions_by_name.reviews[0].can_edit"
                    :value="component.config.version_light"
                    id="version_light"
                    label="Light version"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

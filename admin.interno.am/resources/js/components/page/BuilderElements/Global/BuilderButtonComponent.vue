<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomInput from "@components/global/CustomInput.vue";

import {useStore} from "vuex";
import CustomSelect from "@components/global/CustomSelect.vue";
const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const emits = defineEmits([
    'update:modelValue'
]);

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
            position: 'center',
            url: null,
            button_text: null,
            color: 'btn-primary',
            new_tab: false
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
        <div class="grid grid-cols-3 gap-9 text-left mt-5">
            <div class="flex flex-col">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    v-model="component.config.button_text"
                    name="button_text"
                    label="Button text *"
                    type="text"
                    placeholder="Button text"
                />
                <div>
                    <CustomInput
                        :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                        v-model="component.config.url"
                        name="url"
                        label="URL *"
                        type="text"
                        placeholder="URL"
                    />
                </div>
            </div>
            <div class="flex flex-col gap-9">
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    label="Position *"
                    v-model="component.config.position"
                    mode="single"
                    placeholder="Select *"
                    :options="[
                      {value: 'center', label: 'Center'},
                      {value: 'left', label: 'Left'},
                      {value: 'right', label: 'Right'},
                    ]"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
                <div class="pt-3">
                    <CustomInput
                        :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                        v-model="component.config.new_tab"
                        label="Open on new tab"
                        name="new_tab"
                        type="checkbox"
                        class="mt-auto mb-auto"
                    />
                </div>
            </div>
            <div class="flex flex-col gap-9">
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                    label="Style *"
                    v-model="component.config.color"
                    mode="single"
                    placeholder="Select *"
                    :options="[
                      {value: 'btn-primary', label: 'Primary (blue)'},
                      {value: 'btn-secondary', label: 'Secondary (white)'},
                    ]"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
        </div>
</template>

<style scoped>

</style>

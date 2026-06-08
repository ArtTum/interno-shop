<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

import {computed, ref, toRefs, watch} from "vue";

import {useStore} from "vuex";
import CustomSelect from "@components/global/CustomSelect.vue";

const store = useStore()

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit'
]);

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

import {validate} from "@validation/customValidation.js";
import CustomTextarea from "@components/global/CustomTextarea.vue";

const auth = computed(() => store.getters['auth/getUser']);
</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-2 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomSelect
                    :disabled="!auth.user_group.permissions_by_name.accounting_settings[0].can_edit"
                    class="py-2 rounded-lg  border-stroke bg-transparent"
                    v-model="form.statuses"
                    label="Statuses *"
                    placeholder="Select statues *"
                    :options="[{value:4, label: 'Completed'}, {value:6, label: 'Refunded'}]"
                    :show-labels="true"
                    :close-on-select="false"
                    :canClear="false"
                    mode="tags"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['statuses'] ?? null"
                />
                <template
                    v-for="(item, key) in form.row"
                    :key="key"
                >
                    <CustomInput
                        :disabled="!auth.user_group.permissions_by_name.accounting_settings[0].can_edit"
                        v-if="item.key !== 'statuses' && item.key !== 'additional_fields'"
                        v-model="item['value']"
                        :label="item.label"
                        type="text"
                    />
                    <div v-else>
                        <CustomTextarea
                            :disabled="!auth.user_group.permissions_by_name.accounting_settings[0].can_edit"
                            v-if="item.key === 'additional_fields'"
                            :label="item.label"
                            name="description"
                            :rows="4"
                            v-model="item['value']"
                        />
                    </div>
                </template>
            </div>


            <div class="flex flex-col p-6.5 save-button-fixed">
                <template v-if="auth.user_group.permissions_by_name.accounting_settings[0].can_edit">
                    <div class="flex ml-auto gap-5">
                        <CustomButton
                            @click="emits('cancel')"
                            class="block w-full rounded border border-stroke bg-gray p-3 text-center font-medium text-black hover:bg-opacity-60"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'arrow-rotate-left']"/>
                            Cancel
                        </CustomButton>
                        <CustomButton
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="submit"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </div>
                </template>
            </div>
        </div>

    </form>
</template>

<style scoped>

</style>

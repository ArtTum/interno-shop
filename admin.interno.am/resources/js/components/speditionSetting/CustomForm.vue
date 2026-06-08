<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";

const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    }
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

store.dispatch('speditionSettings/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['speditionSettings/getParams']);
const auth = computed(() => store.getters['auth/getUser']);
const addNewSection = () => {
    form.value.rules.push({});
}

const removeRule = (key) => {
    form.value.rules.splice(key, 1);
}

const filteredCountries = computed(() => {
    const selectedCountries = form.value.rules.map(rule => rule.country).filter(Boolean);

    return params.value.customCountries.map(country => ({
        ...country,
        disabled: selectedCountries.includes(country.value),
    }));
});

const handleCountryChange = () => {
    form.value.errors = validate(form.value);
};

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
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    v-model="form.min_order_weight"
                    name="min_order_weight"
                    label="Min. Order Weight"
                    type="text"
                    placeholder="Enter Order Weight"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['min_order_weight']"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9" style="margin-top: -50px">
            <div class="flex flex-col p-6.5">
                <div v-for="(rule, key) in form.rules" :key="key"
                     class="flex flex-col relative my-6.5 min-h-[100px] border-[1.5px] shadow-default relative top-0 col-span-1 sm:col-span-1 md:col-span-1 ">
                    <button
                        type="button"
                        @click="removeRule(key)"
                        class="hover:text-primary absolute right-2 top-1"
                        title="Delete"
                    >
                        <font-awesome-icon :icon="['fas', 'trash-can']"/>
                    </button>
                    <div class="grid grid-cols-2 gap-9 p-6.5">
                        <div>
                            <CustomSelect
                                class="py-2 rounded-lg border-stroke bg-transparent"
                                v-model="rule.country"
                                @update:modelValue="handleCountryChange"
                                mode="single"
                                label="Country / Region"
                                placeholder="Select country"
                                :options="filteredCountries"
                                :show-labels="true"
                                :close-on-select="true"
                                :canClear="false"
                                :searchable="true"
                                :error="form.errors['sender_country'] ?? null"
                            />
                        </div>
                        <div >
                            <CustomInput
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                                v-model="rule.min_order_weight"
                                name="min_order_weight"
                                label="Min. Order Weight"
                                type="text"
                                placeholder="Enter Order Weight"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['min_order_weight']"
                            />

                        </div>
                    </div>
                </div>
                <CustomButton
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_label_settings[0].can_edit"
                    title="Add new section"
                    @click="addNewSection"
                    type="button"
                    class="flex items-center gap-2 rounded bg-meta-3 py-2 px-3.5 font-medium text-white hover:bg-opacity-80 ml-auto"
                >
                    <font-awesome-icon :icon="'plus'" class="size-5"/>
                    Add rule
                </CustomButton>
            </div>
        </div>
        <div class="flex flex-col p-6.5 save-button-fixed">
            <div class="flex ml-auto gap-5">
                <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.currencies[0].can_edit">
                    <CustomButton
                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="submit"
                    >
                        <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                        Save
                    </CustomButton>
                </template>
            </div>
        </div>
    </form>
</template>

<style scoped>

</style>

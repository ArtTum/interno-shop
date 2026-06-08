<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";

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
                <CustomInput
                    v-model="form.patient_full_name"
                    name="patient_full_name"
                    label="Հիվանդի անուն ազգանուն *"
                    type="text"
                    placeholder="Մուտքագրեք հիվանդի անուն ազգանունը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['patient_full_name']"
                />
            </div>
            <div class="flex flex-col p-6.5">
                <CustomInput
                    v-model="form.phone"
                    name="phone"
                    label="Հեռախոս"
                    type="text"
                    placeholder="Մուտքագրեք հեռախոսահամարը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['phone']"
                />
            </div>
        </div>
        <div class="grid grid-cols-2 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomInput
                    v-model="form.other_phone"
                    name="other_phone"
                    label="Այլ հեռախոս"
                    type="text"
                    placeholder="Մուտքագրեք այլ հեռախոսահամարը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['other_phone']"
                />
            </div>
            <div class="flex flex-col p-6.5">
                <CustomInput
                    v-model="form.call_date"
                    name="call_date"
                    label="Զանգի ամսաթիվ"
                    type="text"
                    placeholder="Մուտքագրեք զանգի ամսաթիվը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['call_date']"
                />
            </div>
        </div>
        <div class="grid grid-cols-3 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomInput
                    v-model="form.year"
                    name="year"
                    label="Տարի"
                    type="number"
                    placeholder="Մուտքագրեք տարին"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['year']"
                />
            </div>
            <div class="flex flex-col p-6.5">
                <CustomInput
                    v-model="form.month"
                    name="month"
                    label="Ամիս"
                    type="number"
                    placeholder="Մուտքագրեք ամիսը (1-12)"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['month']"
                />
            </div>
            <div class="flex flex-col p-6.5">
                <CustomInput
                    v-model="form.sms_bazacol"
                    name="sms_bazacol"
                    label="SMS Բազա սյունակ"
                    type="text"
                    placeholder="Մուտքագրեք SMS բազա սյունակը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['sms_bazacol']"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomInput
                    v-model="form.disease"
                    name="disease"
                    label="Հիվանդություն"
                    type="text"
                    placeholder="Մուտքագրեք հիվանդությունը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['disease']"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomInput
                    v-model="form.medical_and_doctor"
                    name="medical_and_doctor"
                    label="Բժշկական և բժիշկ"
                    type="text"
                    placeholder="Մուտքագրեք բժշկական և բժիշկի տվյալները"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['medical_and_doctor']"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                        <CustomButton
                            v-if="form.id && (auth?.superadmin || auth?.user_group?.permissions_by_name.sms_bazas[0].can_delete)"
                            @click="store.commit('smsBaza/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.id
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Ջնջել
                        </CustomButton>
                        <CustomButton
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="submit"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Պահպանել
                        </CustomButton>
                </div>

            </div>
        </div>
    </form>
</template>

<style scoped>

</style>

<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const store = useStore()
const auth = computed(() => store.getters['auth/getUser']);
const props = defineProps({
    modelValue: {type: Object, required: true},
    emitAction: {type: String}
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);
const emits = defineEmits(['update:modelValue', 'submit']);

watch(modelValue, (newVal) => { form.value = newVal; });
watch(form.value, (newVal) => { emits('update:modelValue', newVal); });

const params = ref([]);
const fetchPageParams = async () => {
    params.value = await store.dispatch('hospitalsBase/fetchParams', {dontNeedLoading: true});
};
fetchPageParams();
</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div v-if="Object.keys(form.errors).length > 0 && form.errors.general" class="grid grid-cols-1 gap-9 p-6.5">
            <AlertError :errors="form.errors.general"/>
        </div>

        <div class="grid grid-cols-2 gap-4 p-4 items-stretch max-xl:grid-cols-3 max-md:grid-cols-2 max-md:gap-4 max-md:p-4 max-sm:grid-cols-1 max-sm:p-1">
            <div class="flex flex-col">
                <CustomSelect
                    v-model="form.year"
                    mode="single"
                    label="Տարի *"
                    placeholder="ընտրել տարի"
                    :options='params.years'
                    :canClear="false"
                    :searchable="true"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['year'] ?? null"
                />
            </div>
            <div class="flex flex-col">
                <CustomSelect
                    v-model="form.month"
                    mode="single"
                    label="Ամիս *"
                    placeholder="ընտրել ամիս"
                    :options='params.months'
                    :canClear="false"
                    :searchable="true"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['month'] ?? null"
                />
            </div>
            <div class="flex flex-col">
                <CustomDatePicker
                    v-model="form.call_date"
                    placeholder="dd/mm/yyyy"
                    label="Զանգի ամսաթիվ *"
                    format="Y-m-d"
                    altFormat="d.m.Y"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['call_date']"
                />
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 p-4 items-stretch max-xl:grid-cols-3 max-md:grid-cols-2 max-md:gap-4 max-md:p-4 max-sm:grid-cols-1 max-sm:p-1">
            <div class="col-span-8">
                <CustomInput
                    v-model="form.patient_full_name"
                    name="patient_full_name"
                    label="Հիվանդի անուն ազգանուն"
                    type="text"
                    placeholder="Մուտքագրեք անունը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['patient_full_name']"
                />
            </div>
            <div class="col-span-4">
                <CustomInput
                    v-model="form.age"
                    name="age"
                    label="Տարիք"
                    type="number"
                    placeholder="Մուտքագրեք տարիք"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['age']"
                />
            </div>
            <div class="col-span-6">
                <CustomInput
                    v-model="form.phone"
                    name="phone"
                    label="Հեռախոս *"
                    type="text"
                    placeholder="Մուտքագրեք"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['phone']"
                />
            </div>
            <div class="col-span-6">
                <CustomInput
                    v-model="form.other_phone"
                    name="other_phone"
                    label="Հեռախոս 2"
                    type="text"
                    placeholder="Մուտքագրեք"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['other_phone']"
                />
            </div>
            <div class="col-span-6">
                <div>
                    <label class="mb-2.5 block font-medium text-black">Հիվանդություն</label>
                    <textarea
                        v-model="form.disease"
                        rows="2"
                        placeholder="Հիվանդություն"
                        class="w-full rounded border-[1.5px] text-black border-stroke bg-transparent py-3 px-5 font-normal outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                    ></textarea>
                </div>
            </div>
            <div class="col-span-6">
                <CustomSelect
                    v-model="form.color"
                    mode="single"
                    label="Գույն"
                    placeholder="ընտրել"
                    :options='params.colors'
                    :canClear="true"
                    :searchable="true"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['color'] ?? null"
                />
            </div>
            <div class="col-span-12 w-full">
                <label class="mb-2.5 block font-medium text-black">Լրացուցիչ տվյալներ</label>
                <CKEditorComponent
                    :model="form.additional_data"
                    @updateValue="(value) => { form.additional_data = value }"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <CustomButton
                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="submit"
                    >
                        <font-awesome-icon :icon="['far', 'floppy-disk']" /> Պահպանել
                    </CustomButton>
                    <CustomButton
                        v-if="emitAction === 'update' && (auth?.superadmin || auth?.user_group?.permissions_by_name.hospitals_bases[0].can_delete)"
                        @click="store.commit('hospitalsBase/SET_DELETE_MODAL_VALUE', {value: true, id: form.id});"
                        class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']" /> Ջնջել
                    </CustomButton>
                </div>
            </div>
        </div>
    </form>
</template>

<style>
.invalid-feedback {
    display: none !important;
}
</style>

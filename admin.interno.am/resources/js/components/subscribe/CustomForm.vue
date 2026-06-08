<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";

const store = useStore();
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

const now = new Date();
const years = [];
for (let y = 2016; y <= now.getFullYear() + 1; y++) {
    years.push({value: y, label: y});
}

const months = [
    {value: 1,  label: 'Հունվար'},
    {value: 2,  label: 'Փետրվար'},
    {value: 3,  label: 'Մարտ'},
    {value: 4,  label: 'Ապրիլ'},
    {value: 5,  label: 'Մայիս'},
    {value: 6,  label: 'Հունիս'},
    {value: 7,  label: 'Հուլիս'},
    {value: 8,  label: 'Օգոստոս'},
    {value: 9,  label: 'Սեպտեմբեր'},
    {value: 10, label: 'Հոկտեմբեր'},
    {value: 11, label: 'Նոյեմբեր'},
    {value: 12, label: 'Դեկտեմբեր'},
];

const colors = [
    {value: '#000000', label: 'Սև'},
    {value: '#0070C0', label: 'Կապույտ'},
    {value: '#E26B0A', label: 'Դեղին'},
    {value: '#00B050', label: 'Կանաչ'},
    {value: '#FF0000', label: 'Կարմիր'},
    {value: '#7030A0', label: 'Մանուշկագույն'},
    {value: '#FFC000', label: 'Նարջագույն'},
    {value: '#6f2222', label: 'Շագանակագույն'},
];
</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div v-if="Object.keys(form.errors).length > 0 && form.errors.general" class="grid grid-cols-1 gap-9 p-6.5">
            <AlertError :errors="form.errors.general"/>
        </div>

        <div class="grid grid-cols-2 gap-4 p-4 max-md:grid-cols-2 max-md:gap-4 max-md:p-4 max-sm:grid-cols-1 max-sm:p-1">
            <div class="flex flex-col">
                <CustomSelect
                    v-model="form.year"
                    mode="single"
                    label="Տարի"
                    placeholder="Ընտրել տարի"
                    :options="years"
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
                    label="Ամիս"
                    placeholder="Ընտրել ամիս"
                    :options="months"
                    :canClear="false"
                    :searchable="true"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['month'] ?? null"
                />
            </div>
            <div class="flex flex-col">
                <CustomInput
                    v-model="form.phone"
                    name="phone"
                    label="Հեռախոս"
                    type="text"
                    placeholder="+374XXXXXXXX"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['phone']"
                />
            </div>
            <div class="flex flex-col">
                <CustomInput
                    v-model="form.full_name"
                    name="full_name"
                    label="Անուն Ազգանուն"
                    type="text"
                    placeholder="Մուտքագրեք անուն ազգանուն"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['full_name']"
                />
            </div>
            <div class="flex flex-col col-span-2 max-sm:col-span-1">
                <label class="mb-2.5 block font-medium text-black">Ինֆորմացիա</label>
                <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Ինֆորմացիա"
                    class="w-full rounded border-[1.5px] text-black border-stroke bg-transparent py-3 px-5 font-normal outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                ></textarea>
            </div>
            <div class="flex flex-col">
                <CustomInput
                    v-model="form.doctor"
                    name="doctor"
                    label="Բժիշկ"
                    type="text"
                    placeholder="Մուտքագրեք"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['doctor']"
                />
            </div>
            <div class="flex flex-col">
                <CustomInput
                    v-model="form.hospital"
                    name="hospital"
                    label="Բուժհաստատություն"
                    type="text"
                    placeholder="Մուտքագրեք"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['hospital']"
                />
            </div>
            <div class="flex flex-col">
                <CustomSelect
                    v-model="form.color"
                    mode="single"
                    label="Գույն"
                    placeholder="Ընտրել"
                    :options="colors"
                    :canClear="true"
                    :searchable="true"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['color'] ?? null"
                />
            </div>
            <div class="flex flex-col">
                <label class="mb-2.5 block font-medium text-black">Հաստատել</label>
                <div class="flex items-center gap-6 mt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" :value="0" v-model="form.status" class="w-4 h-4"/>
                        <span>Չհաստատված</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" :value="1" v-model="form.status" class="w-4 h-4"/>
                        <span>Հաստատված</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <CustomButton
                        v-if="emitAction === 'update' && (auth?.superadmin || auth?.user_group?.permissions_by_name.subscribes[0].can_delete)"
                        @click.prevent="store.commit('subscribe/SET_DELETE_MODAL_VALUE', {value: true, id: form.id});"
                        class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/> Ջնջել
                    </CustomButton>
                    <CustomButton
                        class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="submit"
                    >
                        <font-awesome-icon :icon="['far', 'floppy-disk']"/> Պահպանել
                    </CustomButton>
                </div>
            </div>
        </div>
    </form>
</template>

<style scoped></style>
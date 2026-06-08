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
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomInput
                    v-model="form.name"
                    name="name"
                    label="Անուն *"
                    type="text"
                    placeholder="Մուտքագրեք անունը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5">
                <label class="mb-3 block text-black">
                    SMS Տեքստ *
                </label>
                <textarea
                    v-model="form.sms_text"
                    name="sms_text"
                    rows="5"
                    placeholder="Մուտքագրեք SMS տեքստը"
                    @keyup="form.errors = validate(form)"
                    class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
                    :class="{'border-meta-1': form.errors['sms_text']}"
                ></textarea>
                <span v-if="form.errors['sms_text']" class="text-meta-1 text-sm">{{ form.errors['sms_text'] }}</span>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                        <CustomButton
                            v-if="form.id && (auth?.superadmin || auth?.user_group?.permissions_by_name.sms_shablons[0].can_delete)"
                            @click="store.commit('smsShablon/SET_DELETE_MODAL_VALUE', {
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

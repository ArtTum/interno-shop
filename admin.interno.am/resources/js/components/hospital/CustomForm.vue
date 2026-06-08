<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

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

const auth = computed(() => store.getters['auth/getUser']);

</script>

<template>
    <form @submit.prevent="emits('submit')">

        <!-- Ընդհանուր սխալներ -->
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>

        <div class="grid grid-cols-2 gap-9 p-6.5">

            <div class="flex flex-col">
                <CustomInput
                    v-model="form.name"
                    name="name"
                    label="Բուժհաստատություն *"
                    type="text"
                    placeholder="Մուտքագրեք"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>

            <div class="flex flex-col">
                <CustomInput
                    v-model="form.full_name"
                    name="full_name"
                    label="Լրիվ անվանում"
                    type="text"
                    placeholder="Մուտքագրեք"
                />
            </div>

            <div class="flex flex-col">
                <CustomInput
                    v-model="form.phone"
                    name="phone"
                    label="Հեռախոս"
                    type="text"
                    placeholder="Մուտքագրեք"
                />
            </div>

            <div class="flex flex-col">
                <CustomInput
                    v-model="form.email"
                    name="email"
                    label="Էլ․ փոստ"
                    type="text"
                    placeholder="Մուտքագրեք"
                />
            </div>

            <div class="flex flex-col">
                <CustomInput
                    v-model="form.address"
                    name="address"
                    label="Հասցե"
                    type="text"
                    placeholder="Մուտքագրեք"
                />
            </div>

            <div class="flex flex-col">
                <CustomInput
                    v-model="form.sale"
                    name="sale"
                    label="Զեղչ 1 (%)"
                    type="text"
                />
            </div>

            <div class="flex flex-col">
                <CustomInput
                    v-model="form.sale_2"
                    name="sale_2"
                    label="Զեղչ 2 (%)"
                    type="text"
                />
            </div>

        </div>

        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">

                    <!-- Ջնջել -->
                    <CustomButton
                        v-if="emitAction === 'update'"
                        @click.prevent="store.commit('hospital/SET_DELETE_MODAL_VALUE', {value: true, id: form.id});"
                        class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/> Ջնջել
                    </CustomButton>

                    <!-- Պահպանել -->
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

<style scoped>
</style>

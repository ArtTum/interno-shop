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
const newIP = ref(null);
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
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.users_groups[0].can_edit"
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
        </div>
        <template
            v-for="(ip, IPKey) in form.ips"
            :key="IPKey"
        >
            <div class="grid grid-cols-7" v-if="!ip.deleted">
                <div class="flex flex-col p-6.5 pb-0 col-span-2">
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.users_groups[0].can_edit"
                        v-model="ip.ip"
                        name="ip"
                        label="IP *"
                        type="text"
                        placeholder="Enter IP"
                    />
                </div>
                <div class="flex flex-col p-6.5 pb-0 col-span-2">
                    <div class="flex">
                        <div class="w-full">
                            <CustomDatePicker
                                :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.users_groups[0].can_edit"
                                placeholder="yyyy/mm/dd"
                                label="Expires at"
                                format="Y-m-d"
                                v-model="ip.expires_at"
                            />
                        </div>
                        <div class="max-w-[8.5rem] mx-auto ml-3">
                            <template v-if="ip.expires_at">
                                <label for="time" class="mb-2.5 block font-medium text-black">Select time:</label>
                                <div class="flex">
                                    <input
                                        type="time"
                                        id="time"
                                        class="py-4 h-full w-full rounded border-[1.5px] border-stroke bg-transparent px-5 font-normal outline-none transition focus:border-primary active:border-primary"
                                        min="00:00"
                                        max="23:59"
                                        v-model="ip.time"
                                    >
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex p-6.5 pb-0 items-center col-span-2">
                    <CustomButton
                        @click="ip.deleted = true"
                        class="h-[45px] max-w-[120px] flex items-center gap-2 ml-3 rounded bg-danger px-3.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        <font-awesome-icon :icon="['far', 'trash']"/>
                        Delete
                    </CustomButton>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.users_groups[0].can_delete">
                        <CustomButton
                            @click="store.commit('userGroup/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.id
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.users_groups[0].can_edit">
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
        </div>
    </form>
</template>

<style scoped>

</style>

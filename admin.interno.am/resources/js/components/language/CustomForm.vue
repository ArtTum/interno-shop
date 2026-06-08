<script setup>

import Switch from "@components/global/Switch.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, toRefs, watch} from "vue";

import {useStore} from "vuex";

const store = useStore()

import {validate} from "@validation/customValidation.js";

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    },
    emitOutlook: {
        type: String
    }
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'outlook',
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
        <div class="grid grid-cols-3 gap-6 py-6 px-4">
            <div class="px-4">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.languages[0].can_edit"
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
            <div class="px-4">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.languages[0].can_edit"
                    v-model="form.code"
                    name="code"
                    label="Code *"
                    type="text"
                    placeholder="Enter code"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['code']"
                />
            </div>
            <div class="px-4">
                <CustomSelect
                    label="Base currency"
                    v-model="form.currency_id"
                    mode="single"
                    placeholder="Select currency"
                    :disabled="emitAction === 'create'"
                    :options="form.currencies"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />

            </div>
            <div class="px-4">

                <CustomInput
                    :disabled="true"
                    v-model="form.email"
                    name="email"
                    label="Email *"
                    type="text"
                    placeholder="Enter email"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['email']"
                />
            </div>
            <div class="px-4">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.languages[0].can_edit"
                    v-model="form.hreflang"
                    name="hreflang"
                    label="Hreflang *"
                    type="text"
                    placeholder="Example: de or en_GB or en_US or fr and etc."
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['hreflang']"
                />

            </div>
            <div class="px-4">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.languages[0].can_edit"
                    v-model="form.local_for_trustpilot"
                    name="local_for_trustpilot"
                    label="Local for trustpilot *"
                    type="text"
                    placeholder="Example: en-GB or en-US or fr and etc."
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['local_for_trustpilot']"
                />
            </div>
            <div class="px-4 flex gap-10 col-span-3">
                <div v-if="form.code">
                    <img
                        alt="icon"
                        width="50px"
                        class="mt-3"
                        :src="`/flags/${form.code.toLowerCase()}.svg`"
                    >
                </div>
                <div class="flex gap-8">
                    <Switch
                        @change="(value) => {
                           form.status = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.languages[0].can_edit"
                        :value="form.status"
                        id="status"
                        label="Status"
                    />
                    <Switch
                        @change="(value) => {
                           form.base = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.languages[0].can_edit"
                        :value="form.base"
                        id="base"
                        label="Base"
                    />
                    <Switch
                        @change="(value) => {
                           form.default_hreflang = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.languages[0].can_edit"
                        :value="form.default_hreflang"
                        id="default_hreflang"
                        label="Default hreflang"
                    />
                    <Switch
                        @change="(value) => {
                           form.is_rtl = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.languages[0].can_edit"
                        :value="form.is_rtl"
                        id="is_rtl"
                        label="Is RTL Direction"
                    />
                </div>
            </div>
            <div class="p-6 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.languages[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="emits('outlook')"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-10 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <img src="@assets/images/outlook-icon.svg" alt="Logo" width="35px"/>
                            Connect outlook email
                        </CustomButton>
                    </template>
                    <template v-if="auth.user_group.permissions_by_name.languages[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('language/SET_DELETE_MODAL_VALUE', {
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
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.languages[0].can_edit">
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

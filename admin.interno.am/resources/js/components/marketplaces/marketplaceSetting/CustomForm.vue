<script setup>

import Switch from "@components/global/Switch.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";
import CustomTextarea from "@components/global/CustomTextarea.vue";

const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    },
    emitGenerateToken: {
        type: String
    }
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit',
    'generateToken'
]);

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

store.dispatch('marketplaceSetting/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['marketplaceSetting/getParams']);
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
        <div class="grid grid-cols-2 gap-0  p-6.5">
            <div class="mx-3">
                <Switch
                    @change="(value) => {
                                    form.is_sandbox = value;
                                }"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.coupons[0].can_edit"
                    :value="form.is_sandbox"
                    id="is_sandbox"
                    label="Is sandbox"
                    class="mb-5"
                />
            </div>
            <div class="mx-3">

            </div>
            <div class="mx-3">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.marketplace_settings[0].can_edit"
                    v-model="form.client_id"
                    name="client_id"
                    label="Client id *"
                    type="text"
                    placeholder="Enter client id"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['client_id']"
                />
            </div>
            <div class="mx-3">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.marketplace_settings[0].can_edit"
                    v-model="form.client_secret"
                    name="client_secret"
                    label="Client secret *"
                    type="text"
                    placeholder="Enter client secret"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['client_secret']"
                />
            </div>

            <div class="mx-3">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.marketplace_settings[0].can_edit"
                    v-model="form.dev_id"
                    name="dev_id"
                    :label="form.id == 2 ? 'Dev id *' : 'App id *'"
                    type="text"
                    :placeholder="form.id == 2 ? 'Enter dev id ' : 'Enter app id'"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['dev_id']"
                />
            </div>
            <div class="mx-3">
                <CustomSelect
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.marketplace_settings[0].can_edit"
                    label="Region *"
                    v-model="form.region"
                    mode="single"
                    placeholder="Select *"
                    :options="params.regions"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['region']"
                />
            </div>
            <div class="mx-3">
                <CustomInput
                    :disabled="true"
                    v-model="form.access_token_expires_at"
                    name="access_token_expires_at"
                    label="Access token expires at"
                    type="text"
                    placeholder="Enter client id"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['access_token_expires_at']"
                />
            </div>
            <div class="mx-3">
                <CustomInput
                    :disabled="true"
                    v-model="form.refresh_token_expires_at"
                    name="refresh_token_expires_at"
                    label="Refresh token expires at"
                    type="text"
                    placeholder="Enter refresh token expires at"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['refresh_token_expires_at']"
                />
            </div>
            <div class="mx-3">
                <CustomTextarea
                    :disabled="true"
                    v-model="form.access_token"
                    name="access_token"
                    label="Access token"
                    type="text"
                    placeholder="Enter access token"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['access_token']"
                />
            </div>
            <div class="mx-3">
                <CustomTextarea
                    :disabled="true"
                    v-model="form.refresh_token"
                    name="refresh_token"
                    label="Refresh token"
                    type="text"
                    placeholder="Enter access token"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['refresh_token']"
                />
            </div>

        </div>

        <div class="flex flex-col p-6.5 save-button-fixed">
            <div class="flex ml-auto gap-5">
                <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.marketplace_settings[0].can_edit">
                    <CustomButton
                        v-if="emitAction === 'update'"
                        @click="emits('generateToken')"
                        class="flex items-center gap-2 rounded border-meta-1 bg-meta-10 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                        type="button"
                    >
                        Generate Token
                    </CustomButton>
                </template>
                <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.marketplace_settings[0].can_edit">
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

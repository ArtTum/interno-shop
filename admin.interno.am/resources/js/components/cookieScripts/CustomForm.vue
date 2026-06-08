<script setup>
import {computed, ref, toRefs, watch,} from "vue";
import {useStore} from "vuex";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import {validate} from "@validation/customValidation.js";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import Switch from "@components/global/Switch.vue";

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
])

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

store.dispatch('cookieScripts/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['cookieScripts/getParams']);
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
        <div class="grid grid-cols-5 gap-9">
            <div class="flex flex-col p-6.5">
                <CustomSelect
                    label="Languages"
                    v-model="form.language_id"
                    mode="single"
                    placeholder="Select languages"
                    :disabled="emitAction === 'create'"
                    :options="params.languages"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['language_id']"
                />
            </div>
        </div>
        <hr class="text-gray">
        <template v-if="form.language_id == -1">
            <div class="grid grid-cols-5 gap-9 p-6.5">
                <div class="flex flex-col pb-0 col-span-2">
                    <CustomInput
                        v-model="form.key"
                        name="key"
                        label="Key *"
                        type="text"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.cookie_settings[0].can_edit"
                        placeholder="Enter key"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['key']"
                    />
                </div>
            </div>
        </template>
        <template v-else>
            <div class="grid grid-cols-2">
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            v-model="form.name"
                            name="name"
                            label="Name *"
                            type="text"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.cookie_settings[0].can_edit"
                            placeholder="Enter name"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['name']"
                        />
                    </div>
                </div>
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomTextarea
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.cookie_settings[0].can_edit"
                            v-model="form.description"
                            name="description"
                            label="Description"
                            type="text"
                        />
                    </div>
                </div>
                <div class="flex flex-col p-6.5">
                    <CustomTextarea
                        v-model="form.code"
                        name="code"
                        label="Code"
                        type="text"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.cookie_settings[0].can_edit"
                        placeholder="Enter code"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['code']"
                    />
                </div>
                <div class="flex flex-col gap-6 p-6.5">
                    <Switch
                        @change="(value) => {
                          form.granted_anyway = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.cookie_settings[0].can_edit"
                        :value="form.granted_anyway"
                        id="granted_anyway"
                        label="Granted anyway"
                    />
                    <Switch
                        @change="(value) => {
                          form.consent_mode_v2 = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.cookie_settings[0].can_edit"
                        :value="form.consent_mode_v2"
                        id="consent_mode_v2"
                        label="Consent mode v2"
                    />
                    <Switch
                        @change="(value) => {
                          form.required_cookie = value;
                        }"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.cookie_settings[0].can_edit"
                        :value="form.required_cookie"
                        id="required_cookie"
                        label="Required cookie"
                    />
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.cookie_settings[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update' && (form.language_id == -1 || form.translation_id)"
                            @click="store.commit('cookieScripts/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.language_id == -1 ? form.id : form.translation_id,
                                    deletingActionApi: form.language_id == -1 ? 'delete' : 'delete-translation',
                                    deletingText: form.language_id == -1 ? null : 'Deleting this translation will be permanent and cannot be undone. Once deleted, you will not be able to restore it.',
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.cookie_settings[0].can_edit">
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

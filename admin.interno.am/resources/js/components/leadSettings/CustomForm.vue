<script setup>
import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import validationRules from "@validation/customValidationRules.js";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const store = useStore();

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
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
const editorFields = ['validity_text', 'email_content'];
</script>

<template>
    <form @submit.prevent="emits('submit')" v-if="form.rows">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div>
            <div class="p-6.5">
                <template
                    v-for="(item, key) in form.rows"
                    :key="key"
                >
                    <template v-if="editorFields.includes(item.key)">
                        <div class="flex flex-col mb-6">
                            <label class="block font-medium text-black mb-2.5">{{ item.label }}</label>
                            <CKEditorComponent
                                :disabled="!auth.user_group.permissions_by_name.leads[0].can_edit"
                                :model="item.translation"
                                @updateValue="(value) => item.translation = value"
                            />
                        </div>
                    </template>
                    <template v-else>
                        <CustomInput
                            :disabled="!auth.user_group.permissions_by_name.leads[0].can_edit"
                            v-model="item.translation"
                            :name="item.key"
                            :label="`${item.label} *`"
                            type="text"
                            @keyup="form.errors = !validationRules.required.validate(item.translation) ? {[item.key]:validationRules.required.message} : {}"
                            :error="form.errors[item.key]"
                        />
                    </template>
                </template>
                <div>
                    <h3 class="text-black font-bold">Placeholders:</h3>
                    <ul>
                        <li>{customer_name}</li>
                        <li>{customer_email}</li>
                        <li>{customer_phone}</li>
                        <li>{customer_zip_code}</li>
                    </ul>
                </div>
            </div>
            <div class="p-6.5 save-button-fixed !right-3">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.leads[0].can_edit">
                        <CustomButton
                            @click="emits('cancel')"
                            class="block w-full rounded border border-stroke bg-gray p-3 text-center font-medium text-black hover:bg-opacity-60"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'arrow-rotate-left']"/>
                            Cancel
                        </CustomButton>
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

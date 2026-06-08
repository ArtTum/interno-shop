<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import {validate} from "@validation/customValidation.js";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

import {computed, ref, toRefs, watch} from "vue";

import {useStore} from "vuex";

const store = useStore()

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    },
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

store.dispatch('postCategory/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['postCategory/getParams']);
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
            <div class="grid grid-cols-2 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.posts[0].can_edit"
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
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.posts[0].can_edit"
                            v-model="form.slug"
                            name="slug"
                            label="Slug"
                            type="text"
                            placeholder="Enter or will generate automatically"
                            :error="form.errors['slug']"
                        />
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <label class="mb-2.5 mt-2.5 block font-medium text-black">Description</label>
                    <CKEditorComponent
                        :model="form.description"
                        @updateValue="(value) => {
                           form.description = value
                        }"
                    />
                </div>
            </div>
        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.posts[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update' && form.translation_id"
                            @click="store.commit('postCategory/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.translation_id,
                                    deletingActionApi: 'delete-translation',
                                    deletingText: 'Deleting this translation will be permanent and cannot be undone. Once deleted, you will not be able to restore it.',
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.posts[0].can_edit">
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

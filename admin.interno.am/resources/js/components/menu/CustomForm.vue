<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import Switch from "@components/global/Switch.vue";

import {computed, ref, toRefs, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import {useStore} from "vuex";
import {debounce} from "lodash";
import {useRoute, useRouter} from "vue-router";

const store = useStore()
const router = useRouter()
const route = useRoute()

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

const fetchParams = () => {
    store.dispatch('menu/fetchParams', {
        id: form.value.id,
        language_id: form.value.language_id,
        type: form.value.type
    });
}
fetchParams();

const params = computed(() => store.getters['menu/getParams']);

const productsOptions = ref([]);

const productsAutocomplete = debounce(async (term) => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        forOrder: false,
        language_id: form.value.language_id
    });
}, 300);

const autocompleteRequest = async (alreadySelectedId, term = '') => {
    productsOptions.value = await store.dispatch('product/autocomplete', {
        field: 'name',
        term,
        alreadySelectIds: [alreadySelectedId],
        forOrder: false,
        language_id: form.value.language_id
    });
}

if (props.emitAction === 'update') {
    autocompleteRequest(form.value.product_translation_id);
}

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
        <div class="grid grid-cols-4 gap-9">
            <div class="flex flex-col p-6.5 pb-0 col-span-1">
                <CustomSelect
                    label="Parent"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit"
                    v-model="form.parent_id"
                    mode="single"
                    :canClear="true"
                    :excluded-value="form.id"
                    placeholder="Select"
                    :options="params.structuredMenus"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :searchable="true"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0 col-span-1">
                <div>
                    <CustomInput
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit"
                        v-model="form.name"
                        name="name"
                        label="Name"
                        type="text"
                        placeholder="Enter name"
                    />
                </div>
            </div>
            <div class="flex flex-col p-6.5 pb-0 col-span-1">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit || (!!form.category_translation_id || !!form.product_translation_id || !!form.page_translation_id)"
                    v-model="form.url"
                    name="url"
                    label="URL"
                    type="text"
                    placeholder="Custom URL"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['url']"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0 col-span-1">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit"
                    v-model="form.text_for_all"
                    name="text_for_all"
                    label="Text for all"
                    type="text"
                    placeholder="Text for all"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['text_for_all']"
                />
            </div>
        </div>
        <div class="grid grid-cols-4 gap-9">
            <div class="flex flex-col p-6.5 pb-0">
                <CustomSelect
                    :disabled="!!form.product_translation_id || !!form.page_translation_id || (emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit)"
                    label="Related category"
                    v-model="form.category_translation_id"
                    mode="single"
                    :canClear="true"
                    placeholder="Select"
                    :searchable="true"
                    :options="params.structuredCategories"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <CustomSelect
                    @search-change="productsAutocomplete"
                    label="Related product"
                    :disabled="!!form.category_translation_id || !!form.page_translation_id || (emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit)"
                    v-model="form.product_translation_id"
                    mode="single"
                    :searchable="true"
                    :canClear="true"
                    :need-autocomplete="true"
                    placeholder="Select"
                    :options="productsOptions"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <CustomSelect
                    :disabled="!!form.product_translation_id || !!form.category_translation_id || (emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit)"
                    label="Related page"
                    v-model="form.page_translation_id"
                    mode="single"
                    :canClear="true"
                    :searchable="true"
                    placeholder="Select"
                    :options="params.pages"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <CustomSelect
                    :disabled="!!form.product_translation_id || !!form.category_translation_id || (emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit)"
                    label="Related post"
                    v-model="form.page_translation_id"
                    mode="single"
                    :canClear="true"
                    :searchable="true"
                    placeholder="Select"
                    :options="params.posts"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                />
            </div>
        </div>
        <div class="grid grid-cols-3 gap-9">
            <div class="flex flex-col p-6.5 pb-0">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit"
                    v-model="form.new_tab"
                    label="Open on new tab"
                    name="new_tab"
                    type="checkbox"
                    class="mt-auto mb-auto"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <Switch
                    @change="(value) => {
                            form.status = value;
                        }"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit"
                    :value="form.status"
                    id="status"
                    label="Status"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <Switch
                    @change="(value) => {
                            form.is_private = value;
                        }"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.menus[0].can_edit"
                    :value="form.is_private"
                    id="is_private"
                    label="Is private"
                />
            </div>
        </div>
        <hr class="text-gray mt-6.5">
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.menus[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('menu/SET_DELETE_MODAL_VALUE', {
                                  value: true,
                                    id: form.id,
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

                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.menus[0].can_edit">
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

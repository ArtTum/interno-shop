<script setup>

import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

import {validate} from "@validation/customValidation.js";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import CustomSelect from "@components/global/CustomSelect.vue";
import {debounce} from "lodash";

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

store.dispatch('affiliateProduct/fetchParams', {id: form.value.id});
const params = computed(() => store.getters['affiliateProduct/getParams']);
const auth = computed(() => store.getters['auth/getUser']);

const usersOptions = ref([]);
const usersAutocomplete = debounce(async (term) => {
    usersOptions.value = await store.dispatch('member/autocomplete', {
        field: 'offer_label',
        type: 'offer',
        for: 'member',
        term,
        alreadySelectIds: [form.value.user_id],
    });
}, 200);

const usersAutocompleteRequest = async (alreadySelectedId, term = '') => {
    usersOptions.value = await store.dispatch('member/autocomplete', {
        field: 'label',
        type: 'offer',
        for: 'member',
        term,
        alreadySelectIds: [alreadySelectedId],
    });
}

if (props.emitAction === 'update') {
    usersAutocompleteRequest(form.value.user_id);
} else {
    usersAutocompleteRequest([]);
}
</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>

        <div class="grid grid-cols-3 gap-9 pb-5">
            <div class="flex flex-col p-6.5 pb-0">
                <CustomSelect
                    label="Product *"
                    v-model="form.product_id"
                    mode="single"
                    placeholder="Select product"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.affiliate_products[0].can_edit"
                    :options="params.products"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['product_id']"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <CustomSelect
                    label="Program *"
                    v-model="form.affiliate_program_id"
                    mode="single"
                    placeholder="Select product"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.affiliate_products[0].can_edit"
                    :options="params.programs"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['affiliate_program_id']"
                />
            </div>
            <div class="flex flex-col p-6.5 pb-0">
                <CustomSelect
                    @search-change="usersAutocomplete"
                    label="Member *"
                    v-model="form.user_id"
                    mode="single"
                    :need-autocomplete="true"
                    placeholder="Select member"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.affiliate_products[0].can_edit"
                    :options="usersOptions"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['user_id']"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.affiliate_products[0].can_delete">
                        <CustomButton
                            @click="store.commit('affiliateProduct/SET_DELETE_MODAL_VALUE', {
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
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.affiliate_products[0].can_edit">
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

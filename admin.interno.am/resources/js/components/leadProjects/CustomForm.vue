<script setup>
import {computed, ref, toRefs, watch,} from "vue";
import {useStore} from "vuex";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import {validate} from "@validation/customValidation.js";
import CustomRadio from "@components/global/CustomRadio.vue";

const store = useStore();

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

const params = computed(() => store.getters['leadProject/getParams']);
const auth = computed(() => store.getters['auth/getUser']);

const newField = ref(null);

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
            <div class="grid grid-cols-5 gap-9 p-6.5 pb-0">
                <div class="flex flex-col pb-0 col-span-2">
                    <CustomInput
                        v-model="form.product_price"
                        name="product_price"
                        label="Product price *"
                        type="text"
                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                        placeholder="Enter price"
                        @keyup="form.errors = validate(form)"
                        :error="form.errors['product_price']"
                    />
                </div>
            </div>
            <div class="flex p-6.5 pt-0">
                <h3 class="text-black-2 font-bold text-2xl">Service prices</h3>
            </div>
            <template v-for="(field, fieldKey) in form.service_prices" :key="fieldKey">
                <div class="grid grid-cols-9 mt-5">
                    <div class="flex flex-col p-6.5 pb-0 col-span-2">
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            v-model="field.min_sqm"
                            name="min_sqm"
                            label="Min m²"
                            type="text"
                            :tooltip="true"
                        />
                    </div>
                    <div class="flex flex-col p-6.5 pb-0 col-span-2">
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            v-model="field.max_sqm"
                            name="max_sqm"
                            label="Max m²"
                            type="text"
                            :tooltip="true"
                        />
                    </div>
                    <div class="flex flex-col p-6.5 pb-0 col-span-2">
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            v-model="field.price"
                            name="price"
                            label="Price"
                            type="text"
                            :tooltip="true"
                        />
                    </div>
                    <div class="flex flex-col p-6.5 pb-0 col-span-2 gap-3">
                        <div class="block font-medium text-black">Price type</div>
                        <CustomRadio
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            v-model="field.price_type"
                            name="price_type"
                            label="Price type"
                            :options="[
                            {value: 'fixed', label: 'Fixed'},
                            {value: 'dynamic', label: 'Dynamic'}
                        ]"
                        />
                    </div>
                    <div class="flex p-6.5 pb-0 items-center justify-end col-span-1">
                        <CustomButton
                            @click="form.service_prices.splice(fieldKey, 1);"
                            class="h-[45px] max-w-[120px] flex items-center gap-2 ml-3 rounded bg-danger px-3.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </div>
                </div>
            </template>
            <template v-if="newField">
                <div class="grid grid-cols-9">
                    <div class="flex flex-col p-6.5 pb-0 col-span-2">
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            v-model="newField.min_sqm"
                            name="min_sqm"
                            label="Min m²"
                            type="text"
                            :tooltip="true"
                        />
                    </div>
                    <div class="flex flex-col p-6.5 pb-0 col-span-2">
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            v-model="newField.max_sqm"
                            name="max_sqm"
                            label="Max m²"
                            type="text"
                            :tooltip="true"
                        />
                    </div>
                    <div class="flex flex-col p-6.5 pb-0 col-span-2">
                        <CustomInput
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            v-model="newField.price"
                            name="price"
                            label="Price"
                            type="text"
                            :tooltip="true"
                        />
                    </div>
                    <div class="flex flex-col p-6.5 pb-0 col-span-2 gap-3">
                        <div class="block font-medium text-black">Price type</div>
                        <CustomRadio
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            v-model="newField.price_type"
                            name="price_type"
                            label="Price type"
                            :options="[
                            {value: 'fixed', label: 'Fixed'},
                            {value: 'dynamic', label: 'Dynamic'}
                        ]"
                        />
                    </div>
                </div>
            </template>
            <div class="grid grid-cols-1">
                <template v-if="!newField">
                    <div class="flex p-6.5">
                        <CustomButton
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            @click="newField = {min_sqm: null, max_sqm: null, price: null, price_type: null}"
                            title="Add new field"
                            type="button"
                            class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80"
                        >
                            <font-awesome-icon :icon="'plus'" class="size-5"/>
                            New field
                        </CustomButton>
                    </div>
                </template>
                <template v-else>
                    <div class="flex p-6.5">
                        <CustomButton
                            @click="newField = null"
                            class="h-[45px] max-w-[120px] block w-full rounded border border-stroke bg-gray px-3.5 font-medium text-black hover:bg-opacity-60"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'arrow-rotate-left']"/>
                            Cancel
                        </CustomButton>
                        <template v-if="newField.price && (newField.min_sqm || newField.max_sqm)">
                            <CustomButton
                                @click="() => {
                                form.service_prices.push(newField);
                                newField = null;
                            }"
                                class="h-[45px] max-w-[120px] flex items-center gap-2 ml-3 rounded bg-primary px-3.5 font-medium text-white hover:bg-opacity-80"
                                type="button"
                            >
                                <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                Save
                            </CustomButton>
                        </template>
                        <template v-else>
                            <CustomButton
                                disabled
                                class="h-[45px] max-w-[120px] flex items-center gap-2 ml-3 rounded bg-primary px-3.5 font-medium text-white hover:bg-opacity-80"
                                type="button"
                            >
                                <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                Save
                            </CustomButton>
                        </template>
                    </div>
                </template>
            </div>
        </template>
        <template v-else>
            <div class="grid grid-cols-2 gap-9">
                <div class="flex flex-col p-6.5 pb-0">
                    <div>
                        <CustomInput
                            v-model="form.name"
                            name="name"
                            label="Name *"
                            type="text"
                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.leads[0].can_edit"
                            placeholder="Enter name"
                            @keyup="form.errors = validate(form)"
                            :error="form.errors['name']"
                        />
                    </div>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.leads[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update' && (form.language_id == -1 || form.translation_id)"
                            @click="store.commit('leadProject/SET_DELETE_MODAL_VALUE', {
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
                    <template v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.leads[0].can_edit">
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

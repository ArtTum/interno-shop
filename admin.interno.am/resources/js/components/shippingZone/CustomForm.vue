<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import AccordionTwo from "@components/accordion/AccordionTwo.vue";
import FreeShipping from "@components/shippingZone/FreeShipping.vue";
import FlatRate from "@components/shippingZone/FlatRate.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, toRefs, watch} from "vue";

import {validate} from "@validation/customValidation.js";

import {useStore} from "vuex";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const store = useStore()

const props = defineProps({
    emitAction: {
        type: String
    },
    modelValue: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        required: false
    },
    shippingMethodsErrorsIndexes: {
        type: Array,
        required: false
    }
});

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});


const fetchParams = async () => {
    let res = await store.dispatch(`shippingZone/fetchParams`, {
        id: form.value.id,
        language_id: form.value.language_id
    });

    if (form.value.language_id < 0) {
        form.value.language_id = res.base_language_id
    }
}

fetchParams();

const params = computed(() => store.getters['shippingZone/getParams']);

const emits = defineEmits([
    'update:modelValue',
    'add-new-shipping-method',
    'empty-dynamic-errors',
    'submit'
]);

const auth = computed(() => store.getters['auth/getUser']);
const generalParams = computed(() => store.getters['general/getParams']);

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
                    :disabled="emitAction !== 'update'"
                    :options="params.languages"
                    :searchable="true"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['language_id']"
                />
            </div>
        </div>
        <hr class="text-gray">

        <div class="grid grid-cols-3 gap-9 pl-6.5 pr-6.5 pt-6.5">
            <div class="flex flex-col">
                <CustomInput
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                    @keyup="form.errors = validate(form)"
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    placeholder="Enter name *"
                    :error="form.errors['name'] ?? null"
                />
            </div>
            <div class="flex flex-col">
                <CustomSelect
                    class="py-2 rounded-lg  border-stroke bg-transparent"
                    v-model="form.country_ids"
                    :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                    @update:modelValue="form.errors = validate(form)"
                    mode="tags"
                    label="Regions *"
                    placeholder="Select regions *"
                    :options="params.countries"
                    :show-labels="true"
                    :close-on-select="false"
                    :canClear="false"
                    :error="form.errors['country_ids'] ?? null"
                />
            </div>
            <div class="flex flex-col mt-auto mb-auto">
                <template
                    v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.shipping_zones[0].can_edit">
                    <button
                        type="button"
                        @click="emits('add-new-shipping-method')"
                        class="p-6.5 flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 w-fit ml-auto"
                    >
                        <font-awesome-icon :icon="'plus'"/>
                        Add new shipping method
                    </button>
                </template>
            </div>
        </div>
        <div class="grid grid-cols-1">
            <template
                :key="index"
                v-for="(shippingMethod, index) in form.shippingMethods"
            >
                <template v-if="!shippingMethod.deleted">
                    <AccordionTwo
                        :parent="shippingMethod"
                        :invalid="shippingMethodsErrorsIndexes.includes(index)"
                    >
                        <template #header>
                            <h4
                                class="text-left text-title-xsm font-bold text-black sm:text-title-md"
                            >
                                {{ shippingMethod.name }} | {{ params.carrier_methods[shippingMethod.carrier] }} | {{
                                    shippingMethod.type === 'Free' ? shippingMethod.type : shippingMethod.flat_rates.cost
                                }}
                            </h4>
                        </template>
                        <template #content>
                            <div class="grid grid-cols-9 gap-9">
                                <div class="flex flex-col col-span-1">
                                    <CustomInput
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                                        v-model="shippingMethod.default"
                                        label="Is default"
                                        :name="`is_default_${index}`"
                                        type="checkbox"
                                        class="mt-auto mb-auto"
                                    />
                                </div>
                                <div class="flex flex-col col-span-2">
                                    <div>
                                        <CustomInput
                                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                                            v-model="shippingMethod.name"
                                            @update:modelValue="emits('empty-dynamic-errors')"
                                            name="name"
                                            label="Name"
                                            type="text"
                                            placeholder="Enter Name"
                                            :error="errors[index].name ?? null"
                                        />
                                    </div>
                                </div>
                                <div class="flex flex-col col-span-2">
                                    <div>
                                        <CustomSelect
                                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                                            label="Carrier"
                                            placeholder="Choose carrier"
                                            v-model="shippingMethod.carrier"
                                            @update:modelValue="emits('empty-dynamic-errors')"
                                            mode="single"
                                            :options="params.carrier_methods"
                                            :searchable="false"
                                            :canClear="false"
                                            :error="errors[index].carrier ?? null"
                                            class="py-2 rounded-lg border-stroke bg-transparent"
                                        />
                                    </div>
                                </div>
                                <div class="flex flex-col col-span-2">
                                    <CustomSelect
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                                        label="Type"
                                        placeholder="Choose type"
                                        v-model="shippingMethod.type"
                                        @update:modelValue="emits('empty-dynamic-errors')"
                                        mode="single"
                                        :options="params.shipping_method_types"
                                        :searchable="false"
                                        :canClear="false"
                                        class="py-2 rounded-lg border-stroke bg-transparent"
                                    />
                                </div>
                                <div class="flex flex-col col-span-2" v-if="generalParams?.vendor?.b2b">
                                    <CustomSelect
                                        class="py-2 rounded-lg  border-stroke bg-transparent"
                                        v-model="shippingMethod.customer_group_ids"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                                        mode="tags"
                                        label="B2B Customer groups"
                                        placeholder="Select groups"
                                        :options="params.customerGroups"
                                        :show-labels="true"
                                        :close-on-select="false"
                                    />
                                </div>

                            </div>
                            <div class="grid grid-cols-9 gap-9">
                                <div class="flex flex-col col-span-2">
                                    <CustomSelect
                                        class="py-2 rounded-lg  border-stroke bg-transparent"
                                        v-model="shippingMethod.user_level_ids"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                                        mode="tags"
                                        label="Show for user levels"
                                        placeholder="Select user levels"
                                        :options="params.user_levels"
                                        :show-labels="true"
                                        :close-on-select="false"
                                    />
                                </div>
                                <div class="flex flex-col col-span-2">
                                    <CustomSelect
                                        class="py-2 rounded-lg  border-stroke bg-transparent"
                                        v-model="shippingMethod.hide_user_level_ids"
                                        :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                                        mode="tags"
                                        label="Hide for user levels"
                                        placeholder="Select user levels"
                                        :options="params.user_levels"
                                        :show-labels="true"
                                        :close-on-select="false"
                                    />
                                </div>
                            </div>
                            <template v-if="shippingMethod.type === 'Free'">
                                <FreeShipping
                                    :errors="errors[index]"
                                    :shipping-method="shippingMethod"
                                    :params="params"
                                    @emptyDynamicErrors="emits('empty-dynamic-errors')"
                                    :is-update="emitAction === 'update'"
                                />
                            </template>
                            <template v-if="shippingMethod.type === 'Flat rate'">
                                <FlatRate
                                    :errors="errors[index]"
                                    :shipping-method="shippingMethod"
                                    :params="params"
                                    @emptyDynamicErrors="emits('empty-dynamic-errors')"
                                    :is-update="emitAction === 'update'"
                                />
                            </template>
                            <div class="grid grid-cols-1 gap-9">
                                <template
                                    v-if="(shippingMethod.free_shipping.requirement !== 'None' && shippingMethod.free_shipping.requirement !== 'A valid coupon')
                                      || shippingMethod.type != 'Free'"
                                >
                                    <div class="flex flex-col">
                                        <label class="block font-medium text-black mb-5">Description</label>
                                        <CKEditorComponent
                                            :disabled="emitAction === 'update' && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                                            :model="shippingMethod.description"
                                            @updateValue="(value) => {
                                               shippingMethod.description = value
                                            }"
                                        />
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-col mt-4">
                                <div class="flex ml-auto gap-5">
                                    <template v-if="auth.user_group.permissions_by_name.shipping_zones[0].can_delete">
                                        <CustomButton
                                            @click="shippingMethod.deleted = true, emits('empty-dynamic-errors')"
                                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                            type="button"
                                        >
                                            <font-awesome-icon :icon="['far', 'trash']"/>
                                            Delete
                                        </CustomButton>
                                    </template>

                                    <template
                                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.shipping_zones[0].can_edit">
                                        <CustomButton
                                            @click="shippingMethod.open = false, store.commit('notification/SET_NOTIFICATION', {
                                            visible: true,
                                            title: 'Success',
                                            message: 'Successfully prepared'
                                        })"
                                            class="flex ml-auto mt-auto items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                                            type="button"
                                        >
                                            <font-awesome-icon :icon="['fas', 'check']"/>
                                            Done
                                        </CustomButton>
                                    </template>
                                </div>

                            </div>
                        </template>
                    </AccordionTwo>
                </template>
            </template>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.shipping_zones[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('shippingZone/SET_DELETE_MODAL_VALUE', {
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
                        v-if="emitAction !== 'update' || auth.user_group.permissions_by_name.shipping_zones[0].can_edit">
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

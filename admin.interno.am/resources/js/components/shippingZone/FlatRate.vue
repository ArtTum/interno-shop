<script setup>
import CustomInput from "@components/global/CustomInput.vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import {computed} from "vue";

import {useStore} from "vuex";
const store = useStore();

const props = defineProps({
    shippingMethod: Object,
    params: {
        type: Object,
        required: false
    },
    errors: {
        type: Object,
        required: false
    },
    isUpdate: {
        type: Boolean,
        default: false,
    }
});

const emits = defineEmits([
    'empty-dynamic-errors'
]);

const auth = computed(() => store.getters['auth/getUser']);
</script>

<template>
    <div class="grid grid-cols-3 gap-9">
        <div class="flex flex-col">
            <div>
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                    label="Tax status"
                    placeholder="Choose requirement"
                    v-model="shippingMethod.flat_rates.taxable"
                    @update:modelValue="emits('empty-dynamic-errors')"
                    mode="single"
                    :options="[{label: 'Taxable', value: true}, {label: 'None', value: false}]"
                    :searchable="false"
                    :canClear="false"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
        </div>
        <div class="flex flex-col">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                v-model="shippingMethod.flat_rates.cost"
                @update:modelValue="emits('empty-dynamic-errors')"
                name="cost"
                label="Cost"
                type="text"
                placeholder="Cost"
                :error="errors.flat_rates && errors.flat_rates.cost ? errors.flat_rates.cost : null"
            />
        </div>
        <div class="flex flex-col">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                v-model="shippingMethod.flat_rates.hide_if_more"
                @update:modelValue="emits('empty-dynamic-errors')"
                name="hide_if_more"
                label="Hide if totals grater than"
                type="text"
                placeholder="Hide if totals grater than"
                :error="errors.flat_rates && errors.flat_rates.hide_if_more ? errors.flat_rates.hide_if_more : null"
            />
        </div>
    </div>
    <div class="grid grid-cols-3 gap-9">
        <div class="flex flex-col">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                v-model="shippingMethod.flat_rates.hide_if_less"
                @update:modelValue="emits('empty-dynamic-errors')"
                name="hide_if_more"
                label="Hide if totals less than"
                type="text"
                placeholder="Hide if totals less than"
                :error="errors.flat_rates && errors.flat_rates.hide_if_less ? errors.flat_rates.hide_if_less : null"
            />
        </div>
        <div class="flex flex-col">
            <CustomInput
                :disabled="isUpdate && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                v-model="shippingMethod.flat_rates.some_day_delivery"
                @update:modelValue="emits('empty-dynamic-errors')"
                label="Same day delivery"
                name="some_day_delivery"
                type="checkbox"
                class="mt-auto mb-auto"
            />
        </div>
    </div>
</template>

<style scoped>

</style>

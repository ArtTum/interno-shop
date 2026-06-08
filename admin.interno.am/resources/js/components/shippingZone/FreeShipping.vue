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
        <div class="flex flex-col ">
            <div>
                <CustomSelect
                    :disabled="isUpdate && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                    label="Free shipping requires"
                    placeholder="Choose requirement"
                    v-model="shippingMethod.free_shipping.requirement"
                    @update:modelValue="emits('empty-dynamic-errors')"
                    mode="single"
                    :options="params.free_shipping_requirements"
                    :searchable="false"
                    :canClear="false"
                    class="py-2 rounded-lg border-stroke bg-transparent"
                />
            </div>
        </div>
        <template
            v-if="shippingMethod.free_shipping.requirement !== 'None' && shippingMethod.free_shipping.requirement !== 'A valid coupon'"
        >
            <div class="flex flex-col">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                    v-model="shippingMethod.free_shipping.min_order_amount"
                    @update:modelValue="emits('empty-dynamic-errors')"
                    name="min_order_amount"
                    label="Min order amount"
                    type="text"
                    placeholder="Min order amount"
                    :error="errors.free_shipping && errors.min_order_amount.cost ? errors.free_shipping.min_order_amount : null"
                />
            </div>
            <div class="flex flex-col">
                <CustomInput
                    :disabled="isUpdate && !auth.user_group.permissions_by_name.shipping_zones[0].can_edit"
                    v-model="shippingMethod.free_shipping.min_order_before_coupon"
                    @update:modelValue="emits('empty-dynamic-errors')"
                    label="Apply minimum order rule before coupon discount"
                    name="min_order_before_coupon"
                    type="checkbox"
                    class="mt-auto mb-auto"
                    :error="errors.free_shipping && errors.min_order_before_coupon.cost ? errors.free_shipping.min_order_before_coupon : null"
                />
            </div>
        </template>
    </div>
</template>

<style scoped>

</style>

<script setup>
import {computed, ref, watch} from "vue";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomTableSecond from "@components/global/CustomTableSecond.vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomInput from "@components/global/CustomInput.vue";

import {useStore} from "vuex";

const store = useStore()

const props = defineProps({
    variantId: {
        type: Number,
    },
});

const tieredPrices = ref([]);

const fetchTieredPrices = async () => {
    tieredPrices.value = [];

    const response = await store.dispatch('product/fetchVariantTieredPrices', {
        variant_id: props.variantId
    })

    if (response.data.data) {
        tieredPrices.value = response.data.data;
    }

    console.log(response);
}

watch(() => props.variantId, (newParams) => {
    fetchTieredPrices();
}, {immediate: true});


const newTieredPrice = ref(null);

const finishNewTieredPrice = () => {
    console.log(tieredPrices.value);
    tieredPrices.value.unshift(newTieredPrice.value);
    newTieredPrice.value = null;

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully added and ready for save'
    });
}

const params = computed(() => store.getters['product/getParams']);

const saveTieredPrices = async () => {
    const res = await store.dispatch('product/updateVariantTieredPrices', {
        product_variant_id: props.variantId,
        tiered_prices: tieredPrices.value
    })

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Successfully updated'
    });
    console.log(res);
}

</script>

<template>
    <div>
        <CustomButton
            type="button"
            :disabled="!!newTieredPrice"
            @click="() => {
             newTieredPrice = {
                 customer_group_id: null,
                 min: 0,
                 price: 0,
             }
         }"
            class="flex items-center gap-2 rounded bg-meta-3 py-2 px-4.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
        >
            <font-awesome-icon :icon="'plus'"/>
            Add tiered price
        </CustomButton>

        <CustomTableSecond
            title=""
            class="relative mt-4"
            @save="saveTieredPrices"
            :button-info="{
            title: 'Save',
            emitName: 'save',
            icon: 'floppy-disk',
            classes: 'bg-primary',
        }"
        >
            <template #header>
            </template>

            <template #content>
                <div
                    class="grid grid-cols-7 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5 sticky-header text-black"
                >
                    <div class="col-span-2 flex items-center">
                        <p class="font-bold">Customer group</p>
                    </div>
                    <div class="col-span-2 items-center sm:flex">
                        <p class="font-bold">Min quantity</p>
                    </div>
                    <div class="col-span-2 flex items-center">
                        <p class="font-bold">Price</p>
                    </div>
                    <div class="col-span-1 flex items-center">
                        <p class="font-bold">Actions</p>
                    </div>
                </div>
                <div
                    v-if="newTieredPrice"
                    class="grid grid-cols-7 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5 sticky-header text-black"
                >
                    <div class="col-span-2 items-center pr-3">
                        <CustomSelect
                            label=""
                            v-model="newTieredPrice.customer_group_id"
                            mode="single"
                            placeholder="Select B2B customer group"
                            :options="params.customerGroups"
                            :searchable="true"
                            class="py-2  rounded-lg border-stroke bg-transparent"
                        />
                    </div>
                    <div class="col-span-2 items-center pr-3">
                        <CustomInput
                            v-model="newTieredPrice.min"
                            name="min"
                            label=""
                            type="number"
                            placeholder="Enter min quantity"
                        />
                    </div>
                    <div class="col-span-2 items-center pr-3">
                        <CustomInput
                            v-model="newTieredPrice.price"
                            name="price"
                            label=""
                            type="text"
                            placeholder="Enter price"
                        />
                    </div>
                    <div class="col-span-1 items-center">
                        <div class="flex">
                            <div>
                                <CustomButton
                                    class="flex ml-auto mr-3 items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                    type="button"
                                    :disabled="(!newTieredPrice || !newTieredPrice.customer_group_id || !newTieredPrice.price)"
                                    @click="finishNewTieredPrice()"
                                >
                                    Add
                                </CustomButton>
                            </div>
                            <div>
                                <CustomButton
                                    class="flex ml-auto mr-3 items-center gap-2 rounded bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                    type="button"
                                    @click="newTieredPrice = null"
                                >
                                    Delete
                                </CustomButton>
                            </div>
                        </div>
                    </div>
                </div>

                <template v-if="tieredPrices.length">
                    <template
                        v-for="(tieredPrice, key) in tieredPrices"
                        :key="key"
                    >
                        <div
                            v-if="!tieredPrice.deleted"
                            class="grid grid-cols-7 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5 sticky-header text-black"
                        >
                            <div class="col-span-2 items-center pr-3">
                                <CustomSelect
                                    label=""
                                    v-model="tieredPrice.customer_group_id"
                                    mode="single"
                                    placeholder="Select B2B customer group"
                                    :options="params.customerGroups"
                                    :searchable="true"
                                    class="py-2  rounded-lg border-stroke bg-transparent"
                                />
                            </div>
                            <div class="col-span-2 items-center pr-3">
                                <CustomInput
                                    v-model="tieredPrice.min"
                                    name="min"
                                    label=""
                                    type="number"
                                    placeholder="Enter min quantity"
                                />
                            </div>
                            <div class="col-span-2 items-center pr-3">
                                <CustomInput
                                    v-model="tieredPrice.price"
                                    name="price"
                                    label=""
                                    type="text"
                                    placeholder="Enter price"
                                />
                            </div>
                            <div class="col-span-1 items-center">
                                <div class="flex">
                                    <div>
                                        <CustomButton
                                            class="flex ml-auto mr-3 items-center gap-2 rounded bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80 h-[50px]"
                                            type="button"
                                            @click="tieredPrice.deleted = true"
                                        >
                                            Delete
                                        </CustomButton>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </template>
                </template>
            </template>
        </CustomTableSecond>
    </div>
</template>

<style scoped>

</style>

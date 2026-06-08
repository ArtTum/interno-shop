<script setup>

import {computed, ref, toRefs, watch} from "vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomInput from "@components/global/CustomInput.vue";

import {useStore} from "vuex";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import TestimonialsComponentFieldsDragAndDrop
    from "@components/page/BuilderElements/Testimonials/TestimonialsComponentFieldsDragAndDrop.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";

const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);

const newItem = ref(null);
const emits = defineEmits([
    'update:modelValue',
    'save'
]);

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    isUpdate: {
        type: Boolean,
        default: false
    },
    params: {
        type: Object,
        default: {}
    },
    pageTypeName: {
        type: String,
    },
});

const {modelValue} = toRefs(props);
const component = ref(modelValue.value);

watch(modelValue, (newVal) => {
    component.value = newVal;

    if (Object.keys(component.value.config).length === 0) {
        component.value.config = {}
    }
}, {immediate: true});
watch(
    () => component.value,
    (newVal) => {
        emits('update:modelValue', newVal);
    },
    {deep: true}
);

const activeTab = ref('general');

const tabsRoutes = [
    {key: 'general', title: 'General *', icon: ['far', 'gear']},
    {key: 'testimonials', title: 'Testimonials', icon: ['far', 'comment']},
];

</script>

<template>
    <div class="grid grid-cols-1">
        <div class="w-full">
            <div class="mb-6 flex flex-wrap gap-5 border-b border-stroke">
                <template
                    :key="key"
                    v-for="(tabRoute, key) in tabsRoutes"
                >
                    <router-link
                        to=""
                        @click="activeTab = tabRoute.key"
                        :class="{
                                    'text-primary border-primary': activeTab === tabRoute.key,
                                    'border-transparent': activeTab !== tabRoute.key
                                }"
                        class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base px-2"
                    >
                        <font-awesome-icon :icon="tabRoute.icon"/>
                        {{ tabRoute.title }}
                    </router-link>
                </template>
            </div>
            <div v-if="activeTab === 'general'">
                <div class="grid grid-cols-3 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.more_than"
                            name="more_than"
                            label="More than (text translation) *"
                            type="text"
                            placeholder="More than (text translation)"
                        />
                    </div>
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.satisfied_customers"
                            name="satisfied_customers"
                            label="Satisfied customers (text translation) *"
                            type="text"
                            placeholder="Satisfied customers (text translation)"
                        />
                    </div>
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.based_on_reviews"
                            name="based_on_reviews"
                            label="Based on 20,000 reviews (text translation) *"
                            type="text"
                            placeholder="Based on 20,000 reviews (text translation)"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.tags"
                            name="tags"
                            label="Tags"
                            type="text"
                            placeholder="Example: Transparency, Quality, Customer service"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.customers_count"
                            name="customers_count"
                            label="Satisfied customers (count) *"
                            type="text"
                            placeholder="Satisfied customers (count) *"
                        />
                    </div>
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.rate"
                            name="rate"
                            label="Rate *"
                            type="text"
                            placeholder="Rate (4.77, 5.00, 4.25 or another decimal)"
                        />
                    </div>
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.button_text"
                            name="button_text"
                            label="Button text (text translation) *"
                            type="text"
                            placeholder="Button text (text translation)"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                            v-model="component.config.button_url"
                            name="button_url"
                            label="Button url *"
                            type="text"
                            placeholder="Button url *"
                        />
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'testimonials'">
                <div class="flex justify-center">
                    <div>
                        <template v-if="!newItem">
                            <CustomButton
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                @click="newItem = {date: null, comment: null}"
                                title="Add new item"
                                type="button"
                                class="h-[45px] flex items-center gap-2 rounded bg-meta-3 px-3.5 mt-5 font-medium text-white hover:bg-opacity-80 ml-auto"
                            >
                                <font-awesome-icon :icon="'plus'" class="size-5"/>
                                New comment
                            </CustomButton>
                        </template>
                        <template v-else>
                            <div class="flex">
                                <CustomButton
                                    @click="newItem = null"
                                    class="block w-full rounded border border-stroke bg-gray px-4.5 py-2.5 text-center font-medium text-black hover:bg-opacity-60"
                                    type="button"
                                >
                                    <font-awesome-icon :icon="['far', 'arrow-rotate-left']"/>
                                    Cancel
                                </CustomButton>

                                <template v-if="!newItem.date || !newItem.comment">
                                    <CustomButton
                                        disabled
                                        class="flex items-center gap-2 ml-3 rounded bg-primary px-4.5 py-2.5  font-medium text-white hover:bg-opacity-80"
                                        type="button"
                                    >
                                        <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                        Save
                                    </CustomButton>
                                </template>
                                <template v-else>
                                    <CustomButton
                                        @click="emits('save', newItem), newItem = null"
                                        class="flex items-center gap-2 ml-3 rounded bg-primary px-4.5 py-2.5  font-medium text-white hover:bg-opacity-80"
                                        type="button"
                                    >
                                        <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                                        Save
                                    </CustomButton>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                <template v-if="newItem">
                    <div class="grid grid-cols-3 gap-9 text-left mt-5 px-6.5">
                        <div class="flex flex-col gap-9 col-span-1">
                            <CustomDatePicker
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                placeholder="yyyy/mm/dd"
                                label="Date *"
                                format="Y-m-d"
                                v-model="newItem.date"
                            />
                        </div>
                        <div class="flex flex-col gap-9 col-span-2">
                            <CustomTextarea
                                :disabled="isUpdate && !auth.user_group.permissions_by_name[pageTypeName][0].can_edit"
                                v-model="newItem.comment"
                                name="comment"
                                label="Comment *"
                                type="text"
                                placeholder="Comment"
                            />
                        </div>
                    </div>
                </template>
                <template v-if="component.items">
                    <TestimonialsComponentFieldsDragAndDrop
                        :params="params"
                        class="col-8"
                        v-model="component.items"
                    />
                </template>
            </div>
            <div v-if="activeTab === 'contract_settings'">
                <div class="grid grid-cols-2 gap-9 text-left mt-5">
                    <div class="flex flex-col gap-9">
                        <CustomInput
                            :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                            v-model="component.config.pdf_header"
                            name="pdf_header"
                            label="PDF header"
                            type="text"
                            placeholder="PDF header"
                        />
                    </div>
                    <div class="flex flex-col">
                        <label class="block font-medium text-black mb-2.5">PDF footer text</label>
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                            :model="component.config.pdf_footer_text"
                            @updateValue="(value) => {
                                component.config.pdf_footer_text = value
                            }"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-9 text-left mt-5">
                    <div class="flex flex-col">
                        <label class="block font-medium text-black mb-2.5">PDF top text</label>
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                            :model="component.config.pdf_top_text"
                            @updateValue="(value) => {
                                component.config.pdf_top_text = value
                            }"
                        />
                    </div>
                    <div class="flex flex-col">
                        <label class="block font-medium text-black mb-2.5">PDF bottom text</label>
                        <CKEditorComponent
                            :disabled="isUpdate && !auth.user_group.permissions_by_name.pages[0].can_edit"
                            :model="component.config.pdf_bottom_text"
                            @updateValue="(value) => {
                                component.config.pdf_bottom_text = value
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

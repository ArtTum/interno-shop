<style scoped>
.item-container {
    margin: 0;
}
</style>

<template>
    <draggable
        :disabled="disabled"
        v-bind="dragOptions"
        tag="div"
        class="item-container"
        :list="realValue"
        @input="emitter"
        item-key="index"
    >
        <template #item="{ element, index }">
            <div
                v-if="!element.deleted"
                class="mt-3"
            >
                <div class="flex justify-end">
                    <div class="flex">
                        <router-link
                            title="Drag"
                            :to="''"
                            class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] bg-gray border border-stroke border-b-0 text-white text-center font-medium"
                        >
                            <font-awesome-icon :icon="['far', 'arrows-from-dotted-line']"
                                               class="w-full text-black mr-auto"/>
                        </router-link>
                        <router-link
                            @click="element.open = !element.open"
                            title="Open"
                            :to="''"
                            class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] bg-gray border border-stroke border-b-0 text-white text-center font-medium"
                        >
                            <font-awesome-icon
                                class="w-full text-black mr-auto"
                                :class="{ 'rotate-180': element.open }"
                                icon="chevron-down"
                            />
                        </router-link>
                        <router-link
                            @click="element.deleted = true"
                            title="Delete"
                            :to="''"
                            class="flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-gray border border-stroke border-r-0 border-b-0 text-white text-center font-medium hover:bg-danger hover:border-danger hover:text-white"
                        >
                            <font-awesome-icon :icon="['fas', 'xmark']" class="w-full text-black mr-auto"/>
                        </router-link>
                    </div>
                </div>
                <div class="border border-stroke rounded text-left">
                    <div class="bg-gray p-3">
                        Item #{{ index + 1 }}
                    </div>
                    <template v-if="element.open">
                        <div class="mb-4">
                            <div class="grid grid-cols-3 gap-9 text-left mt-5 px-6.5">
                                <div class="flex flex-col gap-9">
                                    <CustomSelect
                                        :disabled="disabled"
                                        label="Field type *"
                                        v-model="element.field_type"
                                        mode="single"
                                        placeholder="Select *"
                                        :options="params.fieldTypes"
                                        class="py-2 rounded-lg border-stroke bg-transparent"
                                    />
                                </div>
                                <div class="flex flex-col gap-9">
                                    <CustomInput
                                        :disabled="disabled"
                                        v-model="element.label"
                                        name="label"
                                        label="Label *"
                                        type="text"
                                        placeholder="Label *"
                                    />
                                </div>
                                <div class="flex flex-col gap-9">
                                    <CustomInput
                                        :disabled="disabled"
                                        v-model="element.placeholder"
                                        name="placeholder"
                                        label="Placeholder *"
                                        type="text"
                                        placeholder="Placeholder *"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-9 text-left mt-5 px-6.5">
                                <div class="flex flex-col gap-9">
                                    <CustomInput
                                        :disabled="disabled"
                                        v-model="element.required"
                                        name="required"
                                        label="Field is required"
                                        type="checkbox"
                                    />
                                </div>
                                <div class="flex flex-col gap-9">
                                    <CustomInput
                                        :disabled="disabled"
                                        v-model="element.use_as_name"
                                        name="use_as_name"
                                        label="Us as name"
                                        type="checkbox"
                                    />
                                </div>
                                <div class="flex flex-col gap-9">
                                    <CustomInput
                                        :disabled="disabled"
                                        v-model="element.make_as_full_width"
                                        name="make_as_full_width"
                                        label="Make the field full width."
                                        type="checkbox"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </draggable>
</template>

<script>
import draggable from 'vuedraggable';
import RowSection from "@components/page/RowSection.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

export default {
    name: "faq-component-item-drag-and-drop",
    methods: {
        emitter(value) {
            this.$emit("input", value);
        }
    },
    components: {
        CustomSelect,
        CustomInput, CustomTextarea,
        RowSection,
        draggable,
    },
    computed: {
        dragOptions() {
            return {
                animation: 0,
                group: "faq",
                ghostClass: "ghost"
            };
        },
        realValue() {
            return this.value ? this.value : this.list;
        }
    },
    props: {
        value: {
            required: false,
            type: Array,
            default: null
        },
        list: {
            required: false,
            type: Array,
            default: null
        },
        parent: {
            type: Boolean,
            required: false,
            default: false
        },
        disabled: {
            type: Boolean,
            required: false
        },
        params: {
            type: Object,
            default: {}
        },
    }
};
</script>

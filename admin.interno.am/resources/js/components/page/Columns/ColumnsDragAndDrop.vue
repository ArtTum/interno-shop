<style scoped>

</style>

<template>
    <draggable
        :disabled="disabled"
        v-bind="dragOptions"
        tag="div"
        :list="realValue"
        @input="emitter"
        item-key="index"
    >
        <template #item="{ element, index }">
            <div
                @click="this.$emit('past-component', index);"
                class="flex flex-col my-6.5 min-h-[100px] border-[1.5px] shadow-default relative top-0"
                :class="`col-span-${element.responsive_settings['mobile']} sm:col-span-${element.responsive_settings['tablet']} md:col-span-${element.responsive_settings['desktop']} ${index === 0 ? 'ml-3': 'mx-1.5'} ${columnsLength === index + 1 ? 'mr-3': 'mx-1.5'}`"
            >
                <template v-if="element.components && element.components.length">
                    <PageSectionComponentsDragAndDrop
                        :disabled="disabled"
                        :section-index="sectionIndex"
                        :column-index="index"
                        @cloneComponent="(componentIndex, componentKey) => {
                            this.$emit('clone-component', index, componentIndex, componentKey);
                        }"
                        @editComponent="(componentIndex, componentKey) => {
                            this.$emit('open-edit-component-popup', index, componentIndex, componentKey);
                        }"
                        @deleteComponent="(componentIndex) => {
                            this.$emit('delete-component', index, componentIndex);
                        }"
                        :last-index="(element.components.length - 1)"
                        class="col-8"
                        v-model="element.components"
                    />
                </template>
                <div class="absolute center-div shadow-default">
                    <div>
                        <CustomButton
                            :disabled="disabled"
                            @click="this.$emit('open-components-popup-for-column', index);"
                            type="button"
                            class="w-[45px] h-[45px] flex items-center gap-2 rounded bg-primary py-2 px-3.5 font-medium text-white hover:bg-opacity-80 ml-auto"
                        >
                            <font-awesome-icon :icon="'plus'" class="size-5"/>
                        </CustomButton>
                    </div>
                </div>
            </div>
        </template>
    </draggable>
</template>

<script>
import draggable from 'vuedraggable';
import PageSectionComponentsDragAndDrop from "@components/page/PageSectionComponentsDragAndDrop.vue";
import CustomButton from "@components/global/CustomButton.vue";
import RowButtonsGroup from "@components/page/RowButtonsGroup.vue";

export default {
    name: "columns-drag-and-drop",
    methods: {
        emitter(value) {
            this.$emit("input", value);
        },
    },
    components: {
        RowButtonsGroup,
        CustomButton,
        PageSectionComponentsDragAndDrop,
        draggable,
    },
    computed: {
        dragOptions() {
            return {
                animation: 0,
                group: "section_columns",
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
        sectionIndex: {
            required: true,
            type: Number
        },
        columnsLength: {
            required: true,
            type: Number
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
    }
};
</script>

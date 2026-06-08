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
            <div class="mt-7" v-if="!element.deleted">
                <template v-if="element.responsive_settings.config.section_header">
                    <p v-html="element.responsive_settings.config.section_header"></p>
                </template>
                <template v-else>
                    <p>Section: #{{ index + 1 }}</p>
                </template>
                <RowSection
                    :disabled="disabled"
                    @editSectionColumns="(type) => {
                     this.$emit('editSectionColumns', index, type)
                    }"
                    @editSection="() => {
                     this.$emit('editSection', index)
                    }"
                    @updateStatus="(value) => {
                     element.status = value;
                    }"
                    @cloneSection="() => {
                     this.$emit('cloneSection', index)
                    }"
                    @additionalColumn="() => {
                     this.$emit('additionalColumn', index)
                    }"
                    @pastComponent="(columnIndex) => {
                     this.$emit('pastComponent', index, columnIndex)
                    }"
                    @openComponentsPopupForColumn="(columnIndex) => {
                     this.$emit('openComponentsPopupForColumn', index, columnIndex)
                    }"
                    @openEditComponentPopup="(columnIndex, componentIndex, componentKey) => {
                     this.$emit('openEditComponentPopup', index, columnIndex, componentIndex, componentKey)
                    }"
                    @cloneComponent="(columnIndex, componentIndex, componentKey) => {
                     this.$emit('cloneComponent', index, columnIndex, componentIndex, componentKey)
                    }"
                    @deleteSection="element.deleted = true"
                    :item="element"
                    :section-index="index"
                ></RowSection>
            </div>
        </template>
    </draggable>
</template>

<script>
import draggable from 'vuedraggable';
import RowSection from "@components/page/RowSection.vue";
import RowButtonsGroup from "@components/page/RowButtonsGroup.vue";

export default {
    name: "page-nested-drag-and-drop",
    methods: {
        emitter(value) {
            this.$emit("input", value);
        }
    },
    components: {
        RowButtonsGroup,
        RowSection,
        draggable,
    },
    computed: {
        dragOptions() {
            return {
                animation: 0,
                group: "sections",
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
    }
};
</script>

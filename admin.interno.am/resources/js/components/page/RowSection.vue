<script setup>
import RowButtonsGroup from "@components/page/RowButtonsGroup.vue";
import ColumnsDragAndDrop from "@components/page/Columns/ColumnsDragAndDrop.vue";

const props = defineProps({
    item: {
        type: Object
    },
    sectionIndex: {
        type: Number
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emits = defineEmits([
    'edit-section-columns', 'delete-section', 'open-components-popup-for-column',
    'open-edit-component-popup', 'edit-section', 'clone-section', 'update-status', 'clone-component', 'past-component',
    'additional-column'
]);

</script>

<template>
    <div class="shadow-default">
        <RowButtonsGroup
            :disabled="disabled"
            :type="item.type"
            :status="item.status"
            :status-id="`${item.status}_${sectionIndex}`"
            @updateStatus="(value) => {
               emits('update-status', value)
            }"
            @editSectionColumns="(type) => {
               emits('edit-section-columns', type)
            }"
            @editSection="() => {
               emits('edit-section')
            }"
            @cloneSection="() => {
               emits('clone-section')
            }"
            @additionalColumn="() => {
               emits('additional-column')
            }"
            @deleteSection="emits('delete-section')"
        />
        <ColumnsDragAndDrop
            :disabled="disabled"
            :columns-length="item.columns.length"
            :section-index="sectionIndex"
            @past-component="(columnId) => {
               emits('past-component', columnId)
            }"
            @cloneComponent="(columnKey, componentIndex, componentKey) => {
                    emits('clone-component', columnKey, componentIndex, componentKey)
                }"
            @openEditComponentPopup="(columnKey, componentIndex, componentKey) => {
                    emits('open-edit-component-popup', columnKey, componentIndex, componentKey)
                }"
            @openComponentsPopupForColumn="(columnKey) => {
                    emits('open-components-popup-for-column', columnKey)
                }"
            @deleteComponent="(columnKey, componentIndex) => {
                    item.columns[columnKey].components[componentIndex].deleted = true;
                }"
            :class="`grid min-h-[100px] border-[1.5px] grid-cols-${item.responsive_settings['mobile']} sm:grid-cols-${item.responsive_settings['tablet']} md:grid-cols-${item.responsive_settings['desktop']}`"
            v-model="item.columns"
        />
    </div>
</template>

<style scoped>

</style>

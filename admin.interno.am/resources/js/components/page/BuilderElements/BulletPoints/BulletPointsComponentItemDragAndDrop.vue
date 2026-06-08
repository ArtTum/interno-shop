<script>
import draggable from 'vuedraggable';
import RowSection from "@components/page/RowSection.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

export default {
    name: "bullet-points-component-fields-drag-and-drop",
    methods: {
        emitter(value) {
            this.$emit("input", value);
        }
    },
    components: {
        CKEditorComponent,
        CustomMediaList,
        CustomInput,
        CustomTextarea,
        RowSection,
        draggable,
    },
    computed: {
        dragOptions() {
            return {
                animation: 0,
                group: "bullet_points",
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
                        <div class="text-left my-5 px-6.5">
                            <div>
                                <label class="block font-medium text-black mb-2">Row text *</label>
                            </div>
                            <CKEditorComponent
                                :model="element.row_text"
                                @updateValue="(value) => {element.row_text = value}"
                            />
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </draggable>
</template>

<style scoped>
.item-container {
    margin: 0;
}
</style>

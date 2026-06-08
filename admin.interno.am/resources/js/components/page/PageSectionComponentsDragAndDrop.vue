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
            <div v-if="!element.deleted">
                <div class="flex flex-col text-left bg-gray shadow-default">
                    <div class="flex justify-between h-[30px]">
                        <div class="flex">
                            <router-link
                                title="Drag"
                                :to="''"
                                class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] bg-white border border-white border-b-0 text-white text-center font-medium"
                            >
                                <font-awesome-icon :icon="['far', 'arrows-from-dotted-line']"
                                                   class="w-full text-black mr-auto"/>
                            </router-link>
                            <router-link
                                :to="''"
                                class="group relative justify-center mr-1 flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-white border !border-b-0 text-white text-center font-medium hover:bg-primary hover:border-primary"
                            >
                                <div
                                    class="absolute events-none z-20 !border-0 whitespace-nowrap rounded bg-black py-1.5 px-4.5 text-sm font-medium text-white opacity-0 group-hover:opacity-100 bottom-full left-1/2 mb-3 -translate-x-1/2">
                                    <span
                                        class="absolute -z-10 h-2 w-2 rotate-45 rounded-sm bg-black bottom-[-3px] left-1/2 -translate-x-1/2"></span>
                                    <span v-html="element.name"></span>
                                </div>
                                <button :class="['inline-flex font-medium text-black']">
                                    <font-awesome-icon :icon="['far', 'info']"/>
                                </button>
                            </router-link>
                        </div>
                        <div class="flex">
                            <router-link
                                @click.stop="this.$emit('cloneComponent', index, element.component_key)"
                                title="Clone"
                                :to="''"
                                class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-white border border-white border-b-0 text-white text-center font-medium hover:bg-primary hover:border-primary"
                            >
                                <font-awesome-icon :icon="['fasr', 'clone']" class="w-full text-black mr-auto"/>
                            </router-link>
                            <router-link
                                @click="this.$emit('editComponent', index, element.component_key)"
                                title="Edit"
                                :to="''"
                                class="mr-1 flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-white border border-white border-b-0 text-white text-center font-medium hover:bg-primary hover:border-primary"
                            >
                                <font-awesome-icon :icon="['far', 'pen-to-square']" class="w-full text-black mr-auto"/>
                            </router-link>
                            <router-link
                                @click="this.$emit('deleteComponent', index)"
                                title="Delete"
                                :to="''"
                                class="flex items-center cursor-pointer w-[27px] h-[27px] hover-border-white-trigger bg-white border border-white border-r-0 border-b-0 text-white text-center font-medium hover:bg-danger hover:border-danger hover:text-white"
                            >
                                <font-awesome-icon :icon="['fas', 'xmark']" class="w-full text-black mr-auto"/>
                            </router-link>
                        </div>
                    </div>
                    <div>
                        <img
                            :alt="element.name"
                            :src="element.layout_image"
                        >
                    </div>
                </div>
                <template v-if="index !== lastIndex">
                    <hr
                        :class="{'mt-0': !index}"
                        class="text-gray my-2"
                    >
                </template>
            </div>
        </template>
    </draggable>
</template>

<script>
import draggable from 'vuedraggable';

export default {
    name: "page-section-component-drag-and-drop",
    methods: {
        emitter(value) {
            this.$emit("input", value);
        }
    },
    components: {
        draggable,
    },
    computed: {
        dragOptions() {
            return {
                animation: 0,
                group: `column_${this.columnIndex}_components`,
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
        disabled: {
            type: Boolean,
            required: false
        },
        lastIndex: {
            type: Number,
            default: 0
        },
        columnIndex: {
            required: true,
            type: Number
        },
    }
};
</script>

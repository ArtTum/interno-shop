<style scoped>
.item-container {
    margin: 0;
    display: flex;
    flex-direction: column;
}

.item {
    border: solid black 1px;
    background-color: #fefefe;
}

.item-sub {
    margin: 0 0 0 1rem;
    min-height: 20px;
}

.items-div {
    width: 600px;
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
        item-key="id"
    >
        <template #item="{ element }">
            <div class="items-div">
                <div class="item mx-1 my-1 cursor-move py-1.5 px-3 flex justify-between">
                    <div>
                        <button
                            class="hover:text-primary"
                        >
                            <font-awesome-icon :icon="['far', 'arrows-maximize']"/>
                        </button>
                        {{ element.name }}
                    </div>
                    <div>
                        <RouterLink
                            class="float-right ml-2"
                            :to="`/contents/menus/update/${element.id}/${languageId}/${type}`"
                        >
                            <button
                                class="hover:text-primary"
                                title="Edit"
                            >
                                <font-awesome-icon :icon="['far', 'pen-to-square']"/>
                            </button>
                        </RouterLink>
                    </div>
                </div>
                <nested-drag-and-drop
                    class="item-sub"
                    :disabled="disabled"
                    :list="element.elements"
                    :language-id="languageId"
                    :type="type"
                />
            </div>
        </template>
    </draggable>
</template>

<script>
import draggable from 'vuedraggable';

export default {
    name: "nested-drag-and-drop",
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
                group: "menu_link",
                ghostClass: "ghost"
            };
        },
        realValue() {
            return this.value ? this.value : this.list;
        }
    },
    props: {
        languageId: {
          type: Number,
        },
        type: {
          type: Number,
        },
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

<script setup>
import {useStore} from "vuex";
import {computed} from "vue";

const store = useStore();

const props = defineProps({
    getterVariable: {
        type: String
    },
    getterVariableForText: {
        type: String,
        required: false
    },
    mutationVariable: {
        type: String
    },
    actionVariable: {
        type: String
    },
    params: {
        type: Object,
        required: false,
        default: {}
    },
    parentName: {
        type: String,
        default: ''
    }
})

const modalOpen = computed(() => store.getters[props.getterVariable]);
const text = computed(() => store.getters[props.getterVariableForText]);

const emits = defineEmits([
    'fetch',
    'fetch-variations',
    'media-delete',
    'fetch-params'
]);

const deleteAction = async () => {
    store.commit(props.mutationVariable, {value: false});
    await store.dispatch(props.actionVariable, props.params);
    store.commit(props.mutationVariable, {id: null});

    if (props.parentName === 'media_index') {
        emits('media-delete');
    } else {
        if (text.value && (text.value.includes('variant') || text.value.includes('account'))) {
            emits('fetch-variations')
        } else {
            emits('fetch');
        }
    }

    emits('fetch-params');
};
</script>

<template>
    <div
        v-show="modalOpen"
        class="fixed top-0 left-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5"
    >
        <div
            ref="target"
            class="w-full max-w-142.5 rounded-lg bg-white py-12 px-8 text-center md:py-15 md:px-17.5"
        >
            <span class="mx-auto inline-block p-4 rounded-full bg-wrong-color">
                <font-awesome-icon :icon="['far', 'triangle-exclamation']" class="text-danger" size="2x"/>
            </span>
            <h3 class="mt-5.5 pb-2 text-xl font-bold text-black sm:text-2xl">
                Are you sure you want to delete?
            </h3>
            <p class="mb-10 font-medium">
                <template v-if="text !== undefined && text">
                    {{ text }}
                </template>
                <template v-else>
                    This action is irreversible and cannot be restored after deletion.
                </template>
            </p>
            <div class="-mx-3 flex flex-wrap gap-y-4">
                <div class="w-full px-3 2xsm:w-1/2">
                    <button
                        type="button"
                        @click="store.commit(props.mutationVariable, {
                                    value: false,
                                    id: null
                                })"
                        class="block w-full rounded border border-stroke bg-gray p-3 text-center font-medium text-black hover:bg-opacity-60"
                    >
                        Cancel
                    </button>
                </div>
                <div class="w-full px-3 2xsm:w-1/2">
                    <button
                        type="button"
                        @click="deleteAction()"
                        class="block w-full rounded border border-meta-1 bg-meta-1 p-3 text-center font-medium text-white hover:bg-opacity-90"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

import CustomTextarea from "@components/global/CustomTextarea.vue";

import {useStore} from "vuex";
import {computed, reactive, watch} from "vue";

const store = useStore();

const props = defineProps({
    getterVariable: {
        type: String
    },
    mutationVariable: {
        type: String
    },
    params: {
        type: Object,
        required: false,
        default: {}
    },
})

const modalOpen = computed(() => store.getters[props.getterVariable]);
const data = computed(() => store.getters['translation/getEditData']);
const form = reactive({});

watch(data, (newItem) => {
    form.id = newItem.id;
    form.translation_id = newItem.selected_variable_translation ? newItem.selected_variable_translation.id : null;
    form.value = newItem.selected_variable_translation ? newItem.selected_variable_translation.value : null;
    form.base = newItem.variable_translation ? newItem.variable_translation.value : null;
    form.language_id = props.params.language_id
}, {immediate: true});

const emits = defineEmits([
    'fetch-variations',
    'fetch-params'
]);
const updateAction = async () => {
    try {
        store.commit(props.mutationVariable, {
            value: false,
            item: []
        })

        await store.dispatch('translation/update', form);

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully updated'
        });

        await store.dispatch('translation/fetchPageData', props.params);
    } catch (error) {
        form.errors = error;
    }
};
</script>

<template>
    <div
        v-show="modalOpen"
        class="fixed top-0 left-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5"
    >
        <div
            ref="target"
            class="w-full max-w-203 rounded-lg bg-white  py-12 px-8 text-center md:py-15 md:px-17.5"
        >
            <div class="-mx-3 flex flex-wrap gap-y-4">
                <div class="w-full px-3 2xsm:w-1/2">
                    <CustomTextarea
                        :disabled="true"
                        label="Base translations"
                        name="text"
                        placeholder="Enter text"
                        v-model="form.base"
                    />
                </div>
                <div class="w-full px-3 2xsm:w-1/2">
                    <CustomTextarea
                        label="Value "
                        name="text"
                        placeholder="Enter text"
                        v-model="form.value"
                    />
                </div>
            </div>
            <div class="grid-cols-1 flex gap-5 sm:grid-cols-1">
                <button
                    @click="store.commit(props.mutationVariable, {
                                    value: false,
                                    item: []
                                })"
                    class="block ml-auto  w-fit rounded border border-stroke bg-gray p-3 text-center font-medium text-black"
                >
                    Cancel
                </button>
                <button
                    @click="updateAction()"
                    class="block w-fit rounded border border-meta-3 bg-meta-3 p-3 text-center font-medium text-white hover:bg-opacity-90"
                >
                    Update
                </button>
            </div>
        </div>
    </div>
</template>

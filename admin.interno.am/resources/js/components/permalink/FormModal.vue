<script setup>

import {useStore} from "vuex";
import {computed, reactive, watch} from "vue";
import CustomInput from "@components/global/CustomInput.vue";

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
const data = computed(() => store.getters['permalink/getEditData']);
const form = reactive({});

watch(data, (newItem) => {
    form.id = newItem.id ? newItem.id : '';
    form.slug = newItem.slug;
    form.type = newItem.type;
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

        await store.dispatch('permalink/update', form);

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully updated'
        });

        await store.dispatch('permalink/fetchPageData', props.params);
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
                <div class="w-full px-3 text-left">
                    <CustomInput
                        label="Slug "
                        name="text"
                        placeholder="Enter text"
                        v-model="form.slug"
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

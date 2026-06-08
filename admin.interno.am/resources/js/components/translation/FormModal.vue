<script setup>

import CustomTextarea from "@components/global/CustomTextarea.vue";

import {useStore} from "vuex";
import {computed, reactive, ref, watch} from "vue";
import CustomButton from "@components/global/CustomButton.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

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
    languages: {
        type: Array,
        required: false,
        default: []
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

const isAiTranslateExpanded = ref(false);
const toggleAiTranslate = () => {
    isAiTranslateExpanded.value = !isAiTranslateExpanded.value;
};

const selectedLanguagesForTranslation = ref([]);
const generateAITranslations = async (isAll = false) => {
    await store.dispatch('translation/translateAI', {
        translation_id: form.translation_id,
        language_ids: isAll ? [] : selectedLanguagesForTranslation.value,
    })

    if (!isAll) selectedLanguagesForTranslation.value = [];

    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: `Successfully submitted (Will work in background)`
    });
}
</script>

<template>
    <div
        v-show="modalOpen"
        class="fixed top-0 left-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5"
    >
        <div
            ref="target"
            class="w-full max-w-203 rounded-lg bg-white py-8 px-8 text-center"
        >
            <div class="grid grid-cols-1 sm:gap-4 sm:grid-cols-2">
                <div class="w-full">
                    <CustomTextarea
                        :disabled="true"
                        label="Base translations"
                        name="text"
                        placeholder="Enter text"
                        v-model="form.base"
                    />
                </div>
                <div class="w-full">
                    <CustomTextarea
                        label="Value "
                        name="text"
                        placeholder="Enter text"
                        v-model="form.value"
                    />
                </div>
            </div>
            <div class="-mt-3" v-if="form.translation_id">
                <CustomButton
                    @click="toggleAiTranslate"
                    type="button"
                    class="ai-expand-button flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                >
                    <font-awesome-icon :icon="['fasr', 'robot']"/>
                    Translate with AI
                </CustomButton>
                <div class="pb-3 ai-translate-body text-left mt-4" :class="{ 'hidden': !isAiTranslateExpanded }">
                    <h2 class="text-black mt-2 font-bold">You can generate AI translations from this language to the
                        missing ones.</h2>
                    <div class="mt-2">
                        <div>
                            <CustomButton
                                type="button"
                                @click="generateAITranslations(true)"
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fas', 'robot']"/>
                                Generate AI translations (all missing)
                            </CustomButton>
                        </div>
                        <div class="max-w-[330px] mt-4">
                            <CustomSelect
                                v-model="selectedLanguagesForTranslation"
                                mode="tags"
                                placeholder="Select languages"
                                :options="languages"
                                :invalid-feedback-place="false"
                                :close-on-select="false"
                                :searchable="true"
                                :with-general="false"
                                class="py-2 rounded-lg border-stroke bg-transparent w-full"
                            />
                        </div>
                        <div class="mt-2">
                            <CustomButton
                                :title="!selectedLanguagesForTranslation.length ? 'First select languages' : ''"
                                :disabled="!selectedLanguagesForTranslation.length"
                                type="button"
                                @click="generateAITranslations()"
                                class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            >
                                <font-awesome-icon :icon="['fas', 'robot']"/>
                                Generate AI translations for selected languages
                            </CustomButton>
                        </div>
                    </div>
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

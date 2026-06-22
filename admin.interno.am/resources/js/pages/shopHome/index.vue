<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import {computed, ref} from "vue";
import {useStore} from "vuex";

const store = useStore();
const form = ref(null);
const isSaving = ref(false);

const clone = (value) => JSON.parse(JSON.stringify(value));
const makeId = (prefix = 'item') => `${prefix}-${Date.now()}-${Math.random().toString(16).slice(2)}`;

const languages = computed(() => form.value?.languages || []);
const baseLanguageCode = computed(() => languages.value[0]?.code || 'hy');
const menuGroups = computed(() => form.value?.menuGroups || []);

const localizedValue = (record) => {
    if (!record) return '';

    return record[baseLanguageCode.value] || record.hy || Object.values(record)[0] || '';
};

const targetOptions = computed(() => {
    return menuGroups.value.flatMap((group) => {
        const groupTitle = localizedValue(group.title);
        const children = group.children?.[baseLanguageCode.value] || group.children?.hy || Object.values(group.children || {})[0] || [];

        return [
            {
                value: `${group.key}|all`,
                label: groupTitle,
            },
            ...children.map((child, index) => ({
                value: `${group.key}|${index}`,
                label: `${groupTitle} / ${child}`,
            })),
        ];
    });
});

const targetValue = (selection) => {
    return `${selection.categoryKey || ''}|${typeof selection.childIndex === 'number' ? selection.childIndex : 'all'}`;
};

const applyTarget = (selection, value) => {
    const [categoryKey, childIndex] = String(value || '').split('|');

    selection.categoryKey = categoryKey || '';
    selection.childIndex = childIndex === 'all' || childIndex === undefined ? null : Number(childIndex);
};

const createSelection = (target = null) => {
    const option = target || targetOptions.value[0]?.value || '|all';
    const selection = {
        id: makeId('selection'),
        categoryKey: '',
        childIndex: null,
        limit: 8,
    };

    applyTarget(selection, option);

    return selection;
};

const createSection = () => ({
    id: makeId('home-section'),
    selections: [createSelection()],
});

const normalizeHomeSections = () => {
    form.value.homeSections = Array.isArray(form.value.homeSections) && form.value.homeSections.length
        ? form.value.homeSections
        : menuGroups.value.map((group) => ({
            id: makeId('home-section'),
            selections: [{
                id: makeId('selection'),
                categoryKey: group.key,
                childIndex: null,
                limit: 8,
            }],
        }));

    form.value.homeSections.forEach((section, sectionIndex) => {
        section.id = section.id || makeId('home-section');
        section.selections = Array.isArray(section.selections) && section.selections.length
            ? section.selections
            : [createSelection()];
        section.selections = section.selections.slice(0, sectionIndex === 0 ? 2 : 1);

        section.selections.forEach((selection) => {
            selection.id = selection.id || makeId('selection');
            selection.categoryKey = selection.categoryKey || menuGroups.value[0]?.key || '';
            selection.childIndex = typeof selection.childIndex === 'number' ? selection.childIndex : null;
            selection.limit = Number(selection.limit || 8);
        });
    });
};

const fetchConfig = async () => {
    const data = await store.dispatch('shopFrontend/fetchConfig');
    form.value = clone(data);
    normalizeHomeSections();
};

const addSection = () => {
    form.value.homeSections.push(createSection());
};

const removeSection = (index) => {
    form.value.homeSections.splice(index, 1);
    normalizeHomeSections();
};

const moveSection = (index, direction) => {
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= form.value.homeSections.length) return;

    const [section] = form.value.homeSections.splice(index, 1);
    form.value.homeSections.splice(targetIndex, 0, section);
    normalizeHomeSections();
};

const addSelection = (section) => {
    if (section.selections.length >= 2) return;

    section.selections.push(createSelection());
};

const removeSelection = (section, index) => {
    section.selections.splice(index, 1);

    if (!section.selections.length) {
        section.selections.push(createSelection());
    }
};

const save = async () => {
    isSaving.value = true;

    try {
        normalizeHomeSections();
        await store.dispatch('shopFrontend/updateConfig', form.value);
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Saved',
            message: 'Home page product sections were saved.'
        });
    } finally {
        isSaving.value = false;
    }
};

fetchConfig();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Home Page" :breadcrumb="[{path: '/', title: 'Dashboard'}]"/>

        <div v-if="form" class="space-y-6">
            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-black">Home Page Products</h3>
                        <p class="text-sm text-gray-500">Choose category or subcategory product lists and sort the homepage blocks.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="rounded bg-black px-4 py-2 font-medium text-white" @click="addSection">Add block</button>
                        <button :disabled="isSaving" type="button" class="rounded bg-primary px-5 py-2 font-medium text-white disabled:opacity-70" @click="save">
                            {{ isSaving ? 'Saving...' : 'Save changes' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div v-for="(section, sectionIndex) in form.homeSections" :key="section.id" class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h4 class="font-semibold text-black">Block {{ sectionIndex + 1 }}</h4>
                            <p class="text-sm text-gray-500">
                                {{ sectionIndex === 0 ? 'First block can show two product lists.' : 'This block can show one product list.' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="sectionIndex === 0" @click="moveSection(sectionIndex, -1)">Up</button>
                            <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="sectionIndex === form.homeSections.length - 1" @click="moveSection(sectionIndex, 1)">Down</button>
                            <button type="button" class="rounded border border-meta-1 px-3 py-2 text-meta-1" @click="removeSection(sectionIndex)">Remove</button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(selection, selectionIndex) in section.selections" :key="selection.id" class="grid grid-cols-[1fr_140px_auto] gap-3 rounded border border-stroke p-3 max-lg:grid-cols-1">
                            <label class="block">
                                <span class="mb-2 block font-medium text-black">Category / subcategory</span>
                                <select
                                    class="w-full rounded border border-stroke px-3 py-2 text-black"
                                    :value="targetValue(selection)"
                                    @change="applyTarget(selection, $event.target.value)"
                                >
                                    <option v-for="option in targetOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                            </label>

                            <label class="block">
                                <span class="mb-2 block font-medium text-black">Random limit</span>
                                <input v-model.number="selection.limit" type="number" min="1" class="w-full rounded border border-stroke px-3 py-2 text-black"/>
                            </label>

                            <div class="flex items-end">
                                <button type="button" class="rounded border border-meta-1 px-3 py-2 text-meta-1 disabled:opacity-40" :disabled="section.selections.length === 1" @click="removeSelection(section, selectionIndex)">Remove list</button>
                            </div>
                        </div>
                    </div>

                    <button
                        v-if="sectionIndex === 0 && section.selections.length < 2"
                        type="button"
                        class="mt-3 rounded border border-stroke px-4 py-2 font-medium text-black"
                        @click="addSelection(section)"
                    >
                        Add second list
                    </button>
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

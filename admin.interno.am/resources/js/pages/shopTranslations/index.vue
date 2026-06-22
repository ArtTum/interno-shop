<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import {computed, ref} from "vue";
import {useStore} from "vuex";

const store = useStore();
const form = ref(null);
const activeLanguage = ref('hy');
const isSaving = ref(false);
const search = ref('');
const newKey = ref('');

const clone = (value) => JSON.parse(JSON.stringify(value));

const languages = computed(() => form.value?.languages || []);
const translations = computed(() => form.value?.translations || {});
const activeLanguageLabel = computed(() => {
    return languages.value.find((language) => language.code === activeLanguage.value)?.label || activeLanguage.value;
});

const translationKeys = computed(() => {
    const keys = new Set();

    Object.values(translations.value).forEach((items) => {
        if (!items || typeof items !== 'object') return;

        Object.keys(items).forEach((key) => keys.add(key));
    });

    return Array.from(keys).sort((a, b) => a.localeCompare(b));
});

const filteredKeys = computed(() => {
    const needle = search.value.trim().toLowerCase();

    if (!needle) {
        return translationKeys.value;
    }

    return translationKeys.value.filter((key) => {
        const value = translations.value?.[activeLanguage.value]?.[key] || '';

        return key.toLowerCase().includes(needle) || String(value).toLowerCase().includes(needle);
    });
});

const labelForKey = (key) => {
    return key
        .replace(/([a-z])([A-Z])/g, '$1 $2')
        .replace(/[_-]+/g, ' ')
        .replace(/^./, (letter) => letter.toUpperCase());
};

const shouldUseTextarea = (key) => {
    const value = translations.value?.[activeLanguage.value]?.[key] || '';

    return String(value).length > 80 || /(text|description|intro|placeholder|aria)/i.test(key);
};

const normalizeTranslations = () => {
    form.value.translations = form.value.translations || {};

    const keys = new Set();

    Object.values(form.value.translations).forEach((items) => {
        if (!items || typeof items !== 'object') return;

        Object.keys(items).forEach((key) => keys.add(key));
    });

    languages.value.forEach((language) => {
        form.value.translations[language.code] = form.value.translations[language.code] || {};

        keys.forEach((key) => {
            form.value.translations[language.code][key] = form.value.translations[language.code][key] ?? '';
        });
    });
};

const fetchTranslations = async () => {
    const data = await store.dispatch('shopFrontend/fetchTranslations');
    form.value = clone(data);
    normalizeTranslations();
    activeLanguage.value = form.value.languages?.[0]?.code || 'hy';
};

const addKey = () => {
    const key = newKey.value.trim();

    if (!key) return;

    languages.value.forEach((language) => {
        form.value.translations[language.code] = form.value.translations[language.code] || {};
        form.value.translations[language.code][key] = form.value.translations[language.code][key] ?? '';
    });

    newKey.value = '';
};

const removeKey = (key) => {
    languages.value.forEach((language) => {
        delete form.value.translations[language.code]?.[key];
    });
};

const save = async () => {
    isSaving.value = true;

    try {
        const data = await store.dispatch('shopFrontend/updateTranslations', {
            translations: form.value.translations,
        });

        form.value = clone(data);
        normalizeTranslations();

        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Saved',
            message: 'Translations were saved.'
        });
    } finally {
        isSaving.value = false;
    }
};

fetchTranslations();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Translations" :breadcrumb="[{path: '/', title: 'Dashboard'}]"/>

        <div v-if="form" class="space-y-6">
            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-black">Frontend Translations</h3>
                        <p class="text-sm text-gray-500">Edit the public shop labels stored in the database.</p>
                    </div>
                    <button :disabled="isSaving" type="button" class="rounded bg-primary px-5 py-2 font-medium text-white disabled:opacity-70" @click="save">
                        {{ isSaving ? 'Saving...' : 'Save changes' }}
                    </button>
                </div>

                <div class="mb-5 flex flex-wrap gap-2">
                    <button
                        v-for="language in languages"
                        :key="language.code"
                        type="button"
                        class="rounded border px-4 py-2 font-medium"
                        :class="activeLanguage === language.code ? 'border-primary bg-primary text-white' : 'border-stroke text-black'"
                        @click="activeLanguage = language.code"
                    >
                        {{ language.label }} ({{ language.code }})
                    </button>
                </div>

                <div class="mb-5 grid grid-cols-[minmax(220px,1fr)_minmax(220px,1fr)_auto] gap-3 max-lg:grid-cols-1">
                    <label class="block">
                        <span class="mb-2 block font-medium text-black">Search</span>
                        <input v-model="search" class="w-full rounded border border-stroke px-3 py-2 text-black" placeholder="Search by key or value"/>
                    </label>
                    <label class="block">
                        <span class="mb-2 block font-medium text-black">New key</span>
                        <input v-model="newKey" class="w-full rounded border border-stroke px-3 py-2 text-black" placeholder="exampleButtonLabel" @keyup.enter="addKey"/>
                    </label>
                    <div class="flex items-end">
                        <button type="button" class="w-full rounded bg-black px-4 py-2 font-medium text-white" @click="addKey">Add key</button>
                    </div>
                </div>

                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <h4 class="font-semibold text-black">Text for {{ activeLanguageLabel }}</h4>
                    <span class="text-sm text-gray-500">{{ filteredKeys.length }} keys</span>
                </div>

                <div class="overflow-hidden rounded border border-stroke">
                    <div class="grid grid-cols-[260px_1fr_88px] bg-gray-2 text-sm font-semibold text-black max-lg:hidden">
                        <div class="border-r border-stroke px-4 py-3">Key</div>
                        <div class="border-r border-stroke px-4 py-3">Value</div>
                        <div class="px-4 py-3 text-center">Action</div>
                    </div>

                    <div v-for="key in filteredKeys" :key="key" class="grid grid-cols-[260px_1fr_88px] border-t border-stroke max-lg:grid-cols-1">
                        <div class="border-r border-stroke px-4 py-3 max-lg:border-r-0">
                            <div class="font-medium text-black">{{ labelForKey(key) }}</div>
                            <div class="break-all text-xs text-gray-500">{{ key }}</div>
                        </div>
                        <div class="border-r border-stroke px-4 py-3 max-lg:border-r-0">
                            <textarea
                                v-if="shouldUseTextarea(key)"
                                v-model="form.translations[activeLanguage][key]"
                                rows="3"
                                class="w-full rounded border border-stroke px-3 py-2 text-black"
                            ></textarea>
                            <input
                                v-else
                                v-model="form.translations[activeLanguage][key]"
                                class="w-full rounded border border-stroke px-3 py-2 text-black"
                            />
                        </div>
                        <div class="flex items-center justify-center px-4 py-3 max-lg:justify-start">
                            <button type="button" class="rounded border border-meta-1 px-3 py-2 text-meta-1" @click="removeKey(key)">Remove</button>
                        </div>
                    </div>

                    <div v-if="!filteredKeys.length" class="px-4 py-8 text-center text-gray-500">
                        No translations found.
                    </div>
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

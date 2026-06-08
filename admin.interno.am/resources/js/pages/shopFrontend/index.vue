<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import {computed, ref} from "vue";
import {useStore} from "vuex";

const store = useStore();
const form = ref(null);
const activeLanguage = ref('hy');
const newLanguage = ref({code: '', label: '', icon: '/assets/icons/flag.svg'});
const isSaving = ref(false);

const clone = (value) => JSON.parse(JSON.stringify(value));
const emptyLocaleRecord = () => Object.fromEntries((form.value?.languages || []).map((language) => [language.code, '']));

const languages = computed(() => form.value?.languages || []);
const activeLanguageLabel = computed(() => languages.value.find((language) => language.code === activeLanguage.value)?.label || activeLanguage.value);
const translationEntries = computed(() => {
    if (!form.value?.translations?.[activeLanguage.value]) return [];

    return Object.keys(form.value.translations[activeLanguage.value]).sort();
});

const ensureLanguageSlots = (code) => {
    if (!form.value.translations[code]) {
        const baseLanguage = languages.value[0]?.code || 'hy';
        form.value.translations[code] = Object.fromEntries(Object.keys(form.value.translations[baseLanguage] || {}).map((key) => [key, '']));
    }

    form.value.menuGroups.forEach((group) => {
        group.title = group.title || {};
        group.children = group.children || {};
        group.title[code] = group.title[code] || '';
        group.children[code] = Array.isArray(group.children[code]) ? group.children[code] : [];
    });

    form.value.products.forEach((product) => {
        product.title = product.title || {};
        product.title[code] = product.title[code] || '';
    });

    if (form.value.privacy?.content) {
        const basePrivacy = form.value.privacy.content.hy || Object.values(form.value.privacy.content)[0] || {};
        form.value.privacy.content[code] = form.value.privacy.content[code] || clone(basePrivacy);
    }
};

const fetchConfig = async () => {
    const data = await store.dispatch('shopFrontend/fetchConfig');
    form.value = clone(data);
    activeLanguage.value = form.value.languages?.[0]?.code || 'hy';
    form.value.languages.forEach((language) => ensureLanguageSlots(language.code));
};

const addLanguage = () => {
    const code = newLanguage.value.code.trim().toLowerCase();

    if (!code || languages.value.some((language) => language.code === code)) return;

    form.value.languages.push({
        code,
        label: newLanguage.value.label.trim() || code.toUpperCase(),
        icon: newLanguage.value.icon.trim() || '/assets/icons/flag.svg',
    });

    ensureLanguageSlots(code);
    activeLanguage.value = code;
    newLanguage.value = {code: '', label: '', icon: '/assets/icons/flag.svg'};
};

const removeLanguage = (code) => {
    if (languages.value.length <= 1) return;

    form.value.languages = form.value.languages.filter((language) => language.code !== code);
    delete form.value.translations[code];

    form.value.menuGroups.forEach((group) => {
        delete group.title?.[code];
        delete group.children?.[code];
    });

    form.value.products.forEach((product) => {
        delete product.title?.[code];
    });

    if (form.value.privacy?.content) {
        delete form.value.privacy.content[code];
    }

    if (activeLanguage.value === code) {
        activeLanguage.value = form.value.languages[0]?.code || 'hy';
    }
};

const addCategory = () => {
    form.value.menuGroups.push({
        key: `category-${Date.now()}`,
        title: emptyLocaleRecord(),
        children: Object.fromEntries(languages.value.map((language) => [language.code, []])),
    });
};

const removeCategory = (index) => {
    form.value.menuGroups.splice(index, 1);
};

const addProduct = () => {
    const nextId = Math.max(0, ...form.value.products.map((product) => Number(product.id) || 0)) + 1;
    form.value.products.push({
        id: nextId,
        title: emptyLocaleRecord(),
        price: '0',
        kind: 'black',
        image: '/assets/products/profile-black.png',
        isNew: false,
        options: {},
    });
};

const removeProduct = (index) => {
    form.value.products.splice(index, 1);
};

const addTranslationKey = () => {
    const key = window.prompt('Translation key');
    if (!key) return;

    languages.value.forEach((language) => {
        form.value.translations[language.code] = form.value.translations[language.code] || {};
        form.value.translations[language.code][key] = form.value.translations[language.code][key] || '';
    });
};

const removeTranslationKey = (key) => {
    languages.value.forEach((language) => {
        delete form.value.translations[language.code]?.[key];
    });
};

const childrenToText = (group) => (group.children?.[activeLanguage.value] || []).join('\n');
const updateChildren = (group, value) => {
    group.children[activeLanguage.value] = value.split('\n').map((item) => item.trim()).filter(Boolean);
};

const save = async () => {
    isSaving.value = true;

    try {
        await store.dispatch('shopFrontend/updateConfig', form.value);
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Saved',
            message: 'Shop frontend languages and content were saved.'
        });
    } finally {
        isSaving.value = false;
    }
};

fetchConfig();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Shop Frontend" :breadcrumb="[{path: '/', title: 'Dashboard'}]" />

        <div v-if="form" class="space-y-6">
            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-black">Languages</h3>
                        <p class="text-sm text-gray-500">These languages drive the frontend language switcher and localized fields.</p>
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

                <div class="grid grid-cols-4 gap-3 max-lg:grid-cols-2 max-sm:grid-cols-1">
                    <div v-for="language in languages" :key="language.code" class="rounded border border-stroke p-3">
                        <label class="mb-2 block text-sm font-medium text-black">Code</label>
                        <input v-model="language.code" disabled class="mb-3 w-full rounded border border-stroke px-3 py-2 text-black" />
                        <label class="mb-2 block text-sm font-medium text-black">Label</label>
                        <input v-model="language.label" class="mb-3 w-full rounded border border-stroke px-3 py-2 text-black" />
                        <label class="mb-2 block text-sm font-medium text-black">Flag icon path</label>
                        <input v-model="language.icon" class="mb-3 w-full rounded border border-stroke px-3 py-2 text-black" />
                        <button v-if="languages.length > 1" type="button" class="text-sm font-medium text-meta-1" @click="removeLanguage(language.code)">Remove</button>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-4 gap-3 max-lg:grid-cols-2 max-sm:grid-cols-1">
                    <input v-model="newLanguage.code" class="rounded border border-stroke px-3 py-2 text-black" placeholder="new code, e.g. de" />
                    <input v-model="newLanguage.label" class="rounded border border-stroke px-3 py-2 text-black" placeholder="label, e.g. Deutsch" />
                    <input v-model="newLanguage.icon" class="rounded border border-stroke px-3 py-2 text-black" placeholder="flag icon path" />
                    <button type="button" class="rounded bg-black px-4 py-2 font-medium text-white" @click="addLanguage">Add language</button>
                </div>
            </div>

            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <h3 class="mb-4 text-lg font-semibold text-black">Site Settings</h3>
                <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <label class="block"><span class="mb-2 block font-medium text-black">Brand logo</span><input v-model="form.settings.brandLogo" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                    <label class="block"><span class="mb-2 block font-medium text-black">Footer text</span><input v-model="form.settings.footerText" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                    <label class="block"><span class="mb-2 block font-medium text-black">Phone</span><input v-model="form.settings.contactPhone" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                    <label class="block"><span class="mb-2 block font-medium text-black">Email</span><input v-model="form.settings.contactEmail" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                    <label class="block"><span class="mb-2 block font-medium text-black">Address</span><input v-model="form.settings.contactAddress" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                    <label class="block"><span class="mb-2 block font-medium text-black">Map URL</span><input v-model="form.settings.contactMapUrl" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                </div>
            </div>

            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-black">Categories for {{ activeLanguageLabel }}</h3>
                    <button type="button" class="rounded bg-black px-4 py-2 font-medium text-white" @click="addCategory">Add category</button>
                </div>
                <div class="space-y-4">
                    <div v-for="(group, index) in form.menuGroups" :key="group.key" class="grid grid-cols-12 gap-3 rounded border border-stroke p-3 max-lg:grid-cols-1">
                        <label class="col-span-2 block"><span class="mb-2 block font-medium text-black">Key</span><input v-model="group.key" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        <label class="col-span-4 block"><span class="mb-2 block font-medium text-black">Title</span><input v-model="group.title[activeLanguage]" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        <label class="col-span-5 block"><span class="mb-2 block font-medium text-black">Children, one per line</span><textarea :value="childrenToText(group)" rows="3" class="w-full rounded border border-stroke px-3 py-2 text-black" @input="updateChildren(group, $event.target.value)"></textarea></label>
                        <div class="col-span-1 flex items-end"><button type="button" class="text-meta-1" @click="removeCategory(index)">Remove</button></div>
                    </div>
                </div>
            </div>

            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-black">Products for {{ activeLanguageLabel }}</h3>
                    <button type="button" class="rounded bg-black px-4 py-2 font-medium text-white" @click="addProduct">Add product</button>
                </div>
                <div class="space-y-4">
                    <div v-for="(product, index) in form.products" :key="product.id" class="grid grid-cols-12 gap-3 rounded border border-stroke p-3 max-xl:grid-cols-2 max-sm:grid-cols-1">
                        <label class="col-span-1 block"><span class="mb-2 block font-medium text-black">ID</span><input v-model="product.id" type="number" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        <label class="col-span-4 block"><span class="mb-2 block font-medium text-black">Title</span><input v-model="product.title[activeLanguage]" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        <label class="col-span-2 block"><span class="mb-2 block font-medium text-black">Price</span><input v-model="product.price" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        <label class="col-span-2 block"><span class="mb-2 block font-medium text-black">Kind</span><input v-model="product.kind" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        <label class="col-span-2 block"><span class="mb-2 block font-medium text-black">Image</span><input v-model="product.image" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        <label class="col-span-1 flex items-center gap-2 pt-8"><input v-model="product.isNew" type="checkbox" /> New</label>
                        <div class="col-span-12"><button type="button" class="text-meta-1" @click="removeProduct(index)">Remove product</button></div>
                    </div>
                </div>
            </div>

            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-black">Translations for {{ activeLanguageLabel }}</h3>
                    <button type="button" class="rounded bg-black px-4 py-2 font-medium text-white" @click="addTranslationKey">Add key</button>
                </div>
                <div class="grid grid-cols-2 gap-3 max-md:grid-cols-1">
                    <div v-for="key in translationEntries" :key="key" class="grid grid-cols-12 gap-2">
                        <label class="col-span-4 block"><span class="mb-1 block text-xs font-medium text-black">{{ key }}</span><input :value="key" disabled class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        <label class="col-span-7 block"><span class="mb-1 block text-xs font-medium text-black">Value</span><input v-model="form.translations[activeLanguage][key]" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        <div class="col-span-1 flex items-end"><button type="button" class="pb-2 text-meta-1" @click="removeTranslationKey(key)">x</button></div>
                    </div>
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

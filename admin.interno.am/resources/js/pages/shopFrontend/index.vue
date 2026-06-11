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
const categoryOptions = computed(() => {
    if (!form.value?.menuGroups) return [];

    return form.value.menuGroups.flatMap((group) => {
        const parentTitle = group.title?.[activeLanguage.value] || group.key;
        const children = group.children?.[activeLanguage.value] || [];
        const options = [{value: `${group.key}|`, label: parentTitle}];

        children.forEach((child, index) => {
            options.push({
                value: `${group.key}|${index}`,
                label: `${parentTitle} / ${child || `Subcategory ${index + 1}`}`,
            });
        });

        return options;
    });
});
const productOptionKeys = ['code', 'size', 'quantity', 'type', 'unit', 'piece', 'height', 'material', 'color'];
const defaultSocialLinks = [
    {label: 'Instagram', href: 'https://www.instagram.com/', external: true},
    {label: 'Facebook', href: 'https://www.facebook.com/', external: true},
];
const seoPages = [
    {key: 'home', label: 'Home'},
    {key: 'contact', label: 'Contact'},
    {key: 'cart', label: 'Cart'},
    {key: 'checkout-success', label: 'Checkout success'},
    {key: 'search', label: 'Search'},
    {key: 'category', label: 'Category pages'},
    {key: 'product', label: 'Product pages'},
];
const contactTranslationFields = [
    {key: 'contactHeroKicker', label: 'Hero kicker', type: 'input'},
    {key: 'contactHeroTitle', label: 'Hero title', type: 'input'},
    {key: 'contactHeroText', label: 'Hero text', type: 'textarea'},
    {key: 'contactInfoTitle', label: 'Info block title', type: 'input'},
    {key: 'contactPhone', label: 'Phone label', type: 'input'},
    {key: 'contactEmail', label: 'Email label', type: 'input'},
    {key: 'contactAddress', label: 'Address label', type: 'input'},
    {key: 'contactHours', label: 'Hours label', type: 'input'},
    {key: 'contactHoursValue', label: 'Hours value', type: 'input'},
    {key: 'contactFormTitle', label: 'Form title', type: 'input'},
    {key: 'firstName', label: 'Name field label', type: 'input'},
    {key: 'contactNamePlaceholder', label: 'Name placeholder', type: 'input'},
    {key: 'contactPhonePlaceholder', label: 'Phone placeholder', type: 'input'},
    {key: 'contactMessage', label: 'Message field label', type: 'input'},
    {key: 'contactMessagePlaceholder', label: 'Message placeholder', type: 'textarea'},
    {key: 'contactSend', label: 'Send button', type: 'input'},
];

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

    ensureSeoLanguageSlots(code);
};

const fetchConfig = async () => {
    const data = await store.dispatch('shopFrontend/fetchConfig');
    form.value = clone(data);
    normalizeSettings();
    normalizeSeo();
    normalizePrivacy();
    normalizeContactTranslations();
    activeLanguage.value = form.value.languages?.[0]?.code || 'hy';
    form.value.languages.forEach((language) => ensureLanguageSlots(language.code));
    form.value.products.forEach((product) => normalizeProduct(product));
};

const normalizeSettings = () => {
    form.value.settings = form.value.settings || {};
    form.value.settings.brandLogo = form.value.settings.brandLogo || '/assets/brand/logo.png';
    form.value.settings.footerText = form.value.settings.footerText || '';
    form.value.settings.contactPhone = form.value.settings.contactPhone || '';
    form.value.settings.contactEmail = form.value.settings.contactEmail || '';
    form.value.settings.contactAddress = form.value.settings.contactAddress || '';
    form.value.settings.contactMapUrl = form.value.settings.contactMapUrl || '';
    form.value.settings.socialLinks = Array.isArray(form.value.settings.socialLinks) && form.value.settings.socialLinks.length
        ? form.value.settings.socialLinks
        : clone(defaultSocialLinks);

    form.value.settings.socialLinks.forEach((link) => {
        link.label = link.label || '';
        link.href = link.href || '';
        link.external = link.external ?? true;
    });
};

const normalizeContactTranslations = () => {
    form.value.translations = form.value.translations || {};

    languages.value.forEach((language) => {
        form.value.translations[language.code] = form.value.translations[language.code] || {};

        contactTranslationFields.forEach((field) => {
            form.value.translations[language.code][field.key] = form.value.translations[language.code][field.key] || '';
        });
    });
};

const emptySeoRecord = (label = '') => ({
    title: label,
    metaTitle: label,
    metaDescription: '',
    metaKeywords: '',
});

const normalizeSeo = () => {
    form.value.seo = form.value.seo || {};

    seoPages.forEach((page) => {
        form.value.seo[page.key] = form.value.seo[page.key] || {};

        languages.value.forEach((language) => {
            form.value.seo[page.key][language.code] = {
                ...emptySeoRecord(page.label),
                ...(form.value.seo[page.key][language.code] || {}),
            };
        });
    });
};

const ensureSeoLanguageSlots = (code) => {
    form.value.seo = form.value.seo || {};

    seoPages.forEach((page) => {
        const baseLanguage = languages.value[0]?.code || 'hy';
        const baseSeo = form.value.seo[page.key]?.[baseLanguage] || emptySeoRecord(page.label);
        form.value.seo[page.key] = form.value.seo[page.key] || {};
        form.value.seo[page.key][code] = form.value.seo[page.key][code] || clone(baseSeo);
    });
};

const emptyPrivacyContent = () => ({
    kicker: '',
    title: '',
    intro: '',
    badgeTitle: '',
    badgeText: '',
    summaryLabel: '',
    summaryTitle: '',
    summaryText: '',
    checklist: [],
    updated: '',
    summaryAria: '',
    checklistAria: '',
    sections: [],
});

const normalizePrivacy = () => {
    form.value.privacy = form.value.privacy || {updatedAt: '', content: {}};
    form.value.privacy.content = form.value.privacy.content || {};

    languages.value.forEach((language) => {
        form.value.privacy.content[language.code] = {
            ...emptyPrivacyContent(),
            ...(form.value.privacy.content[language.code] || {}),
        };
        form.value.privacy.content[language.code].checklist = Array.isArray(form.value.privacy.content[language.code].checklist)
            ? form.value.privacy.content[language.code].checklist
            : [];
        form.value.privacy.content[language.code].sections = Array.isArray(form.value.privacy.content[language.code].sections)
            ? form.value.privacy.content[language.code].sections
            : [];
    });
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

    Object.values(form.value.seo || {}).forEach((pageSeo) => {
        delete pageSeo?.[code];
    });

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

const moveCategory = (index, direction) => {
    moveArrayItem(form.value.menuGroups, index, direction);
};

const activeChildren = (group) => {
    group.children = group.children || {};
    group.children[activeLanguage.value] = Array.isArray(group.children[activeLanguage.value]) ? group.children[activeLanguage.value] : [];

    return group.children[activeLanguage.value];
};

const addChild = (group) => {
    group.children = group.children || {};

    languages.value.forEach((language) => {
        group.children[language.code] = Array.isArray(group.children[language.code]) ? group.children[language.code] : [];
        group.children[language.code].push('');
    });
};

const removeChild = (group, index) => {
    languages.value.forEach((language) => {
        if (Array.isArray(group.children?.[language.code])) {
            group.children[language.code].splice(index, 1);
        }
    });
};

const moveChild = (group, index, direction) => {
    languages.value.forEach((language) => {
        if (Array.isArray(group.children?.[language.code])) {
            moveArrayItem(group.children[language.code], index, direction);
        }
    });
};

const moveArrayItem = (list, index, direction) => {
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= list.length) return;

    const [item] = list.splice(index, 1);
    list.splice(targetIndex, 0, item);
};

const addProduct = () => {
    const nextId = Math.max(0, ...form.value.products.map((product) => Number(product.id) || 0)) + 1;
    const firstCategory = form.value.menuGroups[0];
    form.value.products.push({
        id: nextId,
        slug: `product-${nextId}`,
        title: emptyLocaleRecord(),
        price: '0',
        kind: 'black',
        image: '/assets/products/profile-black.png',
        gallery: ['/assets/products/profile-black.png'],
        isNew: false,
        status: true,
        categoryKey: firstCategory?.key || '',
        categoryChildIndex: null,
        options: {},
    });
};

const normalizeProduct = (product) => {
    const firstCategory = form.value.menuGroups[0];

    product.slug = product.slug || `product-${product.id}`;
    product.gallery = Array.isArray(product.gallery) ? product.gallery : [product.image].filter(Boolean);
    product.status = product.status ?? true;
    product.categoryKey = product.categoryKey || firstCategory?.key || '';
    product.categoryChildIndex = product.categoryChildIndex ?? null;
    ensureProductOptions(product);
};

const removeProduct = (index) => {
    form.value.products.splice(index, 1);
};

const moveProduct = (index, direction) => {
    moveArrayItem(form.value.products, index, direction);
};

const ensureProductOptions = (product) => {
    product.options = product.options || {};

    productOptionKeys.forEach((key) => {
        product.options[key] = product.options[key] ?? '';
    });

    return product.options;
};

const productCategoryValue = (product) => {
    return `${product.categoryKey || ''}|${product.categoryChildIndex ?? ''}`;
};

const updateProductCategory = (product, value) => {
    const [categoryKey, childIndex] = value.split('|');
    product.categoryKey = categoryKey;
    product.categoryChildIndex = childIndex === '' ? null : Number(childIndex);
};

const galleryToText = (product) => {
    return Array.isArray(product.gallery) ? product.gallery.join('\n') : '';
};

const updateGallery = (product, value) => {
    product.gallery = value.split('\n').map((item) => item.trim()).filter(Boolean);
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
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-black">Site Settings</h3>
                </div>
                <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <label class="block"><span class="mb-2 block font-medium text-black">Brand logo</span><input v-model="form.settings.brandLogo" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                    <label class="block"><span class="mb-2 block font-medium text-black">Footer text</span><input v-model="form.settings.footerText" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                </div>

            </div>

            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-black">Categories for {{ activeLanguageLabel }}</h3>
                    <button type="button" class="rounded bg-black px-4 py-2 font-medium text-white" @click="addCategory">Add category</button>
                </div>
                <div class="space-y-4">
                    <div v-for="(group, index) in form.menuGroups" :key="group.key" class="rounded border border-stroke p-3">
                        <div class="grid grid-cols-12 gap-3 max-lg:grid-cols-1">
                            <label class="col-span-2 block"><span class="mb-2 block font-medium text-black">Key</span><input v-model="group.key" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                            <label class="col-span-5 block"><span class="mb-2 block font-medium text-black">Title</span><input v-model="group.title[activeLanguage]" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                            <div class="col-span-5 flex flex-wrap items-end gap-2">
                                <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="index === 0" @click="moveCategory(index, -1)">Up</button>
                                <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="index === form.menuGroups.length - 1" @click="moveCategory(index, 1)">Down</button>
                                <button type="button" class="rounded bg-black px-3 py-2 font-medium text-white" @click="addChild(group)">Add subcategory</button>
                                <button type="button" class="rounded border border-meta-1 px-3 py-2 text-meta-1" @click="removeCategory(index)">Remove category</button>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            <div v-for="(child, childIndex) in activeChildren(group)" :key="`${group.key}-${childIndex}`" class="grid grid-cols-12 gap-2 max-lg:grid-cols-1">
                                <label class="col-span-8 block">
                                    <span class="mb-1 block text-xs font-medium text-black">Subcategory {{ childIndex + 1 }}</span>
                                    <input v-model="group.children[activeLanguage][childIndex]" class="w-full rounded border border-stroke px-3 py-2 text-black" />
                                </label>
                                <div class="col-span-4 flex flex-wrap items-end gap-2">
                                    <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="childIndex === 0" @click="moveChild(group, childIndex, -1)">Up</button>
                                    <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="childIndex === activeChildren(group).length - 1" @click="moveChild(group, childIndex, 1)">Down</button>
                                    <button type="button" class="rounded border border-meta-1 px-3 py-2 text-meta-1" @click="removeChild(group, childIndex)">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-black">Products for {{ activeLanguageLabel }}</h3>
                    <button type="button" class="rounded bg-black px-4 py-2 font-medium text-white" @click="addProduct">Add product</button>
                </div>
                <div class="space-y-4">
                    <div v-for="(product, index) in form.products" :key="product.id" class="rounded border border-stroke p-3">
                        <div class="grid grid-cols-12 gap-3 max-xl:grid-cols-2 max-sm:grid-cols-1">
                            <label class="col-span-1 block"><span class="mb-2 block font-medium text-black">ID</span><input v-model="product.id" type="number" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                            <label class="col-span-2 block"><span class="mb-2 block font-medium text-black">Slug</span><input v-model="product.slug" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                            <label class="col-span-4 block"><span class="mb-2 block font-medium text-black">Title</span><input v-model="product.title[activeLanguage]" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                            <label class="col-span-2 block"><span class="mb-2 block font-medium text-black">Category</span>
                                <select :value="productCategoryValue(product)" class="w-full rounded border border-stroke px-3 py-2 text-black" @change="updateProductCategory(product, $event.target.value)">
                                    <option v-for="option in categoryOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                            </label>
                            <label class="col-span-1 block"><span class="mb-2 block font-medium text-black">Price</span><input v-model="product.price" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                            <label class="col-span-1 block"><span class="mb-2 block font-medium text-black">Kind</span><input v-model="product.kind" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                            <label class="col-span-1 block"><span class="mb-2 block font-medium text-black">Image</span><input v-model="product.image" class="w-full rounded border border-stroke px-3 py-2 text-black" /></label>
                        </div>

                        <div class="mt-3 grid grid-cols-12 gap-3 max-xl:grid-cols-2 max-sm:grid-cols-1">
                            <label class="col-span-5 block">
                                <span class="mb-2 block font-medium text-black">Gallery, one image path per line</span>
                                <textarea :value="galleryToText(product)" rows="3" class="w-full rounded border border-stroke px-3 py-2 text-black" @input="updateGallery(product, $event.target.value)"></textarea>
                            </label>
                            <div class="col-span-5 grid grid-cols-4 gap-2 max-lg:grid-cols-2">
                                <label v-for="optionKey in productOptionKeys" :key="optionKey" class="block">
                                    <span class="mb-1 block text-xs font-medium text-black">{{ optionKey }}</span>
                                    <input v-model="product.options[optionKey]" :type="optionKey === 'color' ? 'color' : 'text'" class="w-full rounded border border-stroke px-3 py-2 text-black" />
                                </label>
                            </div>
                            <div class="col-span-2 flex flex-wrap items-end gap-2">
                                <label class="flex items-center gap-2 rounded border border-stroke px-3 py-2 text-black"><input v-model="product.isNew" type="checkbox" /> New</label>
                                <label class="flex items-center gap-2 rounded border border-stroke px-3 py-2 text-black"><input v-model="product.status" type="checkbox" /> Active</label>
                                <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="index === 0" @click="moveProduct(index, -1)">Up</button>
                                <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="index === form.products.length - 1" @click="moveProduct(index, 1)">Down</button>
                                <button type="button" class="rounded border border-meta-1 px-3 py-2 text-meta-1" @click="removeProduct(index)">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-black">SEO Meta for {{ activeLanguageLabel }}</h3>
                    <p class="text-sm text-gray-500">Controls page title, meta title, description and keywords for frontend pages.</p>
                </div>
                <div class="space-y-4">
                    <div v-for="page in seoPages" :key="page.key" class="rounded border border-stroke p-3">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <h4 class="font-semibold text-black">{{ page.label }}</h4>
                            <span class="rounded bg-gray-100 px-2 py-1 text-xs text-black">{{ page.key }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 max-md:grid-cols-1">
                            <label class="block">
                                <span class="mb-2 block font-medium text-black">Title</span>
                                <input v-model="form.seo[page.key][activeLanguage].title" class="w-full rounded border border-stroke px-3 py-2 text-black" />
                            </label>
                            <label class="block">
                                <span class="mb-2 block font-medium text-black">Meta title</span>
                                <input v-model="form.seo[page.key][activeLanguage].metaTitle" class="w-full rounded border border-stroke px-3 py-2 text-black" />
                            </label>
                            <label class="block">
                                <span class="mb-2 block font-medium text-black">Meta description</span>
                                <textarea v-model="form.seo[page.key][activeLanguage].metaDescription" rows="2" class="w-full rounded border border-stroke px-3 py-2 text-black"></textarea>
                            </label>
                            <label class="block">
                                <span class="mb-2 block font-medium text-black">Meta keywords</span>
                                <textarea v-model="form.seo[page.key][activeLanguage].metaKeywords" rows="2" class="w-full rounded border border-stroke px-3 py-2 text-black" placeholder="keyword 1, keyword 2"></textarea>
                            </label>
                        </div>
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

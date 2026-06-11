<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import {computed, ref} from "vue";
import {useStore} from "vuex";

const store = useStore();
const form = ref(null);
const activeLanguage = ref('hy');
const isSaving = ref(false);

const clone = (value) => JSON.parse(JSON.stringify(value));
const languages = computed(() => form.value?.languages || []);
const activeLanguageLabel = computed(() => languages.value.find((language) => language.code === activeLanguage.value)?.label || activeLanguage.value);

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

const normalizeSettings = () => {
    form.value.settings = form.value.settings || {};
    form.value.settings.contactPhone = form.value.settings.contactPhone || '';
    form.value.settings.contactEmail = form.value.settings.contactEmail || '';
    form.value.settings.contactAddress = form.value.settings.contactAddress || '';
    form.value.settings.contactMapUrl = form.value.settings.contactMapUrl || '';
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

const fetchConfig = async () => {
    const data = await store.dispatch('shopFrontend/fetchConfig');
    form.value = clone(data);
    normalizeSettings();
    normalizeContactTranslations();
    activeLanguage.value = form.value.languages?.[0]?.code || 'hy';
};

const save = async () => {
    isSaving.value = true;

    try {
        await store.dispatch('shopFrontend/updateConfig', form.value);
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Saved',
            message: 'Contact page content was saved.'
        });
    } finally {
        isSaving.value = false;
    }
};

fetchConfig();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Contact Page" :breadcrumb="[{path: '/', title: 'Dashboard'}]"/>

        <div v-if="form" class="space-y-6">
            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-black">Contact Page</h3>
                        <p class="text-sm text-gray-500">Controls only the public contact page content and company contact details.</p>
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

                <div class="mb-5 grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <label class="block">
                        <span class="mb-2 block font-medium text-black">Phone</span>
                        <input v-model="form.settings.contactPhone" class="w-full rounded border border-stroke px-3 py-2 text-black"/>
                    </label>
                    <label class="block">
                        <span class="mb-2 block font-medium text-black">Email</span>
                        <input v-model="form.settings.contactEmail" class="w-full rounded border border-stroke px-3 py-2 text-black"/>
                    </label>
                    <label class="block">
                        <span class="mb-2 block font-medium text-black">Address</span>
                        <input v-model="form.settings.contactAddress" class="w-full rounded border border-stroke px-3 py-2 text-black"/>
                    </label>
                    <label class="block">
                        <span class="mb-2 block font-medium text-black">Map URL</span>
                        <input v-model="form.settings.contactMapUrl" class="w-full rounded border border-stroke px-3 py-2 text-black"/>
                    </label>
                </div>

                <div class="mb-3">
                    <h4 class="font-semibold text-black">Text for {{ activeLanguageLabel }}</h4>
                </div>

                <div class="grid grid-cols-2 gap-3 max-md:grid-cols-1">
                    <label v-for="field in contactTranslationFields" :key="field.key" class="block">
                        <span class="mb-2 block font-medium text-black">{{ field.label }}</span>
                        <textarea
                            v-if="field.type === 'textarea'"
                            v-model="form.translations[activeLanguage][field.key]"
                            rows="2"
                            class="w-full rounded border border-stroke px-3 py-2 text-black"
                        ></textarea>
                        <input v-else v-model="form.translations[activeLanguage][field.key]" class="w-full rounded border border-stroke px-3 py-2 text-black"/>
                    </label>
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

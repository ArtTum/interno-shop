<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";
import {computed, ref} from "vue";
import {useStore} from "vuex";

const store = useStore();
const form = ref(null);
const activeLanguage = ref('hy');
const isSaving = ref(false);

const clone = (value) => JSON.parse(JSON.stringify(value));
const languages = computed(() => form.value?.languages || []);
const activeLanguageLabel = computed(() => languages.value.find((language) => language.code === activeLanguage.value)?.label || activeLanguage.value);

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
    contentHtml: '',
});

const emptySeoRecord = (label = 'Privacy Policy') => ({
    title: label,
    metaTitle: label,
    metaDescription: '',
    metaKeywords: '',
});

const escapeHtml = (value = '') => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const privacyContentToHtml = (content) => {
    const parts = [];

    if (content.title) {
        parts.push(`<h2>${escapeHtml(content.title)}</h2>`);
    }

    if (content.intro) {
        parts.push(`<p>${escapeHtml(content.intro)}</p>`);
    }

    if (Array.isArray(content.checklist) && content.checklist.length) {
        parts.push(`<ul>${content.checklist.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`);
    }

    if (Array.isArray(content.sections)) {
        content.sections.forEach((section) => {
            if (section.title) {
                parts.push(`<h3>${escapeHtml(section.title)}</h3>`);
            }

            if (section.text) {
                parts.push(`<p>${escapeHtml(section.text)}</p>`);
            }
        });
    }

    return parts.join('');
};

const normalizePrivacy = () => {
    form.value.privacy = form.value.privacy || {updatedAt: '', content: {}};
    form.value.privacy.updatedAt = form.value.privacy.updatedAt || '';
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
        form.value.privacy.content[language.code].contentHtml = form.value.privacy.content[language.code].contentHtml
            || privacyContentToHtml(form.value.privacy.content[language.code]);
    });
};

const normalizeSeo = () => {
    form.value.seo = form.value.seo || {};
    form.value.seo['privacy-policy'] = form.value.seo['privacy-policy'] || {};

    languages.value.forEach((language) => {
        form.value.seo['privacy-policy'][language.code] = {
            ...emptySeoRecord(),
            ...(form.value.seo['privacy-policy'][language.code] || {}),
        };
    });
};

const fetchConfig = async () => {
    const data = await store.dispatch('shopFrontend/fetchConfig');
    form.value = clone(data);
    normalizePrivacy();
    normalizeSeo();
    activeLanguage.value = form.value.languages?.[0]?.code || 'hy';
};

const activePrivacy = computed(() => form.value?.privacy?.content?.[activeLanguage.value] || emptyPrivacyContent());
const activeSeo = computed(() => form.value?.seo?.['privacy-policy']?.[activeLanguage.value] || emptySeoRecord());

const save = async () => {
    isSaving.value = true;

    try {
        await store.dispatch('shopFrontend/updateConfig', form.value);
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Saved',
            message: 'Privacy policy page content was saved.'
        });
    } finally {
        isSaving.value = false;
    }
};

fetchConfig();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Privacy Policy" :breadcrumb="[{path: '/', title: 'Dashboard'}]"/>

        <div v-if="form" class="space-y-6">
            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-black">Privacy Policy Page</h3>
                        <p class="text-sm text-gray-500">Controls only the public privacy policy page content and SEO meta.</p>
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

                <div class="mb-6 rounded border border-stroke p-4">
                    <h4 class="mb-3 font-semibold text-black">Title and meta for {{ activeLanguageLabel }}</h4>
                    <div class="grid grid-cols-2 gap-3 max-md:grid-cols-1">
                        <label class="block">
                            <span class="mb-2 block font-medium text-black">Title</span>
                            <input v-model="activeSeo.title" class="w-full rounded border border-stroke px-3 py-2 text-black"/>
                        </label>
                        <label class="block">
                            <span class="mb-2 block font-medium text-black">Meta title</span>
                            <input v-model="activeSeo.metaTitle" class="w-full rounded border border-stroke px-3 py-2 text-black"/>
                        </label>
                        <label class="block">
                            <span class="mb-2 block font-medium text-black">Meta description</span>
                            <textarea v-model="activeSeo.metaDescription" rows="2" class="w-full rounded border border-stroke px-3 py-2 text-black"></textarea>
                        </label>
                        <label class="block">
                            <span class="mb-2 block font-medium text-black">Meta keywords</span>
                            <textarea v-model="activeSeo.metaKeywords" rows="2" class="w-full rounded border border-stroke px-3 py-2 text-black" placeholder="keyword 1, keyword 2"></textarea>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-2.5 block font-medium text-black">Content for {{ activeLanguageLabel }}</label>
                    <CKEditorComponent
                        :model="activePrivacy.contentHtml"
                        @updateValue="(value) => {
                            activePrivacy.contentHtml = value
                        }"
                    />
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

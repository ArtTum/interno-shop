<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import {ref} from "vue";
import {useStore} from "vuex";

const store = useStore();
const form = ref(null);
const isSaving = ref(false);

const clone = (value) => JSON.parse(JSON.stringify(value));
const defaultSocialLinks = [
    {label: 'Instagram', href: 'https://www.instagram.com/', external: true},
    {label: 'Facebook', href: 'https://www.facebook.com/', external: true},
];

const normalizeSocialLinks = () => {
    form.value.settings = form.value.settings || {};
    form.value.settings.socialLinks = Array.isArray(form.value.settings.socialLinks) && form.value.settings.socialLinks.length
        ? form.value.settings.socialLinks
        : clone(defaultSocialLinks);

    form.value.settings.socialLinks.forEach((link) => {
        link.label = link.label || '';
        link.href = link.href || '';
        link.external = link.external ?? true;
    });
};

const fetchConfig = async () => {
    const data = await store.dispatch('shopFrontend/fetchConfig');
    form.value = clone(data);
    normalizeSocialLinks();
};

const moveArrayItem = (list, index, direction) => {
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= list.length) return;

    const [item] = list.splice(index, 1);
    list.splice(targetIndex, 0, item);
};

const addSocialLink = () => {
    form.value.settings.socialLinks.push({
        label: 'Social',
        href: 'https://',
        external: true,
    });
};

const removeSocialLink = (index) => {
    form.value.settings.socialLinks.splice(index, 1);
};

const moveSocialLink = (index, direction) => {
    moveArrayItem(form.value.settings.socialLinks, index, direction);
};

const save = async () => {
    isSaving.value = true;

    try {
        await store.dispatch('shopFrontend/updateConfig', form.value);
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Saved',
            message: 'Social links were saved.'
        });
    } finally {
        isSaving.value = false;
    }
};

fetchConfig();
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Social Links" :breadcrumb="[{path: '/', title: 'Dashboard'}]"/>

        <div v-if="form" class="space-y-6">
            <div class="rounded-sm border border-stroke bg-white p-5 shadow-default">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-black">Footer Social Links</h3>
                        <p class="text-sm text-gray-500">Controls the Facebook and Instagram links shown in the public footer.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="rounded bg-black px-4 py-2 font-medium text-white" @click="addSocialLink">Add social</button>
                        <button :disabled="isSaving" type="button" class="rounded bg-primary px-5 py-2 font-medium text-white disabled:opacity-70" @click="save">
                            {{ isSaving ? 'Saving...' : 'Save changes' }}
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    <div v-for="(link, index) in form.settings.socialLinks" :key="`${link.label}-${index}`" class="grid grid-cols-12 gap-3 rounded border border-stroke p-3 max-lg:grid-cols-1">
                        <label class="col-span-3 block">
                            <span class="mb-2 block font-medium text-black">Label</span>
                            <input v-model="link.label" class="w-full rounded border border-stroke px-3 py-2 text-black" placeholder="Instagram"/>
                        </label>
                        <label class="col-span-5 block">
                            <span class="mb-2 block font-medium text-black">URL</span>
                            <input v-model="link.href" class="w-full rounded border border-stroke px-3 py-2 text-black" placeholder="https://www.instagram.com/..."/>
                        </label>
                        <label class="col-span-1 flex items-center gap-2 pt-8 text-black">
                            <input v-model="link.external" type="checkbox"/>
                            External
                        </label>
                        <div class="col-span-3 flex flex-wrap items-end gap-2">
                            <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="index === 0" @click="moveSocialLink(index, -1)">Up</button>
                            <button type="button" class="rounded border border-stroke px-3 py-2 text-black disabled:opacity-40" :disabled="index === form.settings.socialLinks.length - 1" @click="moveSocialLink(index, 1)">Down</button>
                            <button type="button" class="rounded border border-meta-1 px-3 py-2 text-meta-1" @click="removeSocialLink(index)">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DefaultLayoutComponent>
</template>

<script setup>
import DefaultLayoutComponent from "@layouts/DefaultLayoutComponent.vue";
import BreadcrumbDefault from "@components/global/BreadcrumbDefault.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import Switch from "@components/global/Switch.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomMediaList from "@components/media/CustomMediaList.vue";
import {reactive} from "vue";
import {useRouter} from "vue-router";
import {useStore} from "vuex";

const store = useStore();
const router = useRouter();
const form = reactive({
    media_id: null,
    media: [],
    code: '',
    first_name: '',
    last_name: '',
    phone: '',
    work_region: '',
    work_city: '',
    work_field: '',
    has_whatsapp: false,
    has_viber: false,
    status: true,
    sort_order: 0,
    errors: {}
});

const mediaData = (media) => ({
    id: media.id,
    media_id: media.id,
    path: media.original_path || media.path,
    type: media.type,
    file_type: media.file_type,
});

const insertMedia = (data) => {
    const media = data.media.find((mediaItem) => mediaItem.id);

    if (!media) return;

    form.media_id = media.id;
    form.media = [mediaData(media)];
};

const removeMedia = () => {
    form.media_id = null;
    form.media = [];
};

const submit = async () => {
    try {
        form.errors = {};
        const response = await store.dispatch('shopCraftsman/create', form);
        await router.push(`/shop-craftsmen/update/${response.data.data.id}`);
        store.commit('notification/SET_NOTIFICATION', {visible: true, title: 'Success', message: 'Successfully created'});
    } catch (error) {
        form.errors = error;
    }
};
</script>

<template>
    <DefaultLayoutComponent>
        <BreadcrumbDefault pageTitle="Create craftsman" :breadcrumb="[{path: '/', title: 'Dashboard'}, {path: '/shop-craftsmen', title: 'Craftsmen'}]"/>
        <div class="rounded-sm border border-stroke bg-white shadow-default">
            <div class="border-b border-stroke px-6 py-4"><h3 class="font-medium text-black">Fields</h3></div>
            <form @submit.prevent="submit">
                <div v-if="form.errors && Object.keys(form.errors).length" class="p-6"><AlertError :errors="form.errors"/></div>
                <div class="grid grid-cols-3 gap-6 p-6 max-md:grid-cols-1">
                    <div class="col-span-3 max-md:col-span-1">
                        <CustomMediaList
                            label="Craftsman photo"
                            mode="single"
                            :types="['images']"
                            :images="form.media"
                            @insert="insertMedia"
                            @remove-images="removeMedia"
                        />
                    </div>
                    <CustomInput v-model="form.code" name="code" label="Code *" type="text" placeholder="Enter code" :error="form.errors?.code"/>
                    <CustomInput v-model="form.first_name" name="first_name" label="First name *" type="text" placeholder="Enter first name" :error="form.errors?.first_name"/>
                    <CustomInput v-model="form.last_name" name="last_name" label="Last name" type="text" placeholder="Enter last name" :error="form.errors?.last_name"/>
                    <CustomInput v-model="form.phone" name="phone" label="Phone" type="text" placeholder="Enter phone" :error="form.errors?.phone"/>
                    <CustomInput v-model="form.work_region" name="work_region" label="Main region" type="text" placeholder="Example: Yerevan" :error="form.errors?.work_region"/>
                    <CustomInput v-model="form.work_city" name="work_city" label="City" type="text" placeholder="Example: Arabkir" :error="form.errors?.work_city"/>
                    <CustomInput v-model="form.work_field" name="work_field" label="Work field" type="text" placeholder="Example: Stretch ceilings" :error="form.errors?.work_field"/>
                    <CustomInput v-model="form.sort_order" name="sort_order" label="Sort order" type="number" placeholder="Enter sort order" :error="form.errors?.sort_order"/>
                    <div class="flex items-end pb-2"><Switch @change="(value) => form.has_whatsapp = value" :value="form.has_whatsapp" id="craftsman_whatsapp" label="WhatsApp"/></div>
                    <div class="flex items-end pb-2"><Switch @change="(value) => form.has_viber = value" :value="form.has_viber" id="craftsman_viber" label="Viber"/></div>
                    <div class="flex items-end pb-2"><Switch @change="(value) => form.status = value" :value="form.status" id="craftsman_status" label="Active"/></div>
                </div>
                <hr class="text-gray mt-6.5">
                <div class="flex p-6.5 save-button-fixed"><CustomButton class="ml-auto flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80" type="submit"><font-awesome-icon :icon="['far', 'floppy-disk']"/>Save</CustomButton></div>
            </form>
        </div>
    </DefaultLayoutComponent>
</template>

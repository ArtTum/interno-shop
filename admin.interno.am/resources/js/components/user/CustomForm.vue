<script setup>
import Switch from "@components/global/Switch.vue";
import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";
import CustomSelect from "@components/global/CustomSelect.vue";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import CustomTableSimple from "@components/global/CustomTableSimple.vue";
import PopupWithSlot from "@components/global/PopupWithSlot.vue";

const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    },
    userType: {
        type: String
    },
});

store.dispatch(`${props.userType}/fetchParams`);
const params = computed(() => store.getters[`${props.userType}/getParams`]);
const auth = computed(() => store.getters['auth/getUser']);

const {modelValue} = toRefs(props);
const form = ref(modelValue.value);

const emits = defineEmits([
    'update:modelValue',
    'submit'
]);

watch(modelValue, (newVal) => {
    form.value = newVal;
});

watch(form.value, (newVal) => {
    emits('update:modelValue', newVal);
});

const activeTab = ref(props.userType === 'customer' ? 'transactions' : 'billing_addresses')
const selectTab = (type) => {
    activeTab.value = type
}

const getAdminBaseUrl = computed(() => store.getters['general/getAdminBaseUrl']);

const checkPermission = (can) => {
    if (form.value.id == auth.value.id) {
        return false
    }

    return can;
}

const generalParams = computed(() => store.getters['general/getParams']);

const interestsModal = ref({
    visible: false,
    ids: [],
    interest: null,
})

const showInterests = async (interest) => {
    const res = await store.dispatch(`${props.userType}/getInterests`, {
        user_id: form.value.id,
        interest
    })

    interestsModal.value = {
        visible: true,
        ids: res.ids,
        interest,
    }
}
</script>

<template>
    <form @submit.prevent="emits('submit')">
        <template v-if="interestsModal.visible">
            <PopupWithSlot
                classes="max-w-[80%] w-[80%]"
                @close="interestsModal = {
                    visible: false,
                    ids: [],
                    interest: null,
                }"
            >
                <div class="text-left">
                    <span class="font-bold text-black text-title-xxl">{{ interestsModal.interest.toUpperCase() }}</span>
                </div>
                <div class="gap-3 flex flex-wrap mt-4">
                    <div
                        v-for="(idLink, index) in interestsModal.ids"
                        :key="index"
                    >
                        <a
                            :href="`/catalog/${interestsModal.interest}/update/${idLink.link_id}/-1`"
                            target="_blank"
                        >
                            <div
                                class="inline-block bg-zinc-100 px-4.5 py-2.5 rounded-full border-stroke border cursor-pointer hover-trigger hover:text-primary"
                            >
                                {{ idLink.name }}
                                <font-awesome-icon
                                    class="ml-2"
                                    :icon="['fass', 'up-right-from-square']"
                                />
                            </div>
                        </a>
                    </div>
                </div>
            </PopupWithSlot>
        </template>

        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>

        <div class="grid grid-cols-3 gap-9 mt-5">
            <template v-if="userType === 'user'">
                <div class="flex flex-col px-6.5 py-0">
                    <div v-if="auth.superadmin">
                        <CustomSelect
                            label="Օգտատերերի խումբ *"
                            v-model="form.user_group_id"
                            mode="single"
                            placeholder=""
                            :options="params.userGroups"
                            :disabled="emitAction === 'update' && checkPermission(!auth.user_group.permissions_by_name[`${userType}s`][0].can_edit)"
                            :searchable="true"
                            class="py-2  rounded-lg border-stroke bg-transparent"
                            @update:modelValue="form.errors = validate(form)"
                            :error="form.errors['user_group_id']"
                        />
                    </div>
                </div>
                <div class="flex flex-col px-6.5 py-0 ">

                </div>
                <div class="flex flex-col px-6.5 py-5" >

                </div>

            </template>
            <div class="flex flex-col px-6.5 py-0">
                <CustomInput
                    v-model="form.name"
                    name="name"
                    label="Անուն *"
                    type="text"
                    :disabled="emitAction === 'update' && checkPermission(!auth.user_group.permissions_by_name[`${userType}s`][0].can_edit)"
                    placeholder="Մուտքագրեք անունը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
            <div class="flex flex-col px-6.5 py-0">
                <CustomInput
                    v-model="form.last_name"
                    name="last_name"
                    label="Ազգանուն *"
                    type="text"
                    :disabled="emitAction === 'update' && checkPermission(!auth.user_group.permissions_by_name[`${userType}s`][0].can_edit)"
                    placeholder="Մուտքագրեք ազգանունը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['last_name']"
                />
            </div>
            <div class="flex flex-col px-6.5 py-0">
                <CustomInput
                    v-model="form.email"
                    name="email"
                    label="Էլ․ փոստ *"
                    type="email"
                    :disabled="emitAction === 'update' && checkPermission(!auth.user_group.permissions_by_name[`${userType}s`][0].can_edit)"
                    placeholder="Մուտքագրեք էլ․ փոստը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['email']"
                />
            </div>
        </div>

        <div class="grid grid-cols-3 gap-9 mt-5">
            <div class="flex flex-col px-6.5 py-0">
                <CustomInput
                    v-model="form.password"
                    name="password"
                    :label="emitAction === 'update' ? 'Գաղտնաբառ' : 'Գաղտնաբառ *'"
                    type="password"
                    placeholder="Մուտքագրեք գաղտնաբառը"
                    :disabled="emitAction === 'update' && checkPermission(!auth.user_group.permissions_by_name[`${userType}s`][0].can_edit)"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['password']"
                />
            </div>
            <div class="flex flex-col px-6.5 py-0">
                <CustomInput
                    :disabled="emitAction === 'update' && checkPermission(!auth.user_group.permissions_by_name[`${userType}s`][0].can_edit)"
                    v-model="form.password_confirmation"
                    name="password_confirmation"
                    :label="emitAction === 'update' ? 'Հաստատել գաղտնաբառը' : 'Հաստատել գաղտնաբառը *'"
                    type="password"
                    placeholder="Մուտքագրեք կրկնակի գաղտնաբառը"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['password_confirmation']"
                />
                <br>
                <br>
            </div>
            <div class="flex px-6.5 py-0">
                <div v-if="auth.id != form.id" class="pr-6 py-5 mb-9">
                    <Switch
                        @change="(value) => { form.blocked = value; }"
                        :disabled="emitAction === 'update' && checkPermission(!auth.user_group.permissions_by_name[`${userType}s`][0].can_edit)"
                        :value="form.blocked"
                        id="blocked"
                        label="Արգելված"
                        :colorDanger="true"
                    />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
<!--                    <template v-if="auth.user_group.permissions_by_name[`${userType}s`][0].can_delete">-->
<!--                        <CustomButton-->
<!--                            v-if="emitAction === 'update'"-->
<!--                            @click="store.commit('user/SET_DELETE_MODAL_VALUE', { value: true, id: form.id });"-->
<!--                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"-->
<!--                            type="button"-->
<!--                        >-->
<!--                            <font-awesome-icon :icon="['far', 'trash']"/>-->
<!--                            Ջնջել-->
<!--                        </CustomButton>-->
<!--                    </template>-->

<!--                    <template v-if="emitAction !== 'update' || !checkPermission(!auth.user_group.permissions_by_name[`${userType}s`][0].can_edit)">-->
                        <CustomButton
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="submit"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Պահպանել
                        </CustomButton>
<!--                    </template>-->
                </div>
            </div>
        </div>
    </form>
</template>

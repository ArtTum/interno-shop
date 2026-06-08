<script setup>
import DefaultAuthCard from '@/components/auth/DefaultAuthCard.vue'
import CustomInput from '@components/global/CustomInput.vue'
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

import {ref} from 'vue';
import {useStore} from "vuex";
import {useRouter} from "vue-router";
import {validate} from '@validation/customValidation.js';

const store = useStore();
const router = useRouter()

const form = ref({
    vendor_key: 'crm_doctor911',
    vendor_keyRules: ['required'],
    email: '',
    emailRules: ['required', 'email'],
    password: '',
    passwordRules: ['required', 'minLength:8'],
    errors: {}
})

const submit = async () => {
    const errors = validate(form.value);
    if (Object.keys(errors).length > 0) {
        form.value.errors = errors;
        return false;
    }

    try {
        await store.dispatch('auth/login', form.value);
        await router.push('/');
    } catch (error) {
        form.value.errors = error;
    }
};
</script>

<template>
    <DefaultAuthCard
        subtitle="Start for free"
        title="Sign In"
    >
        <form @submit.prevent="submit">
            <CustomInput
                style="display:none"
                v-model="form.vendor_key"
                name="vendor_key"
                label="Vendor key"
                type="text"
                placeholder="Enter your vendor key"
                :icon="true"
                @keyup="form.errors = validate(form)"
                :error="form.errors['vendor_key']"
            >
                <font-awesome-icon :icon="['far', 'key']" class="size-6"/>
            </CustomInput>
            <CustomInput
                v-model="form.email"
                name="email"
                label="Email"
                type="email"
                placeholder="Enter your email"
                :icon="true"
                @keyup="form.errors = validate(form)"
                :error="form.errors['email']"
            >
                <font-awesome-icon :icon="['far', 'envelope']" class="size-6"/>
            </CustomInput>

            <CustomInput
                v-model="form.password"
                name="password"
                label="Password"
                type="password"
                placeholder="Enter your password"
                :icon="true"
                @keyup="form.errors = validate(form)"
                :error="form.errors['password']"
            >
                <font-awesome-icon :icon="['far', 'lock']" class="size-6"/>
            </CustomInput>

            <div
                v-if="Object.keys(form.errors).length > 0 && form.errors.general"
                class="mb-5 mt-6"
            >
                <AlertError :errors="form.errors.general"/>
            </div>

            <div class="mb-5 mt-6">
                <CustomButton
                    class="flex w-full justify-center rounded bg-primary p-3 font-medium text-gray hover:bg-opacity-90"
                    type="submit"
                >
                    Sign In
                </CustomButton>
            </div>
        </form>
    </DefaultAuthCard>
</template>

<style lang="scss">
@import '@assets/scss/login';
</style>

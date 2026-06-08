<script setup>

import CustomTableSecond from "@components/global/CustomTableSecond.vue";
import CustomButton from "@components/global/CustomButton.vue";

import {useStore} from "vuex";
import {computed, ref} from "vue";
import {validate} from "@validation/customValidation.js";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const store = useStore()
const props = defineProps({
  orderEmails: {
    type: Array
  },
  orderId: {
    type: Number
  },
});

const newEmail = ref(null);

const emits = defineEmits([
  'fetch',
]);

const saveNewEmail = async () => {
  const errors = validate(newEmail.value);
  if (Object.keys(errors).length > 0) {
    newEmail.value.errors = errors;
    return false;
  }

  try {
    await store.dispatch('order/sendCustomerEmail', newEmail.value);
    newEmail.value = null;
    emits('fetch');
    store.commit('notification/SET_NOTIFICATION', {
      visible: true,
      title: 'Success',
      message: 'Successfully added'
    });
  } catch (reqErrors) {

  }
};

const auth = computed(() => store.getters['auth/getUser']);

</script>

<template>
  <CustomTableSecond
      @addItem="newEmail = {
        order_id: orderId,
        message: null,
        messageRules: ['required'],
        errors: {}
      }"
      :button-info="auth.user_group.permissions_by_name.orders[0].can_add ? {
            title: 'Send email',
            emitName: 'add-item',
            icon: 'plus',
            classes: 'bg-meta-3',
            disabled: !!newEmail
        } : null"
      title="Email to customer"
  >
    <template #header>

    </template>

    <template #content>
      <template v-if="newEmail">

        <div class="flex flex-col border-t border-stroke p-6 max-sm:px-2">
          <div class="col-span-8 flex flex-col">
              <label class="mb-2.5 block font-medium text-black">Message</label>
              <CKEditorComponent
                  :model="newEmail.message"
                  @updateValue="(value) => {
                    newEmail.message = value
                  }"
              />
          </div>
          <div class="ml-auto">
            <CustomButton
                @click="saveNewEmail"
                class="w-fit flex items-center gap-2 mt-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                type="button"
            >
              <font-awesome-icon :icon="['far', 'floppy-disk']"/>
              Send email
            </CustomButton>
          </div>
        </div>
      </template>
        <div class="overflow-x-auto">
            <div class="min-w-[700px]">
                <!-- Table Header -->
                <div
                    class="grid grid-cols-11 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                >
                    <div class="col-span-4 flex items-center">
                        <p class="font-medium">Created by</p>
                    </div>
                    <div class="col-span-7 flex items-center">
                        <p class="font-medium">Message</p>
                    </div>
                </div>

                <!-- Table Rows -->
                <template
                    v-for="(email, index) in orderEmails"
                    :key="index"
                >
                    <div
                        class="grid grid-cols-11 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                    >
                        <div class="col-span-4 sm:flex flex-col">
                            <p class="text-sm font-medium text-black">
                                {{ email.user.name }}  {{ email.user.last_name }} <br>
                                {{ email.created_at }}
                            </p>
                        </div>
                        <div class="col-span-7 sm:flex flex-col">
                            <p class="text-sm font-medium text-black" v-html="email.message"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </template>
  </CustomTableSecond>
</template>

<style scoped>

</style>

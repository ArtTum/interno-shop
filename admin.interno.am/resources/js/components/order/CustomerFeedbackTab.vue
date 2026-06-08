<script setup>

import CustomTableSecond from "@components/global/CustomTableSecond.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import CustomButton from "@components/global/CustomButton.vue";

import {useStore} from "vuex";
import {computed, ref, watch} from "vue";
import {validate} from "@validation/customValidation.js";
import CustomSelect from "@components/global/CustomSelect.vue";

const store = useStore()
const props = defineProps({
    orderFeedbacks: {
        type: Array
    },
    orderId: {
        type: Number
    },
    feedbackTypes: {
        type: Array
    },
    employees: {
        type: Array
    },
    feedbackTypesForShowing: {
        type: Object
    },
});

const newFeedback = ref(null);

const emits = defineEmits([
    'fetch',
]);

const showEmployee = ref(false);

const saveNewFeedback = async () => {
    const errors = validate(newFeedback.value);
    if (Object.keys(errors).length > 0) {
        newFeedback.value.errors = errors;
        return false;
    }

    try {
        await store.dispatch('order/addFeedback', newFeedback.value);
        newFeedback.value = null;
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

watch(() => newFeedback.value?.type, (val) => {
    if (val) {
        let feedback = props.feedbackTypesForShowing[val];

        if (feedback.includes("KF")) {
            showEmployee.value = true;
        } else {
            newFeedback.value.employee_id = null;
            showEmployee.value = false;
        }
    } else {
        showEmployee.value = false;
    }
});
</script>

<template>
    <CustomTableSecond
        @addItem="newFeedback = {
            order_id: orderId,
            message: null,
            type: 0,
            employee_id: null,
            messageRules: ['required'],
            typeRules: ['required'],
            user_idRules: [],
            errors: {}
        }"
        :button-info="auth.user_group.permissions_by_name.orders[0].can_add ? {
            title: 'Add feedback',
            emitName: 'add-item',
            icon: 'plus',
            classes: 'bg-meta-3',
            disabled: !!newFeedback
        } : null"
        title="Order feedbacks"
    >
        <template #header>

        </template>

        <template #content>
            <template v-if="newFeedback">
                <div class="flex flex-col gap-4 p-6 border-t border-stroke max-sm:px-2">
                    <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                        <div class="flex flex-col">
                            <CustomSelect
                                v-model="newFeedback.type"
                                mode="single"
                                label="Mistake type *"
                                placeholder="Mistake type *"
                                :options="feedbackTypes"
                                :searchable="true"
                                :canClear="false"
                                @keyup="newFeedback.errors = validate(newFeedback)"
                                :error="newFeedback.errors['type']"
                            />
                        </div>
                        <div class=" flex flex-col" v-if="showEmployee">
                            <CustomSelect
                                v-model="newFeedback.employee_id"
                                mode="single"
                                label="Employee *"
                                placeholder="Employee *"
                                :options="employees"
                                :searchable="true"
                                :canClear="false"
                                @keyup="newFeedback.errors = validate(newFeedback)"
                                :error="newFeedback.errors['employee_id']"
                            />
                        </div>
                    </div>
                    <div class=" flex flex-col">
                        <CustomTextarea
                            :disabled="!newFeedback.type"
                            label="Feedback *"
                            name="feedback"
                            :rows="6"
                            v-model="newFeedback.message"
                            placeholder="Type feedback..."
                            @keyup="newFeedback.errors = validate(newFeedback)"
                            :error="newFeedback.errors['message']"
                        />
                    </div>
                    <div class="ml-auto">
                        <CustomButton
                            @click="saveNewFeedback"
                            class="max-w-[100px] flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </div>
                </div>
            </template>
            <div class="overflow-x-auto">
                <div class="min-w-[960px]">
                    <!-- Table Header -->
                    <div
                        class="grid grid-cols-11 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                    >
                        <div class="col-span-2 flex items-center">
                            <p class="font-medium">Created by</p>
                        </div>
                        <div class="col-span-2 flex items-center">
                            <p class="font-medium">Type</p>
                        </div>
                        <div class="col-span-2 flex items-center">
                            <p class="font-medium">Employee</p>
                        </div>
                        <div class="col-span-5 flex items-center">
                            <p class="font-medium">Feedback</p>
                        </div>
                    </div>

                    <!-- Table Rows -->
                    <template
                        v-for="(feedback, index) in orderFeedbacks"
                        :key="index"
                    >
                        <div
                            class="grid grid-cols-11 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                        >
                            <div class="col-span-2 flex flex-col">
                                <template v-if="feedback.employee_id">
                                    <a
                                        :href="'/users/list/update/' + feedback.employee_id"
                                        target="_blank"
                                    >
                  <span class="text-sm font-medium text-black hover:text-primary">{{
                          feedback.log_by
                      }}</span>
                                        <font-awesome-icon
                                            class="text-black-2 ml-2 hover:text-primary text-sm"
                                            :icon="['fass', 'up-right-from-square']"
                                        />
                                    </a>
                                </template>
                                <template v-else>
                                    <p class="text-sm font-medium text-black">{{ feedback.log_by }}</p>
                                </template>
                                <p class="text-sm font-medium">{{ feedback.created_at }}</p>
                            </div>
                            <div class="col-span-2 flex flex-col">
                                <p class="text-sm font-medium text-black">{{
                                        feedbackTypesForShowing[feedback.type]
                                    }}</p>
                            </div>
                            <div class="col-span-2 flex flex-col">
                                <p class="text-sm font-medium text-black">{{ feedback.employee_name }}
                                    {{ feedback.employee_last_name }}</p>
                            </div>
                            <div class="col-span-5 flex flex-col">
                                <p class="text-sm font-medium text-black" v-html="feedback.message"></p>
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

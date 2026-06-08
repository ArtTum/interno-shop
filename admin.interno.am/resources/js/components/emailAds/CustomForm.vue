<script setup>

import CustomInput from "@components/global/CustomInput.vue";
import CustomButton from "@components/global/CustomButton.vue";
import AlertError from "@components/global/Alerts/AlertError.vue";

import {computed, ref, toRefs, watch} from "vue";
import {useStore} from "vuex";
import {validate} from "@validation/customValidation.js";
import CustomSelect from "@components/global/CustomSelect.vue";
import CustomDatePicker from "@components/global/CustomDatePicker.vue";
import ClipLoader from "vue-spinner/src/ClipLoader.vue";
import CKEditorComponent from "@components/global/CKEditorComponent.vue";

const store = useStore()
const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    emitAction: {
        type: String
    },
});

store.dispatch(`emailAds/fetchParams`);
const params = computed(() => store.getters[`emailAds/getParams`]);
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

const activeTab = ref('content')
const tabsRoutes = [
    {key: 'content', title: 'Content', icon: ['fas', 'arrows-to-circle']},
    {key: 'recipients', title: 'Recipients', icon: ['fas', 'users-line']},
    {key: 'schedule', title: 'Schedule', icon: ['far', 'timer']},
    {key: 'results', title: 'Results', icon: ['far', 'square-poll-vertical']},
];

</script>

<template>
    <form @submit.prevent="emits('submit')">
        <div
            v-if="Object.keys(form.errors).length > 0 && form.errors.general"
            class="grid grid-cols-1 gap-9 p-6.5"
        >
            <AlertError :errors="form.errors.general"/>
        </div>
        <div class="grid grid-cols-1 gap-9" v-if="emitAction === 'update'">
            <div class="flex pt-6.5 px-6.5 items-center">
                 <span
                     class="inline-flex rounded-full bg-opacity-10 py-2 px-5 text-sm font-medium items-center"
                     :class="{
                              'bg-warning text-warning': form.status === 1 || form.status === 4,
                              'bg-primary text-white bg-opacity-80': form.status === 0,
                             'bg-success text-success': form.status === 2,
                             'bg-danger text-danger': form.status === 3,
                       }"
                 >
                           <template v-if="form.status === 0">
                                 Not processed
                           </template>
                            <template v-else-if="form.status === 1">
                                 In progress
                                <ClipLoader class="text-left ml-2" color="#3C50E0" size="20px"/>
                           </template>
                            <template v-else-if="form.status === 4">
                                Waiting to limit
                                <ClipLoader class="text-left ml-2" color="#3C50E0" size="20px"/>
                           </template>
                            <template v-else-if="form.status === 3">
                                Collecting customers in progress
                                <ClipLoader class="text-left ml-2" color="#3C50E0" size="20px"/>
                           </template>
                          <template v-else>
                                    Finished
                          </template>
                 </span>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-9 mt-5">
            <div class="flex flex-col p-2.5 px-6.5">
                <CustomSelect
                    v-model="form.language_id"
                    label="Language *"
                    :options="params.languages"
                    class="py-2 rounded-lg  border-stroke bg-transparent"
                    :searchable="true"
                    :canClear="false"
                    :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                    :close-on-select="true"
                    placeholder="Select language"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['language_id']"
                />
            </div>
            <div class="flex flex-col p-2.5 px-6.5">
                <CustomSelect
                    v-model="form.campaign_id"
                    label="Campaign *"
                    :options="params.campaigns"
                    class="py-2 rounded-lg  border-stroke bg-transparent"
                    :searchable="true"
                    :canClear="false"
                    :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                    :close-on-select="true"
                    placeholder="Select campaign"
                    @update:modelValue="form.errors = validate(form)"
                    :error="form.errors['campaign_id']"
                />
            </div>
            <div class="flex flex-col p-2.5 px-6.5">
                <CustomInput
                    v-model="form.name"
                    name="name"
                    label="Name *"
                    type="text"
                    :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                    placeholder="Enter name"
                    @keyup="form.errors = validate(form)"
                    :error="form.errors['name']"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-9">
            <div class="mb-14 w-full p-7.5 pt-0">
                <div class="mb-6 flex flex-wrap gap-10 border-b border-stroke">
                    <template
                        :key="key"
                        v-for="(tabRoute, key) in tabsRoutes"
                    >
                        <router-link
                            to=""
                            @click="activeTab = tabRoute.key"
                            v-if="tabRoute.key !== 'results' || emitAction === 'update'"
                            :class="{
                                    'text-primary border-primary': activeTab === tabRoute.key,
                                    'border-transparent': activeTab !== tabRoute.key
                                }"
                            class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base px-2"
                        >
                            <font-awesome-icon :icon="tabRoute.icon"/>
                            {{ tabRoute.title }}
                        </router-link>
                    </template>
                </div>

                <div v-if="activeTab === 'recipients'">
                    <div class="grid grid-cols-2 gap-9 mt-5">
                        <div class="flex flex-col p-2.5">
                            <CustomSelect
                                v-model="form.customer_segment_ids"
                                mode="tags"
                                label="Select customer segments for sending *"
                                :options="params.customerSegments"
                                class="py-2 rounded-lg  border-stroke bg-transparent"
                                :searchable="true"
                                :canClear="false"
                                :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                :close-on-select="false"
                                placeholder="Select customer segments for sending"
                                @update:modelValue="form.errors = validate(form)"
                                :error="form.errors['customer_segment_ids']"
                            />
                        </div>
                        <div class="flex flex-col p-2.5">
                            <CustomSelect
                                v-model="form.excluded_customer_segment_ids"
                                mode="tags"
                                label="Select customer segments for excluding"
                                class="py-2 rounded-lg  border-stroke bg-transparent"
                                :options="params.customerSegments"
                                :searchable="true"
                                :canClear="false"
                                :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                :close-on-select="false"
                                placeholder="Select customer segments for excluding"
                            />
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'content'">
                    <div class="grid grid-cols-2 gap-9 mt-5">
                        <div class="flex flex-col p-2.5">
                            <CustomInput
                                v-model="form.from_name"
                                name="from_name"
                                label="From name *"
                                type="text"
                                :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                placeholder="Enter name"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['from_name']"
                            />
                        </div>
                        <div class="flex flex-col p-2.5">
                            <CustomInput
                                v-model="form.subject"
                                name="subject"
                                label="Email subject *"
                                type="text"
                                :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                placeholder="Enter subject"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['subject']"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-9">
                        <div class="flex flex-col p-2.5">
                            <CustomInput
                                v-model="form.from_email"
                                name="from_email"
                                label="From email *"
                                type="text"
                                :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                placeholder="Enter email"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['from_email']"
                            />
                        </div>
                        <div class="flex flex-col p-2.5">
                            <CustomInput
                                v-model="form.reply_to_email"
                                name="reply_to_email"
                                label="Reply to email *"
                                type="text"
                                :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                placeholder="Enter email"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['reply_to_email']"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-9">
                            <div class="flex flex-col p-6.5 pb-0 justify-center">
                                <div class="mb-5">
                                    <label class="mb-2.5 mt-2.5 block font-medium text-black">Body *</label>
                                    <CKEditorComponent
                                        :model="form.body"
                                        @updateValue="(value) => {
                        form.body = value
                    }"
                                        :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                        @keyup="form.errors = validate(form)"
                                        :error="form.errors['body']"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="col-span-3">
                            <div class="flex flex-col p-6.5 pb-0 w-fit">
                                <ul>
                                    <li class="border-b border-stroke font-semibold text-black">Content variables</li>
                                    <li class="mt-4">{unsubscribe_link}</li>
                                    <li>{query_param_for_conversion_tracking}</li>
                                    <li>{customer_name}</li>
                                    <li>{customer_last_name}</li>
                                    <li>{balance}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'schedule'">
                    <div class="grid grid-cols-2 gap-9 mt-5">
                        <div class="flex flex-col p-2.5">
                            <div class="flex">
                                <div class="w-full">
                                    <CustomDatePicker
                                        :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                        v-model="form.schedule_date"
                                        placeholder="yyyy/mm/dd"
                                        label="Date for sending *"
                                        format="Y-m-d"
                                        @update:modelValue="form.errors = validate(form)"
                                        :error="form.errors['schedule_date']"
                                    />
                                </div>
                                <div class="max-w-[8.5rem] mx-auto ml-3">
                                    <template v-if="form.schedule_date">
                                        <label for="time" class="mb-2.5 block font-medium text-black">Select
                                            time:</label>
                                        <div class="flex">
                                            <input
                                                type="time"
                                                id="time"
                                                :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                                class="py-4 h-full w-full rounded border-[1.5px] border-stroke bg-transparent px-5 font-normal outline-none transition focus:border-primary active:border-primary"
                                                min="00:00"
                                                max="23:59"
                                                v-model="form.schedule_time"
                                            >
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col p-2.5">
                            <CustomInput
                                v-model="form.daily_limit"
                                name="daily_limit"
                                label="Daily limit *"
                                type="number"
                                :disabled="(emitAction === 'update' && !auth.user_group.permissions_by_name.email_ads[0].can_edit) || !!form.status"
                                placeholder="Enter limit"
                                @keyup="form.errors = validate(form)"
                                :error="form.errors['daily_limit']"
                            />
                        </div>
                    </div>
                </div>
                <div v-else-if="activeTab === 'results'">
                    <div>
                        <div
                            class="col-span-12 rounded-sm border border-stroke bg-white py-7.5 shadow-default"
                        >
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-8 xl:gap-0">
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.recipients }}
                                        </h4>
                                        <p class="text-sm font-medium">Recipients</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.bounced_count }}
                                        </h4>
                                        <p class="text-sm font-medium">Bounces</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.delivered_count }}
                                        </h4>
                                        <p class="text-sm font-medium">Delivered</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.opened_count }}
                                        </h4>
                                        <p class="text-sm font-medium">Opened</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.clicked_count }}
                                        </h4>
                                        <p class="text-sm font-medium">Clicks / Visits</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black dark:text-white md:text-title-lg">
                                            {{ form.full_statistic.ordered_count }}
                                        </h4>
                                        <p class="text-sm font-medium">Conversions</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black dark:text-white md:text-title-lg">
                                            {{ form.base_currency_symbol }}{{ parseFloat(form.full_statistic.revenue).toFixed(2) }}
                                        </h4>
                                        <p class="text-sm font-medium">Revenue</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b-0 border-stroke pb-0 xl:border-b-0 xl:border-r-0 xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.unsubscribed_count }}
                                        </h4>
                                        <p class="text-sm font-medium">Unsubscribes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-span-12 rounded-sm border border-stroke bg-white py-7.5 shadow-default mt-1"
                        >
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-8 xl:gap-0">
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.segment_size }}%
                                        </h4>
                                        <p class="text-sm font-medium">Size of recipients from all</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.bounce_rate }}%
                                        </h4>
                                        <p class="text-sm font-medium">Bounce rate</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.delivery_rate }}%
                                        </h4>
                                        <p class="text-sm font-medium">Delivery rate</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.open_rate }}%
                                        </h4>
                                        <p class="text-sm font-medium">Open rate</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.click_rate }}%
                                        </h4>
                                        <p class="text-sm font-medium">Click-rate (CTR)</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black dark:text-white md:text-title-lg">
                                            {{ form.full_statistic.conversion_rate }}%
                                        </h4>
                                        <p class="text-sm font-medium">Conversion rate</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b border-stroke pb-5 xl:border-b-0 xl:border-r xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black dark:text-white md:text-title-lg">
                                            {{ form.base_currency_symbol }}{{ form.full_statistic.avg_revenue }}
                                        </h4>
                                        <p class="text-sm font-medium">AVG Revenue</p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 text-center border-b-0 border-stroke pb-0 xl:border-b-0 xl:border-r-0 xl:pb-0"
                                >
                                    <div>
                                        <h4 class="mb-0.5 text-xl font-bold text-black md:text-title-lg">
                                            {{ form.full_statistic.unsubscribe_rate }}%
                                        </h4>
                                        <p class="text-sm font-medium">Unsubscribe rate</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-9">
            <div class="flex flex-col p-6.5 save-button-fixed">
                <div class="flex ml-auto gap-5">
                    <template v-if="auth.user_group.permissions_by_name.email_ads[0].can_delete">
                        <CustomButton
                            v-if="emitAction === 'update'"
                            @click="store.commit('user/SET_DELETE_MODAL_VALUE', {
                                    value: true,
                                    id: form.id
                                });"
                            class="flex items-center gap-2 rounded border-meta-1 bg-meta-1 py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'trash']"/>
                            Delete
                        </CustomButton>
                    </template>

                    <template
                        v-if="(emitAction !== 'update' || auth.user_group.permissions_by_name.email_ads[0].can_edit) && !form.status">
                        <CustomButton
                            class="flex items-center gap-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="submit"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </template>
                </div>

            </div>
        </div>
    </form>
</template>

<style scoped>

</style>

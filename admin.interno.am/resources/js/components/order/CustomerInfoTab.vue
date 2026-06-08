<script setup>
import {computed} from "vue";

import {formatPrice} from "@/utils/formatPrice.js";

const props = defineProps(['form']);

import {useStore} from "vuex";
const store = useStore();

const auth = computed(() => store.getters['auth/getUser']);
</script>

<template>
    <div>
        <font-awesome-icon :icon="['fass', 'hashtag']"/>
        <span class="text-base ml-1">Customer IP:</span>
        <span class="text-base text-black-2 ml-2">{{ form.user_ip }}</span>
    </div>
    <template v-if="form.user">
        <hr class="text-gray my-3">
        <div>
            <font-awesome-icon :icon="['fass', 'hashtag']"/>
            <span class="text-base ml-1">User ID:</span>
            <template v-if="auth.user_group.permissions_by_name.users[0].can_view">
                <a
                    class="hover-trigger hover:text-primary"
                    :href="'/users/list/update/' + form.user_id"
                    target="_blank"
                >
                    <span class="text-base text-black-2 ml-2">{{ form.user_id }}</span>
                    <font-awesome-icon
                        class="text-black-2 ml-2"
                        :icon="['fass', 'up-right-from-square']"
                    />
                </a>
            </template>
        </div>
        <hr class="text-gray my-3">
        <div>
            <span class="text-base ml-1">Orders count:</span>
            <a
                class="hover-trigger hover:text-primary"
                :href="'/orders/?only_actuals=-1&user_id=' + form.user_id"
                target="_blank"
            >
                <span class="text-base text-black-2 ml-2">{{ form.user.orders_count }}</span>
                <font-awesome-icon
                    class="text-black-2 ml-2"
                    :icon="['fass', 'up-right-from-square']"
                />
            </a>
        </div>
        <div>
            <span class="text-base ml-1">Order amounts sum:</span>
            <span class="text-base text-black-2 ml-2">
                {{ formatPrice(form.user.orders_sum_orderstotal_price_order_currency_rate, form.baseCurrencyCode, true) }}
            </span>
        </div>
        <div>
            <span class="text-base ml-1">Order amounts avg:</span>
            <template v-if="form.user.orders_avg_total_price_order_currency_rate">
                <span class="text-base text-black-2 ml-2">
                    {{ formatPrice(form.user.orders_avg_total_price_order_currency_rate, form.baseCurrencyCode, true) }}
                </span>
            </template>
            <template>
                <span class="text-base text-black-2 ml-2">0</span>
            </template>
        </div>
    </template>
    <template v-else>
        <div>
            <span class="text-base ml-1">Guest</span>
        </div>
    </template>
</template>

<style scoped>

</style>

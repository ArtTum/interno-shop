<script setup>

import CustomButton from "@components/global/CustomButton.vue";
import {useStore} from "vuex";
const store = useStore();

const props = defineProps({
    dynamicCclass: {
        type: String,
        default: ''
    },
    text: String|Number,
    disabled: {
        type: Boolean,
        default: false
    }
})

const copy = (text) => {
    const input = document.createElement('input');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);


    store.commit('notification/SET_NOTIFICATION', {
        visible: true,
        title: 'Success',
        message: 'Copied to clipboard!'
    });
};

</script>

<template>
    <customButton type="button" @click="copy(props.text)">
        <font-awesome-icon :icon="['fas', 'copy']" class="size-5" :class="dynamicCclass"/>
    </customButton>
</template>

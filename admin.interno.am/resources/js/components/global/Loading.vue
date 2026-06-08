<script setup>
import ClipLoader from 'vue-spinner/src/ClipLoader.vue'

import {computed} from "vue";
import {useStore} from "vuex";
const store = useStore();

const status = computed(() => store.getters['loading/getLoadingStatus']);

</script>

<template>
    <div
        v-if="status"
        class="loading-overlay"
    >
        <ClipLoader
            color="#3C50E0"
            size="80px"
        />
    </div>
</template>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgb(255 255 255 / 80%);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999999;
}
</style>

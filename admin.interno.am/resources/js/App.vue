<script setup>
import Loading from '@/components/global/Loading.vue'
import {computed, watch} from "vue";
import {useStore} from "vuex";

const store = useStore()

const isForbidden = computed(() => store.getters['auth/getForbidden']);

watch(() => isForbidden.value, (val) => {
    if (val && window.location.href && !window.location.href.includes('/forbidden')) {
        window.location.href = '/forbidden'
    }
});

const fetchParams = () => {
    store.dispatch('general/fetchParams', {
        dontNeedLoading: true
    });
}

watch(
    () => store.getters['auth/getUser'],
    (newValue) => {
        if (newValue) {
            fetchParams();
        }
    },
    {immediate: true}
);

document.addEventListener('wheel', function (e) {
    const active = document.activeElement;
    if (active && active.type === 'number') {
        active.blur();  // optionally blur to completely block changes
        e.preventDefault();
    }
}, { passive: false });
</script>

<template>
    <Loading/>
    <router-view></router-view>
</template>

<style scoped>

</style>

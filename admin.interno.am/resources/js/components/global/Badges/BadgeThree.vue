<script setup>

const props = defineProps({
    badgeItem: {
        type: Object
    }
});

</script>

<template>
    <div>
        <div class="flex flex-wrap items-center gap-4.5">
            <button
                class="inline-flex rounded-full py-1 px-3 text-sm font-medium hover:bg-opacity-90"
                :class="
            badgeItem.name === 'Light' || badgeItem.name === 'Warning'
              ? 'text-[#212B36]'
              : 'text-white'
          "
                :style="{ backgroundColor: badgeItem.color }"
            >
                {{ badgeItem.name }}
            </button>
        </div>
    </div>
</template>

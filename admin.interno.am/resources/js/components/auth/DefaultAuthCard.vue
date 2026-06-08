<script setup>
const props = defineProps(['subtitle', 'title', 'customClasses'])
</script>

<template>
    <div
        class="vh-100 rounded-sm border border-stroke bg-white shadow-default"
    >
        <div
            class="flex flex-wrap items-center vh-100"
        >
            <div class="hidden w-full xl:block xl:w-1/2">
                <div class="py-17.5 px-26 text-center">
                    <router-link class="mb-5.5 inline-block" to="/">
                        <img
                            src="@assets/images/1logo.png"
                            alt="Logo"
                        />
                    </router-link>

                    <p class="font-medium 2xl:px-20">
<!--                        Lorem ipsum dolor sit amet, consectetur adipiscing elit suspendisse.-->
                    </p>

                    <span class="mt-15 inline-block">
            <img
                src="@/assets/images/illustration/illustration-03.svg"
                alt="illustration"
            />
          </span>
                </div>
            </div>
            <div class="w-full border-stroke xl:w-1/2 xl:border-l-2">
                <div class="w-full p-4 sm:p-12.5 xl:p-17.5">
                    <h2 class="mb-9 text-2xl font-bold text-black sm:text-title-xl2">
                        {{ props.title }}
                    </h2>

                    <slot></slot>
                </div>
            </div>
        </div>
    </div>
    <!-- ====== Forms Section End -->
</template>

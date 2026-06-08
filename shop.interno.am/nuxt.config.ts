// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  css: ['~/assets/css/main.css'],
  devtools: { enabled: true },
  runtimeConfig: {
    public: {
      frontApiBase: process.env.NUXT_PUBLIC_FRONT_API_BASE || 'http://admin.interno.am'
    }
  },
  devServer: {
    port: 2000
  }
})

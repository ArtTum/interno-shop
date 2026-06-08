<script setup lang="ts">
const locale = useCookie('interino-locale', {
  default: () => 'hy',
  sameSite: 'lax'
})

await navigateTo(`/${locale.value || 'hy'}`, { replace: true })
</script>

// vite.config.js
import { defineConfig } from "file:///D:/xampp/htdocs/domains/crm.doctor911.am/node_modules/vite/dist/node/index.js";
import laravel from "file:///D:/xampp/htdocs/domains/crm.doctor911.am/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///D:/xampp/htdocs/domains/crm.doctor911.am/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import path from "path";
import tailwindcss from "file:///D:/xampp/htdocs/domains/crm.doctor911.am/node_modules/tailwindcss/lib/index.js";
var __vite_injected_original_dirname = "D:\\xampp\\htdocs\\domains\\crm.doctor911.am";
var vite_config_default = defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: true
    }),
    vue()
  ],
  css: {
    postcss: {
      plugins: [tailwindcss()]
    }
  },
  build: {
    target: "esnext",
    minify: "esbuild",
    sourcemap: true
  },
  cacheDir: "./node_modules/.vite_cache",
  resolve: {
    alias: {
      "@": path.resolve(__vite_injected_original_dirname, "resources/js"),
      "@components": path.resolve(__vite_injected_original_dirname, "resources/js/components"),
      "@pages": path.resolve(__vite_injected_original_dirname, "resources/js/pages"),
      "@store": path.resolve(__vite_injected_original_dirname, "resources/js/store"),
      "@router": path.resolve(__vite_injected_original_dirname, "resources/js/router"),
      "@layouts": path.resolve(__vite_injected_original_dirname, "resources/js/layouts"),
      "@assets": path.resolve(__vite_injected_original_dirname, "resources/js/assets"),
      "@validation": path.resolve(__vite_injected_original_dirname, "resources/js/validation")
    }
  },
  optimizeDeps: {
    include: ["vue", "vue-index"]
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJEOlxcXFx4YW1wcFxcXFxodGRvY3NcXFxcZG9tYWluc1xcXFxjcm0uZG9jdG9yOTExLmFtXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCJEOlxcXFx4YW1wcFxcXFxodGRvY3NcXFxcZG9tYWluc1xcXFxjcm0uZG9jdG9yOTExLmFtXFxcXHZpdGUuY29uZmlnLmpzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy9EOi94YW1wcC9odGRvY3MvZG9tYWlucy9jcm0uZG9jdG9yOTExLmFtL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XG5pbXBvcnQgbGFyYXZlbCBmcm9tICdsYXJhdmVsLXZpdGUtcGx1Z2luJztcbmltcG9ydCB2dWUgZnJvbSAnQHZpdGVqcy9wbHVnaW4tdnVlJztcbmltcG9ydCBwYXRoIGZyb20gJ3BhdGgnO1xuaW1wb3J0IHRhaWx3aW5kY3NzIGZyb20gJ3RhaWx3aW5kY3NzJ1xuXG5leHBvcnQgZGVmYXVsdCBkZWZpbmVDb25maWcoe1xuICAgIHBsdWdpbnM6IFtcbiAgICAgICAgbGFyYXZlbCh7XG4gICAgICAgICAgICBpbnB1dDogWydyZXNvdXJjZXMvY3NzL2FwcC5jc3MnLCAncmVzb3VyY2VzL2pzL2FwcC5qcyddLFxuICAgICAgICAgICAgcmVmcmVzaDogdHJ1ZSxcbiAgICAgICAgfSksXG4gICAgICAgIHZ1ZSgpXG4gICAgXSxcbiAgICBjc3M6IHtcbiAgICAgICAgcG9zdGNzczoge1xuICAgICAgICAgICAgcGx1Z2luczogW3RhaWx3aW5kY3NzKCldXG4gICAgICAgIH1cbiAgICB9LFxuICAgIGJ1aWxkOiB7XG4gICAgICAgIHRhcmdldDogJ2VzbmV4dCcsXG4gICAgICAgIG1pbmlmeTogJ2VzYnVpbGQnLFxuICAgICAgICBzb3VyY2VtYXA6IHRydWVcbiAgICB9LFxuICAgIGNhY2hlRGlyOiAnLi9ub2RlX21vZHVsZXMvLnZpdGVfY2FjaGUnLFxuICAgIHJlc29sdmU6IHtcbiAgICAgICAgYWxpYXM6IHtcbiAgICAgICAgICAgICdAJzogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgJ3Jlc291cmNlcy9qcycpLFxuICAgICAgICAgICAgJ0Bjb21wb25lbnRzJzogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgJ3Jlc291cmNlcy9qcy9jb21wb25lbnRzJyksXG4gICAgICAgICAgICAnQHBhZ2VzJzogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgJ3Jlc291cmNlcy9qcy9wYWdlcycpLFxuICAgICAgICAgICAgJ0BzdG9yZSc6IHBhdGgucmVzb2x2ZShfX2Rpcm5hbWUsICdyZXNvdXJjZXMvanMvc3RvcmUnKSxcbiAgICAgICAgICAgICdAcm91dGVyJzogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgJ3Jlc291cmNlcy9qcy9yb3V0ZXInKSxcbiAgICAgICAgICAgICdAbGF5b3V0cyc6IHBhdGgucmVzb2x2ZShfX2Rpcm5hbWUsICdyZXNvdXJjZXMvanMvbGF5b3V0cycpLFxuICAgICAgICAgICAgJ0Bhc3NldHMnOiBwYXRoLnJlc29sdmUoX19kaXJuYW1lLCAncmVzb3VyY2VzL2pzL2Fzc2V0cycpLFxuICAgICAgICAgICAgJ0B2YWxpZGF0aW9uJzogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgJ3Jlc291cmNlcy9qcy92YWxpZGF0aW9uJyksXG4gICAgICAgIH0sXG4gICAgfSxcbiAgICBvcHRpbWl6ZURlcHM6IHtcbiAgICAgICAgaW5jbHVkZTogWyd2dWUnLCAndnVlLWluZGV4J10sXG4gICAgfVxufSk7XG4iXSwKICAibWFwcGluZ3MiOiAiO0FBQW9ULFNBQVMsb0JBQW9CO0FBQ2pWLE9BQU8sYUFBYTtBQUNwQixPQUFPLFNBQVM7QUFDaEIsT0FBTyxVQUFVO0FBQ2pCLE9BQU8saUJBQWlCO0FBSnhCLElBQU0sbUNBQW1DO0FBTXpDLElBQU8sc0JBQVEsYUFBYTtBQUFBLEVBQ3hCLFNBQVM7QUFBQSxJQUNMLFFBQVE7QUFBQSxNQUNKLE9BQU8sQ0FBQyx5QkFBeUIscUJBQXFCO0FBQUEsTUFDdEQsU0FBUztBQUFBLElBQ2IsQ0FBQztBQUFBLElBQ0QsSUFBSTtBQUFBLEVBQ1I7QUFBQSxFQUNBLEtBQUs7QUFBQSxJQUNELFNBQVM7QUFBQSxNQUNMLFNBQVMsQ0FBQyxZQUFZLENBQUM7QUFBQSxJQUMzQjtBQUFBLEVBQ0o7QUFBQSxFQUNBLE9BQU87QUFBQSxJQUNILFFBQVE7QUFBQSxJQUNSLFFBQVE7QUFBQSxJQUNSLFdBQVc7QUFBQSxFQUNmO0FBQUEsRUFDQSxVQUFVO0FBQUEsRUFDVixTQUFTO0FBQUEsSUFDTCxPQUFPO0FBQUEsTUFDSCxLQUFLLEtBQUssUUFBUSxrQ0FBVyxjQUFjO0FBQUEsTUFDM0MsZUFBZSxLQUFLLFFBQVEsa0NBQVcseUJBQXlCO0FBQUEsTUFDaEUsVUFBVSxLQUFLLFFBQVEsa0NBQVcsb0JBQW9CO0FBQUEsTUFDdEQsVUFBVSxLQUFLLFFBQVEsa0NBQVcsb0JBQW9CO0FBQUEsTUFDdEQsV0FBVyxLQUFLLFFBQVEsa0NBQVcscUJBQXFCO0FBQUEsTUFDeEQsWUFBWSxLQUFLLFFBQVEsa0NBQVcsc0JBQXNCO0FBQUEsTUFDMUQsV0FBVyxLQUFLLFFBQVEsa0NBQVcscUJBQXFCO0FBQUEsTUFDeEQsZUFBZSxLQUFLLFFBQVEsa0NBQVcseUJBQXlCO0FBQUEsSUFDcEU7QUFBQSxFQUNKO0FBQUEsRUFDQSxjQUFjO0FBQUEsSUFDVixTQUFTLENBQUMsT0FBTyxXQUFXO0FBQUEsRUFDaEM7QUFDSixDQUFDOyIsCiAgIm5hbWVzIjogW10KfQo=

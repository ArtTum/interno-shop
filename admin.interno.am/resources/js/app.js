// main.js
import { createApp } from "vue";
import App from '@/App.vue'
import Router from '@router/router.js'
import Store from '@store'
import './bootstrap';

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import { far } from '@fortawesome/pro-regular-svg-icons'
import { fal } from '@fortawesome/pro-light-svg-icons'
library.add(fas, fab, far, fal)

import VueAwesomePaginate from "vue-awesome-paginate";
import "vue-awesome-paginate/dist/style.css";

import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const app = createApp(App)

app.component('font-awesome-icon', FontAwesomeIcon)
app.component('Multiselect', Multiselect)

app.use(Router)
app.use(Store)
app.use(VueAwesomePaginate)
app.mount('#app');

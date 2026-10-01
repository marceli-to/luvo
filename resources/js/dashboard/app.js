import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import Notifications from '@kyvg/vue3-notification';

// vue-advanced-cropper 2 no longer injects its core styles
import 'vue-advanced-cropper/dist/style.css';

// Axios, incl. the response events the ErrorHandling mixin listens to
import axios from '@/config/axios';

// Filters (Vue 3 has no filters; available as $filters.* in templates)
import filters from '@/mixins/Filters';

// Global components
import LoadingIndicator from '@/components/ui/LoadingIndicator.vue';
import Separator from '@/components/ui/Separator.vue';

// Routes
import routes from '@/config/routes';
const router = createRouter({ history: createWebHistory(), routes });

// App component
import App from '@/App.vue';

const app = createApp(App);
app.config.globalProperties.axios = axios;
app.config.globalProperties.$filters = filters;
app.component('LoadingIndicator', LoadingIndicator);
app.component('Separator', Separator);
app.use(router);
app.use(Notifications);
app.mount('#app-administration');

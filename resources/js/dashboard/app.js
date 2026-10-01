import { createApp } from 'vue';
import Notifications from '@kyvg/vue3-notification';
import router from '@/router';
import { handleErrors } from '@/lib/http';
import App from '@/App.vue';

// vue-advanced-cropper 2 no longer injects its core styles
import 'vue-advanced-cropper/dist/style.css';

handleErrors(router);

createApp(App)
  .use(router)
  .use(Notifications)
  .mount('#app-administration');

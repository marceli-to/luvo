<template>
  <div>
    <LoadingIndicator v-if="isLoading" />
    <form @submit.prevent="submit" class="half-width">
      <header class="content-header">
        <h1>Passwort ändern</h1>
      </header>
      <div>
        <div :class="[errors.password ? 'has-error' : '', 'form-row']">
          <label>Neues Passwort (min. 6 Zeichen)</label>
          <input type="password" v-model="user.password">
        </div>
        <div :class="[errors.password_confirm ? 'has-error' : '', 'form-row']">
          <label>Neues Passwort wiederholen</label>
          <input type="password" v-model="user.password_confirm">
        </div>
      </div>
      <footer class="module-footer">
        <div>
          <button type="submit" class="btn-primary">Speichern</button>
          <router-link :to="{ name: 'home' }" class="btn-secondary">
            <span>Zurück</span>
          </router-link>
        </div>
      </footer>
    </form>
  </div>
</template>
<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { notify } from '@kyvg/vue3-notification';
import LoadingIndicator from '@/components/ui/LoadingIndicator.vue';
import http, { validationErrors } from '@/lib/http';

const router = useRouter();

const user = ref({ password: null, password_confirm: null });
const errors = ref({});
const isLoading = ref(false);

async function submit() {
  isLoading.value = true;
  try {
    await http.post('/api/user/password', user.value);
    router.push({ name: 'dashboard' });
    notify({ type: 'success', text: 'Änderungen gespeichert!' });
  }
  catch (error) {
    errors.value = validationErrors(error) ?? errors.value;
  }
  finally {
    isLoading.value = false;
  }
}
</script>

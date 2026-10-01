<template>
  <div>
    <notifications classes="notification" dangerously-set-inner-html />
    <PageHeader @open-menu="menuVisible = true" />
    <main class="site">
      <nav :class="[menuVisible ? 'is-visible' : '', 'page']">
        <header>
          <span>
            <template v-if="user">
              <router-link :to="{ name: 'user-edit', params: { id: user.id } }">{{ user.firstname }} {{ user.name }}</router-link><br>
            </template>
            <a href="/logout" class="feather-icon feather-icon--prepend">
              <LogOutIcon size="12" />
              <span>Logout</span>
            </a>
          </span>
          <a href="javascript:;" @click="menuVisible = false" class="feather-icon menu-close">
            <ArrowRightIcon size="24" />
          </a>
        </header>
        <ul>
          <li>
            <router-link :to="{ name: 'home' }">
              <span>Home</span>
            </router-link>
          </li>
          <li>
            <router-link :to="{ name: 'teams' }">
              <span>Teams</span>
            </router-link>
          </li>
          <li>
            <router-link :to="{ name: 'contact' }">
              <span>Kontakt</span>
            </router-link>
          </li>
          <li>
            <router-link :to="{ name: 'media' }">
              <span>Dateien</span>
            </router-link>
          </li>
        </ul>
      </nav>
      <router-view :key="$route.fullPath" />
    </main>
  </div>
</template>
<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ArrowRightIcon, LogOutIcon } from 'lucide-vue-next';
import PageHeader from '@/views/layout/PageHeader.vue';
import { useUser } from '@/composables/useUser';

const user = useUser();
const menuVisible = ref(false);

const route = useRoute();
watch(() => route.fullPath, () => menuVisible.value = false);
</script>

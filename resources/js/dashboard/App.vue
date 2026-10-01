<template>
<div>
  <notifications classes="notification" dangerously-set-inner-html />
  <page-header :user="user"></page-header>
  <main class="site">
    <nav :class="[!menuVisible ? '' : 'is-visible', 'page']">
      <header>
        <span>
          <template v-if="userId">
            <router-link :to="{name: 'user-edit', params: { id: userId }}">{{user}}</router-link><br>
          </template>
          <template v-else>
            <span>{{user}}</span><br>
          </template>
          <a href="/logout" class="feather-icon feather-icon--prepend">
            <log-out-icon size="12"></log-out-icon>
            <span>Logout</span>
          </a>
        </span>
        <a href="javascript:;" @click="hideMenu()" class="feather-icon menu-close">
          <arrow-right-icon size="24"></arrow-right-icon>
        </a>
      </header>
      <ul>
        <li>
          <router-link :to="{name: 'home'}">
            <span>Home</span>
          </router-link>
        </li>
        <li>
          <router-link :to="{name: 'teams'}">
            <span>Teams</span>
          </router-link>
        </li>
        <li>
          <router-link :to="{name: 'contact'}">
            <span>Kontakt</span>
          </router-link>
        </li>
        <li>
          <router-link :to="{name: 'media'}">
            <span>Dateien</span>
          </router-link>
        </li>
      </ul>
    </nav>
    <router-view></router-view>
  </main>
</div>
</template>
<script>
import { ArrowRightIcon, MenuIcon, LogOutIcon } from 'lucide-vue-next';
import PageHeader from '@/views/layout/PageHeader.vue';

export default {

  components: {
    PageHeader,
    ArrowRightIcon,
    MenuIcon,
    LogOutIcon,
  },

	data() {
		return {
      menuVisible: false,
      user: null,
      userId: null,
		}
  },
  

  mounted() {
    this.fetchUser();
  },

  methods: {
    fetchUser() {
      if (!this.user) {
        this.axios.get(`/api/user`).then(response => {
          this.user = `${response.data.firstname} ${response.data.name}`;
          this.userId = response.data.id;
        });
      }
    },

    hideMenu() {
      this.menuVisible = false;
    }
  }
}
</script>
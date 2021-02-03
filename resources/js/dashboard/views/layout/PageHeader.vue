<template>
<div>
  <header class="page-header">
    <div>
      <a href="/administration" class="brand">
       <logo />
      </a>
      <a href="javascript:;" @click="toggleMenu()" class="feather-icon">
        <menu-icon size="24"></menu-icon>
      </a>
    </div>
  </header>
  <nav :class="[!menuVisible ? '' : 'is-visible', 'page']">
    <header>
      <span>
        {{user}}<br>
        <a href="/logout" class="feather-icon feather-icon--prepend">
          <log-out-icon size="12"></log-out-icon>
          <span>Logout</span>
        </a>
      </span>
      <a href="javascript:;" @click="toggleMenu()" class="feather-icon ">
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
        <router-link :to="{name: 'media'}">
          <span>Dateien</span>
        </router-link>
      </li>
    </ul>
  </nav>
</div>
</template>
<script>

// Icons
import { ArrowRightIcon, MenuIcon, LogOutIcon } from 'vue-feather-icons';

// Theme
import Logo from '@/components/theme/Logo.vue';

export default {
  components: {
    Logo,
    ArrowRightIcon,
    MenuIcon,
    LogOutIcon,
  },

  props: {
    user: '',
  },

	data() {
		return {
			menuVisible: false
		}
  },
  
	methods: {
		toggleMenu() {
			this.menuVisible = this.menuVisible ? false : true;
		}
  },

  watch: {
    '$route'() {
      this.menuVisible = false
    }
  }
}
</script>

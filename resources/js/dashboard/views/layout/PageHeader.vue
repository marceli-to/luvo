<template>
<div>
  <header class="page-header">
    <div>
      <a href="/administration" class="brand">
       <logo />
      </a>
      <a href="javascript:;" @click="showMenu()" class="feather-icon menu-open">
        <menu-icon size="24"></menu-icon>
      </a>
    </div>
  </header>
</div>
</template>
<script>

// Icons
import { MenuIcon } from 'vue-feather-icons';

// Theme
import Logo from '@/components/theme/Logo.vue';

export default {
  components: {
    Logo,
    MenuIcon,
  },
 
	methods: {
		showMenu() {
			this.$parent.menuVisible = true;
		}
  },

  watch: {
    '$route'() {
      this.$parent.menuVisible = false
    }
  }
}
</script>

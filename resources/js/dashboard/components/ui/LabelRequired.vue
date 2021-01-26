<template>
  <div class="is-required">{{text}}</div>
</template>
<script>
export default {
  props: {
    text: {
      type: String,
      default: 'Pflichtfeld'
    }
  }
}
</script>

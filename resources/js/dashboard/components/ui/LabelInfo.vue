<template>
  <div class="is-info">{{text}}</div>
</template>
<script>
export default {
  props: {
    text: {
      type: String,
      default: ''
    }
  }
}
</script>

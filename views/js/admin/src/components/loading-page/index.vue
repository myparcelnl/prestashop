<template>
  <div>
    <p v-t="'Your label(s) are being generated... Please wait. Do not close this page.'" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'LoadingPage',
});
</script>

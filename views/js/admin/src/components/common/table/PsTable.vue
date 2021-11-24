<template>
  <table class="table table-borderless text-nowrap">
    <thead
      v-if="$slots.header">
      <slot name="header" />
    </thead>
    <tbody
      is="transition-group"
      class="mypa__table"
      name="mypa__table">
      <slot />
    </tbody>
    <tfoot
      v-if="$slots.footer">
      <slot name="footer" />
    </tfoot>
  </table>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'PsTable',
});
</script>

<template>
  <div
    v-test="AdminComponent.Row"
    :class="{
      'mypa-gap-4': !collapseGutters,
    }"
    class="mypa-cols-auto mypa-grid mypa-grid-flow-col">
    <slot />
  </div>
</template>

<script lang="ts" setup>
import {AdminComponent} from '@myparcel-pdk/admin';

defineProps<{
  collapseGutters: boolean;
  columns?: number | string;
}>();
</script>

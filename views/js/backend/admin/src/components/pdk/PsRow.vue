<template>
  <div
    v-test="AdminComponent.Row"
    :class="classes"
    class="mypa-grid mypa-grid-flow-col">
    <slot />
  </div>
</template>

<script lang="ts" setup>
import {computed} from 'vue';
import {AdminComponent} from '@myparcel-dev/pdk-admin';

const props = defineProps<{
  collapseGutters: boolean;
  columns?: number | string;
}>();

const classes = computed(() => {
  return {
    'mypa-gap-4': !props.collapseGutters,
    'mypa-cols-auto': !props.columns,
    'mypa-grid-cols-2': props.columns === 2,
  };
});
</script>

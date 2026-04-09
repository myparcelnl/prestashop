<template>
  <PdkBox :loading="loading">
    <template
      v-if="$slots.header"
      #header>
      <slot name="header" />
    </template>

    <slot />

    <template
      v-if="actions?.length"
      #footer>
      <PdkButtonGroup>
        <ActionButton
          v-for="action in actions"
          :key="action.id"
          :action="action" />
      </PdkButtonGroup>
    </template>
  </PdkBox>
</template>

<script lang="ts" setup>
import {ActionButton, type ActionDefinition} from '@myparcel-dev/pdk-admin';

withDefaults(
  defineProps<{
    actions?: ActionDefinition[];
    loading?: boolean;
  }>(),
  {
    actions: () => [],
    loading: false,
  },
);
</script>

<template>
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead v-if="$slots.header">
        <slot name="header" />
      </thead>

      <TransitionGroup
        :name="pdkConfig?.transitions.tableRow"
        tag="tbody">
        <slot />
      </TransitionGroup>

      <tfoot v-if="$slots.footer">
        <slot name="footer" />
      </tfoot>
    </table>
  </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {usePdkConfig} from '@myparcel-pdk/frontend-core';

/**
 * A table component that can be used to render data via slots.
 */
export default defineComponent({
  name: 'DefaultPdkTable',
  setup: () => ({
    pdkConfig: usePdkConfig(),
  }),
});
</script>

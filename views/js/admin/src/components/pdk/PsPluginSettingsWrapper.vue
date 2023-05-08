<template>
  <div class="card row">
    <div class="card-body col-sm-12 pdk-plugin-settings-wrapper">
      <slot />
    </div>
  </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue';

/**
 * A table component that can be used to render data via slots.
 */
export default defineComponent({
  name: 'PsPluginSettingsWrapper',
});
</script>

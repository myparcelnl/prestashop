<template>
  <div class="content-div">
    <div class="row">
      <div class="col-sm-12">
        <slot />
      </div>
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

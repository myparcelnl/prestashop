<template>
  <Transition
    name="mypa__fade"
    @beforeEnter="shown = true"
    @afterLeave="shown = false">
    <div
      v-show="show"
      class="h-100 mypa__loader-overlay position-absolute w-100"
      :class="{
        'd-flex': show || shown,
      }">
      <div class="m-auto ps-spinner" />
    </div>
  </Transition>
</template>

<script lang="ts">
import { defineComponent, ref } from '@vue/composition-api';

export default defineComponent({
  name: 'LoaderOverlay',
  props: {
    show: {
      type: Boolean,
    },
  },

  setup: () => ({
    // Toggled by the transition to be able to differentiate between its start and end.
    shown: ref(false),
  }),

});
</script>

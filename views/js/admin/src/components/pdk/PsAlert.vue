<template>
  <Transition
    appear
    name="fade">
    <div
      :class="`alert alert-${notification.variant}`"
      role="alert">
      <div class="alert-text">
        <strong
          v-if="notification.title"
          class="mb-1"
          v-text="notification.title"></strong>

        <p
          v-for="(item, index) in contentArray"
          :key="`alert_${index}_${item}`"
          v-text="item" />
      </div>
    </div>
  </Transition>
</template>

<script lang="ts">
import {PdkNotification, toArray} from '@myparcel/pdk-frontend';
import {computed, defineComponent, PropType} from 'vue';

export default defineComponent({
  name: 'PsAlert',
  props: {
    notification: {
      type: Object as PropType<PdkNotification>,
      required: true,
    },
  },

  setup: (props) => {
    return {
      contentArray: computed(() => {
        return toArray(props.notification.content);
      }),
    };
  },
});
</script>

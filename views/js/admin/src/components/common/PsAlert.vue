<template>
  <div
    class="alert"
    :class="`alert-${variant}`">
    <ul v-if="contentArray.length > 1">
      <li
        v-for="item in contentArray"
        :key="item"
        v-text="item" />
    </ul>
    <p
      v-else
      v-text="contentArray[0]" />
  </div>
</template>

<script lang="ts">
import { PropType, computed, defineComponent } from '@vue/composition-api';
import { toArray } from '@/utils/toArray';
import { variantProps } from '@/composables/props/variantProps';

export default defineComponent({
  name: 'PsAlert',
  props: {
    ...variantProps('success'),
    content: {
      type: [String, Array] as PropType<string | string[]>,
      required: true,
    },
  },

  setup: (props) => ({
    contentArray: computed((): unknown[] => toArray(props.content)),
  }),
});
</script>

<template>
  <input
    v-model="model"
    :disabled="disabled"
    class="border px-3 py-2 rounded"
    :type="type" />
  <span v-if="$attrs.currencySymbol">
    {{ $attrs.currencySymbol }}
  </span>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {useVModel} from '@vueuse/core';

export default defineComponent({
  name: 'PsInput',
  props: {
    disabled: {
      type: Boolean,
    },

    type: {
      type: String,
      default: 'text',
    },

    // eslint-disable-next-line vue/no-unused-properties
    modelValue: {
      type: [String, Number],
      default: null,
    },
  },

  setup: (props, ctx) => {
    const model = useVModel(props, 'modelValue', ctx.emit);

    return {
      model,
    };
  },
});
</script>

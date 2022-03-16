<template>
  <input
    v-model="mutableValue"
    class="form-control"
    :class="{ disabled }">
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { useModel } from '@/composables/props/model/useModel';

const { model, props, setup } = useModel();

export default defineComponent({
  name: 'PsInput',
  model,
  props: {
    // eslint-disable-next-line vue/no-unused-properties
    value: props.value,
    ...disabledProps,
  },

  setup,
});
</script>

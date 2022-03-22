<template>
  <PsInput v-model="mutableValue" />
</template>

<script lang="ts">
import PsInput from '@/components/common/form/PsInput.vue';
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { useModel } from '@/composables/props/model/useModel';

const { model, props, setup } = useModel();

export default defineComponent({
  name: 'FormSwitch',
  components: { PsInput },
  model,
  props: {
    // eslint-disable-next-line vue/no-unused-properties
    value: props.value,
    ...disabledProps,
  },

  setup: setup,
});
</script>

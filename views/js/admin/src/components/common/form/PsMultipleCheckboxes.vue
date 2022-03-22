<template>
  <div>
    <PsCheckbox
      v-for="(option, index) in options"
      :key="`${option.value}_${index}`"
      v-model="mutableValue"
      :label="option.label"
      :value="option.value" />
  </div>
</template>

<script lang="ts">
import PsCheckbox from '@/components/common/form/PsCheckbox.vue';
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { useModel } from '@/composables/props/model/useModel';
import { useOptionsProps } from '@/composables/props/useOptionsProps';

const { model, props, setup } = useModel('change');

export default defineComponent({
  name: 'PsMultipleCheckboxes',
  components: { PsCheckbox },
  model,
  props: {
    ...props,
    ...disabledProps,
    ...useOptionsProps(),
  },

  setup: (props, ctx) => {
    return {
      ...setup(props, ctx),
    };
  },
});
</script>

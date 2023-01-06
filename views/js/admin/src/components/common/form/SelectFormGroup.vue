<template>
  <FormGroup>
    <template #label>
      <slot>{{ $filters.translate(label) }}</slot>
    </template>
    <template #default>
      <PsSelect
        v-model="mutableValue"
        :disabled="disabled"
        :options="options" />
    </template>
  </FormGroup>
</template>

<script lang="ts">
import { defineComponent, toRefs } from '@vue/composition-api';
import FormGroup from '@/components/common/form/FormGroup.vue';
import PsSelect from '@/components/common/form/PsSelect.vue';
import { disabledProps } from '@/composables/props/disabledProps';
import { labelProps } from '@/composables/props/labelProps';
import { useOptionsProps } from '@/composables/props/useOptionsProps';
import { useSelectModel } from '@/composables/props/model/useSelectModel';

const { model, props, setup } = useSelectModel();

export default defineComponent({
  name: 'SelectFormGroup',
  components: { FormGroup, PsSelect },
  model,
  props: {
    ...props,
    ...disabledProps,
    ...labelProps,
    ...useOptionsProps(),
  },

  setup: (props, ctx) => {
    const data = setup(props, ctx);
    const propRefs = toRefs(props);

    // @ts-expect-error - Vue 2
    if (!data.mutableValue.value && propRefs.options.value.length) {
      // @ts-expect-error - Vue 2
      data.mutableValue.value = propRefs.options.value[0].value;
    }

    return {
      ...data,
    };
  },
});
</script>

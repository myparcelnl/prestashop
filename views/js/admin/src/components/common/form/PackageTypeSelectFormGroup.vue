<template>
  <SelectFormGroup
    v-model="mutableValue"
    :disabled="disabled"
    :options="options"
    label="shipment_package_type" />
</template>

<script lang="ts">
import { ContextKey } from '@/data/global/context';
import SelectFormGroup from '@/components/common/form/SelectFormGroup.vue';
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { useGlobalContext } from '@/composables/context/useGlobalContext';
import { useSelectModel } from '@/composables/props/model/useSelectModel';

const { model, props, setup } = useSelectModel();

export default defineComponent({
  name: 'PackageTypeSelectFormGroup',
  components: { SelectFormGroup },
  model,
  props: {
    ...props,
    ...disabledProps,
  },

  setup: (props, ctx) => {
    return {
      ...setup(props, ctx),
      options: ctx.attrs.options,
    };
  },
});
</script>

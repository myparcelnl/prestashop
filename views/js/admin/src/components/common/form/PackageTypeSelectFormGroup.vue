<template>
  <SelectFormGroup
    v-model="mutableValue"
    :disabled="disabled"
    :options="options"
    label="shipment_package_type" />
</template>

<script lang="ts">
import { PropType, computed, defineComponent } from '@vue/composition-api';
import SelectFormGroup from '@/components/common/form/SelectFormGroup.vue';
import { disabledProps } from '@/composables/props/disabledProps';
import { useSelectModel } from '@/composables/props/model/useSelectModel';

const { model, props, setup } = useSelectModel();

export default defineComponent({
  name: 'PackageTypeSelectFormGroup',
  components: { SelectFormGroup },
  model,
  props: {
    ...props,
    ...disabledProps,

    packageTypes: {
      type: Array as PropType<PackageType[]>,
      required: true,
    },
  },

  setup: (props, ctx) => {
    return {
      ...setup(props, ctx),

      options: computed(() => {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        return props.packageTypes.map((packageType) => {
          return {
            label: packageType.human,
            value: packageType.id,
          };
        });
      }),
    };
  },
});
</script>

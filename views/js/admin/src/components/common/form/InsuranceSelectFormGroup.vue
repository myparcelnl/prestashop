<template>
  <SelectFormGroup
    v-model="mutableValue"
    :disabled="disabled"
    :options="options"
    label="shipment_options_insurance" />
</template>

<script lang="ts">
import { ContextKey, ShipmentOptionsContext } from '@/data/global/context';
import { computed, defineComponent } from '@vue/composition-api';
import SelectFormGroup from '@/components/common/form/SelectFormGroup.vue';
import { contextProps } from '@/composables/props/contextProps';
import { disabledProps } from '@/composables/props/disabledProps';
import { translate } from '@/filters/translate';
import { useGlobalContext } from '@/composables/context/useGlobalContext';
import { useSelectModel } from '@/composables/props/model/useSelectModel';

const { model, props, setup } = useSelectModel();

export default defineComponent({
  name: 'InsuranceSelectFormGroup',
  components: { SelectFormGroup },
  model,
  props: {
    // eslint-disable-next-line vue/no-unused-properties
    value: props.value,
    ...contextProps,
    ...disabledProps,
  },

  setup: (props, ctx) => {
    const contextData = useGlobalContext(ContextKey.SHIPMENT_OPTIONS, props.context as ShipmentOptionsContext);
    const options = computed(() => {
      return [
        {
          label: translate('none'),
          value: 0,
        },
        ...contextData.value.consignment.insuranceOptions.map((amount) => {
          const formattedCurrency = getCurrencyFormatter().format(amount);
          return {
            label: `${translate('up_to')} ${formattedCurrency}`,
            value: amount,
          };
        }),
      ];
    });

    return {
      ...setup(props, ctx),
      options,
    };
  },
});
</script>

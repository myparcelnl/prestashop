<template>
  <SelectFormGroup
    v-model="mutableValue"
    :disabled="disabled"
    :options="options">
    {{ $filters.translate('extra_options_digital_stamp_weight') }}
    <p
      class="form-text"
      v-text="weightString" />
  </SelectFormGroup>
</template>

<script lang="ts">
import { ContextKey } from '@/data/global/context';
import SelectFormGroup from '@/components/common/form/SelectFormGroup.vue';
import { defineComponent } from '@vue/composition-api';
import { disabledProps } from '@/composables/props/disabledProps';
import { formatNumber } from '@/filters/formatNumber';
import { translate } from '@/filters/translate';
import { useGlobalContext } from '@/composables/context/useGlobalContext';
import { useSelectModel } from '@/composables/props/model/useSelectModel';

const { model, props, setup } = useSelectModel();

export default defineComponent({
  name: 'DigitalStampWeightSelectFormGroup',
  components: { SelectFormGroup },
  model,

  props: {
    ...disabledProps,
    // eslint-disable-next-line vue/no-unused-properties
    value: props.value,
    calculatedWeight: {
      type: Number,
      required: true,
    },
  },

  setup: (props, ctx) => {
    const contextData = useGlobalContext(ContextKey.SHIPMENT_OPTIONS);

    return {
      ...setup(props, ctx),
      options: contextData.value.options.digitalStampWeight,
      weightString: `${translate('order_calculated_weight')} ${formatNumber(props.calculatedWeight)}g`,
    };
  },
});
</script>

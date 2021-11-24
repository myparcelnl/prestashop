<template>
  <div>
    <FormGroup label="customer_name">
      <PsInput v-model="contextData.name" />
    </FormGroup>
    <FormGroup label="customer_email">
      <PsInput v-model="contextData.email" />
    </FormGroup>
    <FormGroup label="custom_label">
      <PsInput v-model="customLabel" />
    </FormGroup>
    <PackageTypeSelectFormGroup
      v-if="packageTypeOptions.length"
      v-model="packageType"
      :options="packageTypeOptions" />
    <PackageFormatSelectFormGroup
      v-if="packageFormatOptions.length"
      v-model="packageFormat"
      :options="packageFormatOptions" />

    <FormGroup>
      <PsCheckbox
        v-model="shipmentOptions.signature"
        label="shipment_options_signature" />
    </FormGroup>

    <InsuranceSelectFormGroup v-model="shipmentOptions.insurance" />
  </div>
</template>

<script lang="ts">
import { PropType, defineComponent, reactive, ref } from '@vue/composition-api';
import { ContextKey } from '@/data/global/context';
import FormGroup from './common/form/FormGroup.vue';
import InsuranceSelectFormGroup from '@/components/order/InsuranceSelectFormGroup.vue';
import PackageFormatSelectFormGroup from '@/components/order/PackageFormatSelectFormGroup.vue';
import PackageTypeSelectFormGroup from '@/components/order/PackageTypeSelectFormGroup.vue';
import PsCheckbox from '@/components/common/form/PsCheckbox.vue';
import PsInput from './common/form/PsInput.vue';
import { translate } from '@/filters/translate';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

export default defineComponent({
  name: 'ReturnsForm',
  components: {
    InsuranceSelectFormGroup,
    FormGroup,
    PackageFormatSelectFormGroup,
    PackageTypeSelectFormGroup,
    PsCheckbox,
    PsInput,
  },

  props: {
    modalData: {
      type: Object as PropType<ShipmentLabel>,
      default: null,
    },
  },

  setup: (props) => {
    const contextData = useGlobalContext(ContextKey.RETURNS_FORM);
    const label = { barcode: props.modalData.barcode };

    return {
      contextData,
      shipmentOptions: reactive({}),
      packageTypeOptions: [],
      packageFormatOptions: [],
      packageType: ref(),
      packageFormat: ref(),
      customLabel: ref<string>(`${translate('return_prefix')} ${label.barcode}`),
    };
  },
});
</script>

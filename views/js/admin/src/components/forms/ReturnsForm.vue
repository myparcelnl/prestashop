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
  </div>
</template>

<script lang="ts">
import { PropType, defineComponent, ref } from '@vue/composition-api';
import { ContextKey } from '@/data/global/context';
import FormGroup from '@/components/common/form/FormGroup.vue';
import PackageFormatSelectFormGroup from '@/components/common/form/PackageFormatSelectFormGroup.vue';
import PackageTypeSelectFormGroup from '@/components/common/form/PackageTypeSelectFormGroup.vue';
import PsInput from '@/components/common/form/PsInput.vue';
import { translate } from '@/filters/translate';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

export default defineComponent({
  name: 'ReturnsForm',
  components: {
    FormGroup,
    PackageFormatSelectFormGroup,
    PackageTypeSelectFormGroup,
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
      packageTypeOptions: ['package', 'mailbox'],
      packageFormatOptions: [1, 2],
      packageType: ref(),
      packageFormat: ref(),
      customLabel: ref<string>(`${translate('return_prefix')} ${label.barcode}`),
    };
  },
});
</script>

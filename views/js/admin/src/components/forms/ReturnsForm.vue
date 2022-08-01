<template>
  <div>
    <FormGroup label="custom_label">
      <PsInput v-model="labelDescriptionText" />
    </FormGroup>
    <PackageTypeSelectFormGroup
      v-model="packageTypeId"
      :package-types="contextData.options.packageType" />
    <PackageFormatSelectFormGroup
      v-if="contextData.options.packageFormat.length"
      v-model="packageFormat"
      :options="contextData.options.packageFormat" />
  </div>
</template>

<script lang="ts">
import { PropType, defineComponent, ref, watchEffect } from '@vue/composition-api';
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
    const { largeFormat, packageType } = contextData.value;
    const label = { barcode: props.modalData.barcode };

    const labelDescriptionText = ref<string>(`${translate('return_prefix')} ${label.barcode}`);
    const packageTypeId = ref<PackageType>(packageType);
    const packageFormat = ref<number>(largeFormat);

    watchEffect(() => {
      contextData.value.labelDescription = labelDescriptionText.value;
      contextData.value.packageType = packageTypeId.value;
      contextData.value.largeFormat = packageFormat.value;
    });

    return {
      labelDescriptionText,
      packageTypeId,
      packageFormat,
      contextData,
    };
  },
});
</script>

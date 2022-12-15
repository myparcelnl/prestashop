<template>
  <div>
    <FormGroup label="custom_label">
      <PsInput v-model="labelDescriptionRef" />
    </FormGroup>
    <PackageTypeSelectFormGroup
      v-model="packageTypeRef"
      :package-types="contextData.options.packageType" />
    <PackageFormatSelectFormGroup
      v-if="showPackageFormat"
      v-model="packageFormatRef"
      :options="contextData.options.packageFormat" />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType, ref, watchEffect } from '@vue/composition-api';
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
    const {packageFormat, packageType} = contextData.value;
    const label = {barcode: props.modalData.barcode};

    const labelDescriptionRef = ref<string>(`${translate('return_prefix')} ${label.barcode}`);

    const packageTypeRef = ref<PackageType>({id: '1', name: packageType, human: 'pakkettype'});
    const packageFormatRef = ref<number>(packageFormat);
    const showPackageFormat = packageType === 'package';

    console.log(packageType);
    console.log(packageTypeRef);
    console.log(packageFormat);
    console.log(packageFormatRef);

    watchEffect(() => {
      contextData.value.labelDescription = labelDescriptionRef.value;
      contextData.value.packageType = packageTypeRef.value.name;
      contextData.value.packageFormat = packageFormatRef.value;
    });

    return {
      labelDescriptionRef,
      packageTypeRef,
      packageFormatRef,
      contextData,
      showPackageFormat,
    };
  },
});
</script>

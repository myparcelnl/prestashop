<template>
  <div>
    <PsInput
      v-model="contextData.orderId"
      type="hidden" />

    <FormGroup label="extra_options_label_amount">
      <PsInput
        v-model="contextData.extraOptions.labelAmount"
        type="number"
        min="1"
        max="10" />
    </FormGroup>

    <PackageTypeSelectFormGroup v-model="contextData.deliveryOptions.packageType" />
    <PackageFormatSelectFormGroup
      v-if="contextData.consignment && contextData.consignment.canHaveLargeFormat"
      v-model="contextData.labelOptions.package_format" />
    <DigitalStampWeightSelectFormGroup
      v-if="contextData.deliveryOptions.packageType === 'digital_stamp'"
      v-model="contextData.extraOptions.digitalStampWeight"
      :calculated-weight="contextData.orderWeight || 0" />

    <div v-if="showShipmentOptions">
      <FormGroup label="shipment_options_title">
        <PsCheckbox
          v-if="contextData.consignment.canHaveOnlyRecipient"
          v-model="shipmentOptions.only_recipient"
          label="shipment_options_only_recipient" />

        <PsCheckbox
          v-if="contextData.consignment.canHaveAgeCheck"
          v-model="shipmentOptions.age_check"
          label="shipment_options_age_check" />

        <PsCheckbox
          v-if="contextData.consignment.canHaveReturn"
          v-model="shipmentOptions.return"
          label="shipment_options_return" />

        <PsCheckbox
          v-if="contextData.consignment.canHaveSignature"
          v-model="shipmentOptions.signature"
          label="shipment_options_signature" />
      </FormGroup>

      <InsuranceSelectFormGroup
        v-if="contextData.consignment.canHaveInsurance"
        v-model="shipmentOptions.insurance"
        :context="contextData" />
    </div>

    <PsAlert
      v-if="contextData.deliveryOptionsDateChanged"
      variant="warning"
      :content="$filters.translate('delivery_date_changed')" />
  </div>
</template>

<script lang="ts">
import { ContextKey, ShipmentOptionsContext } from '@/data/global/context';
import { computed, defineComponent, watchEffect } from '@vue/composition-api';
import DigitalStampWeightSelectFormGroup from '@/components/order/DigitalStampWeightSelectFormGroup.vue';
import FormGroup from '@/components/common/form/FormGroup.vue';
import InsuranceSelectFormGroup from '@/components/order/InsuranceSelectFormGroup.vue';
import PackageFormatSelectFormGroup from '@/components/order/PackageFormatSelectFormGroup.vue';
import PackageTypeSelectFormGroup from '@/components/order/PackageTypeSelectFormGroup.vue';
import PsAlert from '@/components/common/PsAlert.vue';
import PsCheckbox from '@/components/common/form/PsCheckbox.vue';
import PsInput from '@/components/common/form/PsInput.vue';
import { contextProps } from '@/composables/props/contextProps';
import { deliveryOptionsEventBus } from '@/data/eventBus/DeliveryOptionsEventBus';
import { orderActionsEventBus } from '@/data/eventBus/OrderActionsEventBus';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

const KEYS_TO_SAVE: (keyof ShipmentOptionsContext)[] = [
  'orderId',
  'deliveryOptions',
  'extraOptions',
  'labelOptions',
];

const CONSIGNMENT_SHIPMENT_OPTIONS_KEYS = [
  'canHaveOnlyRecipient',
  'canHaveAgeCheck',
  'canHaveReturn',
  'canHaveSignature',
  'canHaveInsurance',
] as (keyof Consignment)[];

export default defineComponent({
  name: 'ShipmentOptions',
  components: {
    DigitalStampWeightSelectFormGroup,
    FormGroup,
    InsuranceSelectFormGroup,
    PackageFormatSelectFormGroup,
    PackageTypeSelectFormGroup,
    PsAlert,
    PsCheckbox,
    PsInput,
  },

  props: {
    ...contextProps,
  },

  setup: (props) => {
    const contextData = useGlobalContext(ContextKey.SHIPMENT_OPTIONS, props.context as ShipmentOptionsContext);
    const shipmentOptions = computed(() => {
      return contextData.value.deliveryOptions?.shipmentOptions ?? {};
    });

    const showShipmentOptions = computed(() => {
      const anyShipmentOptionIsSet = CONSIGNMENT_SHIPMENT_OPTIONS_KEYS.some((property) => {
        return Boolean(contextData.value.consignment[property]);
      });

      return contextData.value.consignment && anyShipmentOptionIsSet;
    });

    /**
     * Update the event buses when any contextData property is modified to be able to send it to the backend.
     */
    watchEffect(() => {
      const values = KEYS_TO_SAVE.reduce((acc, key) => {
        return { ...acc, [key]: contextData.value[key] };
      }, {});

      deliveryOptionsEventBus.update(values);
      orderActionsEventBus.update(values);
    });

    return { contextData, showShipmentOptions, shipmentOptions };
  },
});
</script>

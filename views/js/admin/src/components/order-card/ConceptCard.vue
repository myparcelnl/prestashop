<template>
  <PsCard :loading="loading">
    <template #header>
      <MaterialIcon icon="article" />
      <span
        v-t="'concept'"
        class="mr-2" />
      <DeliveryMomentSelector />
    </template>
    <template #default>
      <div class="row">
        <div class="col-sm-12 col-xl-6">
          <ShipmentOptionsForm />
        </div>
        <div class="col-sm-12 col-xl-6">
          <ShippingAddress />
        </div>
      </div>
    </template>
    <template #footer>
      <PsButton
        icon="save"
        @click="saveDeliveryOptions" />

      <div class="flex-fill" />

      <div class="btn-group">
        <PsButton
          icon="add"
          label="action_new_shipment"
          variant="outline-primary"
          @click="exportOrder" />
        <PsButton
          :icon="['add', 'local_printshop']"
          variant="outline-primary"
          @click="() => exportOrder(true)">
          {{ $filters.translate('action_new_shipment_print') }}
        </PsButton>
      </div>
    </template>
  </PsCard>
</template>

<script lang="ts">
import DeliveryMomentSelector from '@/components/order-card/DeliveryMomentSelector.vue';
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import { OrderAction } from '@/data/global/actions';
import PsButton from '@/components/common/PsButton.vue';
import PsCard from '@/components/common/PsCard.vue';
import ShipmentOptionsForm from '@/components/forms/ShipmentOptionsForm.vue';
import ShippingAddress from '@/components/order-card/ShippingAddress.vue';
import { defineComponent } from '@vue/composition-api';
import { deliveryOptionsEventBus } from '@/data/eventBus/DeliveryOptionsEventBus';
import { executeOrderAction } from '@/services/actions/executeOrderAction';
import { orderActionsEventBus } from '@/data/eventBus/OrderActionsEventBus';
import { shipmentOptionsContextEventBus } from '@/data/eventBus/ShipmentOptionsContextEventBus';
import { useEventBusLoadingState } from '@/composables/useEventBusLoadingState';

export default defineComponent({
  name: 'ConceptCard',
  components: {
    DeliveryMomentSelector,
    MaterialIcon,
    PsButton,
    PsCard,
    ShipmentOptionsForm,
    ShippingAddress,
  },

  setup: () => {
    return {
      ...useEventBusLoadingState(
        deliveryOptionsEventBus,
        orderActionsEventBus,
        shipmentOptionsContextEventBus,
      ),

      saveDeliveryOptions: async (): Promise<void> => {
        await deliveryOptionsEventBus.saveConfiguration();
      },

      exportOrder: async (print: boolean = false): Promise<void> => {
        const action = print ? OrderAction.EXPORT_PRINT : OrderAction.EXPORT;

        await executeOrderAction(action);
      },
    };
  },
});
</script>

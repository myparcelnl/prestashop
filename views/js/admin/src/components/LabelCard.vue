<template>
  <div class="card p-1">
    <label v-text="label.status" />
    <div class="align-items-center d-flex text-nowrap">
      <a
        target="_blank"
        :href="label.track_link"
        v-text="label.barcode" />
      <a
        class="btn btn-link"
        @click="() => print(label)">
        <MaterialIcon icon="print" />
      </a>
      <a
        class="btn btn-link"
        @click="() => refresh(label)">
        <MaterialIcon icon="refresh" />
      </a>
    </div>
    <LoaderOverlay v-show="loading" />
  </div>
</template>

<script lang="ts">
import { PropType, defineComponent } from '@vue/composition-api';
import { EventName } from '@/data/eventBus/EventBus';
import { LabelAction } from '@/data/global/actions';
import LoaderOverlay from '@/components/common/LoaderOverlay.vue';
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import ShipmentLabel from '@/components/order/ShipmentLabel.vue';
import { deliveryOptionsEventBus } from '@/data/eventBus/DeliveryOptionsEventBus';
import { executeLabelAction } from '@/services/actions/executeLabelAction';
import { useLoading } from '@/composables/useLoading';

export default defineComponent({
  name: 'LabelCard',
  components: { LoaderOverlay, MaterialIcon },
  props: {
    label: {
      type: Object as PropType<ShipmentLabel>,
      required: true,
    },
  },

  setup: () => {
    const { loading, setLoading } = useLoading();
    deliveryOptionsEventBus.on(EventName.BUSY, ({ response: busy }) => setLoading(busy));

    const execute = async(action: LabelAction, label: ShipmentLabel): Promise<void> => {
      await executeLabelAction(action, Number(label.id_label));
    };

    return {
      loading,
      print: async(label: ShipmentLabel): Promise<void> => execute(LabelAction.PRINT, label),
      refresh: async(label: ShipmentLabel): Promise<void> => execute(LabelAction.REFRESH, label),
    };
  },
});
</script>

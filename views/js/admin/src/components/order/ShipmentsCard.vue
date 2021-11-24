<template>
  <PsCard :loading="loading">
    <template #header>
      <MaterialIcon icon="local_shipping" />
      {{ $filters.translate('order_labels_header') }}
    </template>
    <template #default>
      <ShipmentLabels @select="(value) => selectedLabels = value" />
    </template>
    <template #footer>
      <PsDropdownButton
        :options="bulkActionDropdownItems"
        :disabled="!selectedLabels.length"
        @click="onBulkAction">
        {{ $filters.translate('bulk_actions') }}
        <span
          v-if="selectedLabels.length"
          class="badge badge-light ml-1"
          v-text="selectedLabels.length" />
      </PsDropdownButton>
    </template>
  </PsCard>
</template>

<script lang="ts">
import { defineComponent, ref } from '@vue/composition-api';
import { deleteAction, printAction, refreshAction } from '@/data/dropdownActions';
import { EventName } from '@/data/eventBus/EventBus';
import { LabelAction } from '@/data/global/actions';
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import PsCard from '@/components/common/PsCard.vue';
import PsDropdownButton from '@/components/common/PsDropdownButton.vue';
import ShipmentLabels from '@/components/order/ShipmentLabels.vue';
import { executeLabelAction } from '@/services/actions/executeLabelAction';
import { labelActionsEventBus } from '@/data/eventBus/LabelActionsEventBus';
import { useLoading } from '@/composables/useLoading';

export default defineComponent({
  name: 'ShipmentsCard',
  components: {
    PsDropdownButton,
    ShipmentLabels,
    MaterialIcon,
    PsCard,
  },

  setup: () => {
    const { loading, setLoading } = useLoading();
    labelActionsEventBus.on(EventName.BUSY, setLoading);
    const selectedLabels = ref<number[]>([]);

    return {
      loading,
      selectedLabels,
      bulkActionDropdownItems: [
        refreshAction,
        printAction,
        deleteAction,
      ],

      async onBulkAction(action: LabelAction): Promise<void> {
        await executeLabelAction(action, selectedLabels.value);
      },
    };
  },
});
</script>

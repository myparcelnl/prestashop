<template>
  <PsCard :loading="loading">
    <template #header>
      <MaterialIcon icon="local_shipping" />
      {{ $filters.translate('order_labels_header') }}
    </template>
    <template #default>
      <ShipmentLabels @select="setSelectedLabels" />
    </template>
    <template #footer>
      <PsDropdownButton
        :options="bulkActionDropdownItems"
        :disabled="!selectedLabels.length"
        @click="onBulkAction">
        {{ $filters.translate('bulk_actions') }}
        <span
          v-if="selectedLabels.length"
          class="badge badge-dark ml-1"
          v-text="selectedLabels.length" />
      </PsDropdownButton>
    </template>
  </PsCard>
</template>

<script lang="ts">
import { defineComponent, ref } from '@vue/composition-api';
import { deleteAction, printAction, refreshAction } from '@/data/dropdownActions';
import { LabelAction } from '@/data/global/actions';
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import PsCard from '@/components/common/PsCard.vue';
import PsDropdownButton from '@/components/common/PsDropdownButton.vue';
import ShipmentLabels from '@/components/order-card/ShipmentLabels.vue';
import { executeLabelAction } from '@/services/actions/executeLabelAction';
import { labelActionsEventBus } from '@/data/eventBus/LabelActionsEventBus';
import { useEventBusLoadingState } from '@/composables/useEventBusLoadingState';

export default defineComponent({
  name: 'ShipmentsCard',
  components: {
    PsDropdownButton,
    ShipmentLabels,
    MaterialIcon,
    PsCard,
  },

  setup: () => {
    const selectedLabels = ref<number[]>([]);

    return {
      ...useEventBusLoadingState(labelActionsEventBus),
      selectedLabels,

      setSelectedLabels(labels: number[]): void {
        selectedLabels.value = labels;
      },

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

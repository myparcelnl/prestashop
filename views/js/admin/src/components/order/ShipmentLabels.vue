<template>
  <PsTable class="mb-0">
    <template #header>
      <PsTableRow>
        <PsTableCol component="th">
          <PsCheckbox
            :checked="Boolean(contextData.labels.length && selectedRows.length === contextData.labels.length)"
            :disabled="!contextData.labels.length"
            @change="selectAll" />
        </PsTableCol>
        <PsTableCol
          v-t="'order_labels_column_track_trace'"
          component="th" />
        <PsTableCol
          v-t="'order_labels_column_status'"
          component="th" />
        <PsTableCol
          v-t="'order_labels_column_last_update'"
          component="th" />
        <PsTableCol
          v-t="'order_labels_column_actions'"
          component="th"
          class="text-right" />
      </PsTableRow>
    </template>

    <template #default>
      <PsTableRow
        v-if="!contextData.labels.length"
        key="tr_no_shipments">
        <PsTableCol colspan="5">
          <div class="p-3 text-center">
            <MaterialIcon icon="warning" />
            {{ $filters.translate('no_shipments') }}
          </div>
        </PsTableCol>
      </PsTableRow>

      <ShipmentLabel
        v-for="label in contextData.labels"
        :key="`${label.id_label}_${label.refreshed_at}`"
        v-model="selectedRows"
        :shipment-label="label" />
    </template>
  </PsTable>
</template>

<script lang="ts">
import { computed, defineComponent, ref } from '@vue/composition-api';
import { ContextKey } from '@/data/global/context';
import { EventName } from '@/data/eventBus/EventBus';
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import PsCheckbox from '@/components/common/form/PsCheckbox.vue';
import PsTable from '@/components/common/table/PsTable.vue';
import PsTableCol from '@/components/common/table/PsTableCol.vue';
import PsTableRow from '@/components/common/table/PsTableRow.vue';
import ShipmentLabel from '@/components/order/ShipmentLabel.vue';
import { orderActionsEventBus } from '@/data/eventBus/OrderActionsEventBus';
import { useGlobalContext } from '@/composables/context/useGlobalContext';
import { labelActionsEventBus } from '@/data/eventBus/LabelActionsEventBus';

export default defineComponent({
  name: 'ShipmentLabels',
  components: {
    MaterialIcon,
    PsCheckbox,
    PsTable,
    PsTableCol,
    PsTableRow,
    ShipmentLabel,
  },

  emits: ['select'],

  setup: (props, ctx) => {
    const contextData = useGlobalContext(ContextKey.SHIPMENT_LABELS);
    const mutableSelectedRows = ref<string[]>([]);
    const labels = ref(contextData.value.labels);

    const selectedRows = computed({
      get(): string[] {
        return mutableSelectedRows.value;
      },
      set(rows: string[]): void {
        mutableSelectedRows.value = rows;
        ctx.emit('select', rows);
      },
    });

    /**
     * Handles (de)selecting bulk checkboxes when clicking the checkbox in the table header.
     */
    const selectAll = (bulkCheckboxChecked: boolean): void => {
      selectedRows.value = bulkCheckboxChecked || selectedRows.value.length !== labels.value.length
        ? labels.value.map((label) => label.id_label.toString())
        : [];
    };

    const clearSelection = (): void => {
      selectedRows.value = [];
    };

    orderActionsEventBus.on(EventName.RESPONSE, clearSelection);
    labelActionsEventBus.on(EventName.RESPONSE, clearSelection);

    return {
      contextData,
      selectAll,
      selectedRows,
    };
  },
});
</script>

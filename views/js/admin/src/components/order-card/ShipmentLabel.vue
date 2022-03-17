<template>
  <PsTableRow>
    <PsTableCol>
      <PsCheckbox
        v-model="mutableValue"
        :value="shipmentLabel.id_label" />
    </PsTableCol>
    <PsTableCol>
      <a
        class="text-nowrap"
        :href="shipmentLabel.track_link"
        target="_blank"
        rel="noopener noreferrer">
        {{ shipmentLabel.barcode }}
        <MaterialIcon
          icon="open_in_new"
          class="font-16" />
      </a>
    </PsTableCol>

    <PsTableCol v-text="shipmentLabel.status" />
    <PsTableCol v-text="shipmentLabel.date_upd" />
    <PsTableCol class="text-right">
      <div class="btn-group">
        <PsButton
          class="btn-sm"
          icon="local_printshop"
          label="action_print"
          @click="() => doLabelAction()" />

        <PsDropdownButton
          class="dropdown-toggle-split"
          :options="rowDropdownItems"
          @click="(action) => doLabelAction(action)" />
      </div>
    </PsTableCol>
  </PsTableRow>
</template>

<script lang="ts">
import { PropType, defineComponent } from '@vue/composition-api';
import { deleteAction, refreshAction, returnAction } from '@/data/dropdownActions';
import { LabelAction } from '@/data/global/actions';
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import PsButton from '@/components/common/PsButton.vue';
import PsCheckbox from '@/components/common/form/PsCheckbox.vue';
import PsDropdownButton from '@/components/common/PsDropdownButton.vue';
import PsTableCol from '@/components/common/table/PsTableCol.vue';
import PsTableRow from '@/components/common/table/PsTableRow.vue';
import { executeLabelAction } from '@/services/actions/executeLabelAction';
import { useCheckboxModel } from '@/composables/props/model/useCheckboxModel';

const { model, props, setup } = useCheckboxModel();

export default defineComponent({
  name: 'ShipmentLabel',
  components: {
    PsTableCol,
    PsTableRow,
    MaterialIcon,
    PsButton,
    PsCheckbox,
    PsDropdownButton,
  },

  model,

  props: {
    // eslint-disable-next-line vue/no-unused-properties
    checked: props.checked,
    shipmentLabel: {
      type: Object as PropType<ShipmentLabel>,
      required: true,
    },
  },

  setup: (props, ctx) => {
    /**
     * Callback function for any label action.
     */
    async function doLabelAction(action: LabelAction = LabelAction.PRINT): Promise<void> {
      await executeLabelAction(action, Number(props.shipmentLabel.id_label), props.shipmentLabel);
    }

    return {
      ...setup(props, ctx),
      doLabelAction,
      rowDropdownItems: [
        refreshAction,
        returnAction,
        deleteAction,
      ],
    };
  },
});
</script>

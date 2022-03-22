<template>
  <Modal
    id="addCarrier"
    title="add_carrier_title"
    :on-save="addCarrier"
    on-leave=""
    :context-key="$ContextKey.ADD_CARRIER_FORM">
    <template #default="data">
      <SettingsFormItem
        v-for="(child, index) in data.context.children"
        :key="[data.context.name, child.name, child.action, child.label, child.type, index].join('_')"
        :item="child"
        @change="processValue" />
    </template>
  </Modal>
</template>

<script lang="ts">
import { defineComponent, ref } from '@vue/composition-api';
import Modal from '@/components/modals/Modal.vue';
import { buttonActionsEventBus } from '@/data/eventBus/ButtonActionsEventBus';

export default defineComponent({
  name: 'AddCarrierModal',
  components: {
    Modal,
  },

  setup: () => {
    const values = ref<Record<string, string>>({});

    return {
      processValue(val: AddCarrierValue): void {
        values.value[val.name] = val.value;
      },

      async addCarrier(): Promise<void> {
        await buttonActionsEventBus.execute([window.MyParcelActions.pathButtonAction, 'addCarrier'], values.value);
      },
    };
  },
});
</script>

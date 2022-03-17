<template>
  <Modal
    :context-key="$ContextKey.RETURNS_FORM"
    title="returns_form_title"
    save-label="create">
    <template #default="data">
      <ReturnsForm v-bind="data" />
    </template>
  </Modal>
</template>

<script lang="ts">
import Modal from '@/components/modals/Modal.vue';
import ReturnsForm from '@/components/forms/ReturnsForm.vue';
import { defineComponent } from '@vue/composition-api';

/**
 * Modal with a form to create a return shipment. Triggered by pressing "Create return label" on a label in the MyParcel
 * card in the single order view.
 */
export default defineComponent({
  name: 'ReturnsFormModal',
  components: {
    ReturnsForm,
    Modal,
  },
});
</script>

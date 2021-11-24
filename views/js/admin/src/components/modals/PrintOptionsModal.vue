<template>
  <Modal
    id="printOptions"
    title="print_options_title">
    <PrintOptionsForm />
  </Modal>
</template>

<script lang="ts">
import Modal from '@/components/modals/Modal.vue';
import PrintOptionsForm from '@/components/forms/PrintOptionsForm.vue';
import { defineComponent } from '@vue/composition-api';

/**
 * Modal with print options. Opened when any print action is executed, if the modal is enabled in the module settings.
 */
export default defineComponent({
  name: 'PrintOptionsModal',
  components: {
    Modal,
    PrintOptionsForm,
  },
});
</script>

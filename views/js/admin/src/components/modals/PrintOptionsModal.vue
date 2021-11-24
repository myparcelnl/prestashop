<template>
  <Modal
    id="printOptions"
    title="print_options_title">
    <PrintOptions />
  </Modal>
</template>

<script lang="ts">
import Modal from '@/components/Modal.vue';
import PrintOptions from '@/components/PrintOptions.vue';
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'PrintOptionsModal',
  components: {
    PrintOptions,
    Modal,
  },
});
</script>

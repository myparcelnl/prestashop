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
import Modal from '@/components/Modal.vue';
import ReturnsForm from '@/components/ReturnsForm.vue';
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'ReturnsFormModal',
  components: {
    ReturnsForm,
    Modal,
  },
});
</script>

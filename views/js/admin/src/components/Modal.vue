<template>
  <div
    :id="modalId"
    class="fade modal"
    tabindex="-1"
    role="dialog">
    <div
      class="modal-dialog"
      role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4
            v-t="title"
            class="modal-title" />
          <button
            type="button"
            class="close"
            data-dismiss="modal">
            <MaterialIcon icon="close" />
          </button>
        </div>
        <div
          v-if="shown"
          class="modal-body">
          <slot
            :modal-data="modalData"
            :context="contextData" />
          <LoaderOverlay v-show="loading" />
        </div>
        <div class="modal-footer">
          <PsButton
            class="btn-lg btn-outline-secondary"
            label="cancel"
            @click="() => onButtonClick('leave')" />
          <PsButton
            class="btn-lg btn-primary"
            :label="saveLabel || 'save'"
            data-type="save"
            @click="() => onButtonClick('save')" />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { ModalCallbackProps, useModalContext } from '@/composables/context/useModalContext';
import { PropType, computed, defineComponent, ref } from '@vue/composition-api';
import LoaderOverlay from '@/components/common/LoaderOverlay.vue';
import MaterialIcon from '@/components/common/MaterialIcon.vue';
import PsButton from '@/components/common/PsButton.vue';
import { contextProps } from '@/composables/props/contextProps';
import { useContext } from '@/composables/context/useContext';

export default defineComponent({
  name: 'Modal',
  components: {
    LoaderOverlay,
    MaterialIcon,
    PsButton,
  },

  props: {
    id: {
      type: String,
      default: null,
    },

    /**
     * Callback to change behavior of save button. Note: You need to manually close the modal when using this.
     */
    onSave: {
      type: Function as PropType<ModalCallbackProps['onSave']>,
      default: null,
    },

    /**
     * Callback to change behavior of leave button. Note: You need to manually close the modal when using this.
     */
    onLeave: {
      type: Function as PropType<ModalCallbackProps['onLeave']>,
      default: null,
    },

    title: {
      type: String,
      required: true,
    },

    saveLabel: {
      type: String,
      default: null,
    },

    ...contextProps,
  },

  setup: (props) => {
    const contextData = props.context ? useContext(props.context ?? props.contextKey) : null;
    const modalId = ref<string>(props.id ?? contextData?.id ?? props.contextKey ?? null);

    if (!modalId.value) {
      throw new Error('Modal must have an ID or context');
    }

    const { additionalContext, ...modalContext } = useModalContext(modalId, props.onSave, props.onLeave);
    return {
      ...modalContext,
      contextData: computed(() => {
        return additionalContext.value ?? contextData;
      }),

      modalId,
    };
  },
});
</script>

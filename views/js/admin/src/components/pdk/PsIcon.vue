<template>
  <div
    class="material-icons"
    v-text="materialIcon" />
</template>

<script lang="ts">
import {PropType, computed, defineComponent} from 'vue';
import {PdkIcon} from '@myparcel/pdk-frontend';
import {isEnumValue} from '@myparcel/ts-utils';

const materialIconMap: Record<PdkIcon, string> = {
  [PdkIcon.ADD]: 'add',
  [PdkIcon.ARROW_DOWN]: 'arrow-down',
  [PdkIcon.ARROW_LEFT]: 'arrow-left',
  [PdkIcon.ARROW_RIGHT]: 'arrow-right',
  [PdkIcon.ARROW_UP]: 'arrow-up',
  [PdkIcon.CLOSE]: 'close',
  [PdkIcon.DELETE]: 'delete',
  [PdkIcon.DOWNLOAD]: 'download',
  [PdkIcon.EDIT]: 'edit',
  [PdkIcon.EXPORT]: 'add',
  [PdkIcon.EXTERNAL]: 'external',
  [PdkIcon.LABEL]: 'label',
  [PdkIcon.PRINT]: 'print',
  [PdkIcon.WARNING]: 'warning',
  [PdkIcon.REFRESH]: 'refresh',
  [PdkIcon.RETURN]: 'reply',
  [PdkIcon.SAVE]: 'save',
};

export default defineComponent({
  name: 'PsIcon',
  props: {
    icon: {
      type: String as PropType<PdkIcon>,
      default: null,
      validator: (value: string) => isEnumValue(value, PdkIcon),
    },
  },

  setup: (props) => {
    return {
      materialIcon: computed(() => materialIconMap[props.icon]),
    };
  },
});
</script>

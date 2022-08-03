<template>
  <div
    class="material-icons"
    v-text="materialIcon" />
</template>

<script lang="ts">
import {PropType, computed, defineComponent} from 'vue';
import {AdminIcon} from '@myparcel-pdk/admin/src';
import {isEnumValue} from '@myparcel/ts-utils';

const materialIconMap: Record<AdminIcon, string> = {
  [AdminIcon.ADD]: 'add',
  [AdminIcon.ARROW_DOWN]: 'arrow-down',
  [AdminIcon.ARROW_UP]: 'arrow-up',
  [AdminIcon.CLOSE]: 'close',
  [AdminIcon.DELETE]: 'delete',
  [AdminIcon.DOWNLOAD]: 'download',
  [AdminIcon.EDIT]: 'edit',
  [AdminIcon.EXPORT]: 'add',
  [AdminIcon.EXTERNAL]: 'external',
  [AdminIcon.PRINT]: 'print',
  [AdminIcon.REFRESH]: 'refresh',
  [AdminIcon.RETURN]: 'reply',
  [AdminIcon.SAVE]: 'save',

};

export default defineComponent({
  name: 'PsIcon',
  props: {
    icon: {
      type: String as PropType<AdminIcon>,
      default: null,
      validator: (value: string) => isEnumValue(value, AdminIcon),
    },
  },

  setup: (props) => {
    return {
      materialIcon: computed(() => materialIconMap[props.icon]),
    };
  },
});
</script>

<template>
  <div
    class="material-icons"
    v-text="materialIcon" />
</template>

<script lang="ts">
import {type PropType, computed, defineComponent} from 'vue';
import {AdminIcon} from '@myparcel-pdk/admin';
import {isEnumValue} from '@myparcel/ts-utils';

const materialIconMap: Record<AdminIcon, string> = {
  [AdminIcon.Add]: 'add',
  [AdminIcon.ArrowDown]: 'south',
  [AdminIcon.ArrowUp]: 'north',
  [AdminIcon.Close]: 'close',
  [AdminIcon.Delete]: 'delete',
  [AdminIcon.Download]: 'download',
  [AdminIcon.Edit]: 'edit',
  [AdminIcon.Export]: 'exit_to_app',
  [AdminIcon.External]: 'external',
  [AdminIcon.Print]: 'print',
  [AdminIcon.Refresh]: 'refresh',
  [AdminIcon.Return]: 'reply',
  [AdminIcon.Save]: 'save',
  [AdminIcon.No]: 'clear',
  [AdminIcon.Shipment]: 'local_shipping',
  [AdminIcon.Spinner]: 'loop',
  [AdminIcon.Yes]: 'check',
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

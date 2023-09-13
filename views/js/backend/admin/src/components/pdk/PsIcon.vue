<template>
  <div
    v-test="AdminComponent.Icon"
    class="material-icons"
    v-text="materialIcon" />
</template>

<script lang="ts" setup>
import {computed} from 'vue';
import {AdminIcon, AdminComponent} from '@myparcel-pdk/admin';

const props = defineProps<{icon: AdminIcon}>();

const materialIconMap = {
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
} satisfies Record<AdminIcon, string>;

const materialIcon = computed(() => materialIconMap[props.icon]);
</script>
